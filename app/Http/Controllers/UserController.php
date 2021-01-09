<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    private $status_ok = 200;
    private $status_created = 201;
    private $status_accepted = 202;
    private $status_badrequest = 400;
    private $status_unauthorized = 401;
    private $status_forbidden = 403;
    private $status_notfound = 404;

    /**
     * Register new user.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */

    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username'   => 'required|max:25',
            'fullname'   => 'required',
            'email'      => 'required|unique:users|email',
            'password'   => 'required|min:8',
            'c_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "validation error",
                "msg"     => $validator->errors(),
                "data"    => null
            ], $this->status_badrequest);
        }

        $inputs = $request->except('c_password');
        $inputs['password'] = bcrypt($inputs['password']);  // encrypt passwords

        $user = User::create($inputs);    // create new

        if (!is_null($user)) {
            return response()->json([
                "success" => true,
                "type"    => "success",
                "reason"  => null,
                "msg"     => "User created successfully",
                "data"    => $user,
            ], $this->status_created);
        } else {
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "not create",
                "msg"     => "User not created",
                "data"    => null
            ], $this->status_forbidden);
        }
    }
    /**
     * Login existing user.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */

    public function loginUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email"    => 'required|email',
            "password" => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "validation error",
                "msg"     => $validator->errors(),
                "data"    => null,
            ], $this->status_badrequest);
        }

        if (Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::guard('web')->user();
            $token = $user->createToken("fixbit-api")->accessToken;

            return response()->json([
                "success" => true,
                "type"    => "success",
                "reason"  => null,
                "msg"     => "Login successed",
                "data"    => $user,
                "token"   => $token
            ], $this->status_ok);
        } else {
            $email = User::where('email', $request->email)->get();
            if(count($email) > 0){
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "invalid",
                    "msg"     => "Invalid login credientials",
                    "data"    => null
                ], $this->status_unauthorized);
            }else{
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "notfound",
                    "msg"     => "Email not registered!. please register.",
                    "data"    => null
                ], $this->status_ok);
            }
        }
    }

    /**
     * Get details current user.
     *
     * @return \Illuminate\Http\Response
     */
    public function userDetails()
    {
        $user = Auth::user();
        if (!is_null($user)) {
            return response()->json([
                "success" => true,
                "type"    => "success",
                "reason"  => null,
                "msg"     => "User found",
                "data"    =>  $user
            ], $this->status_ok);
        } else {
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "notfound",
                "msg"     => "User not found",
                "data"    =>  null
            ], $this->status_notfound);
        }
    }

    /**
     * Get details of specific user.
     *
     * @param  int  $uid
     * @return \Illuminate\Http\Response
     */
    public function specificUserDetails(int $uid)
    {
        $user = Auth::user();
        if (!is_null($user)) {
            $user_data = User::find($uid);
            return response()->json([
                "success" => !is_null($user_data) ? true: false,
                "type"    => !is_null($user_data) ? "success": "error",
                "reason"  => !is_null($user_data) ? null: "notfound",
                "msg"     => !is_null($user_data) ? "User Found": "User Not Found",
                "data"    =>  $user_data
            ], !is_null($user_data) ? $this->status_ok : $this->status_notfound);
        } else {
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "unauthorized",
                "msg"     => "unauthorized",
                "data"    =>  null
            ], $this->status_unauthorized);
        }
    }

    /**
     * Update user details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $uid
     * @return \Illuminate\Http\Response
     */
    public function updateUserDetails(Request $request){
        $user = Auth::user();
        if(!is_null($user)){
            $validator = Validator::make($request->all(), [
                "username"     => "string|max:25",
                "fullname"     => "string",
                "email"        => "email",
                "old_password" => "min:8",
                "password"     => "min:8",
                "c_password"   => "same:password",
                "twitter"      => "string|max:50",
                "linkedIn"     => "string|max:50",
                "github"       => "string|max:50",
                ]);

            if($validator->fails()){
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "validation error",
                    "msg"     => $validator->errors(),
                    "data"    => null
                ], $this->status_badrequest);
            }

            $updated_data = null;

            if(!is_null($request->password) || !is_null($request->email)){
                if(!is_null($request->password) &&
                    !is_null($request->old_password)
                ){
                    if( Auth::guard('web')->attempt(['email' => $user->email, 'password' => $request->old_password])){
                        $inputs = $request->only(['username', 'fullname', 'password', 'twitter', 'linkedIn', 'github']);
                        $inputs['password'] = bcrypt($inputs['password']);
                        if(User::where('id', $user->id)->update($inputs)){
                            $updated_data = User::find($user->id);
                        }
                    }else{
                        return response()->json([
                            "success" => false,
                            "type"    => "error",
                            "reason"  => "notmatch error",
                            "msg"     => "Old password not matched",
                            "data"    => null
                        ], $this->status_forbidden);
                    }
                }
                if(!is_null($request->email) && $request->email !== $user->email){
                    if(0 === count(User::where('email', $request->email)->get())){
                        if(User::where('id', $user->id)->update($request->only(['username', 'fullname', 'email', 'twitter', 'linkedIn','github']))){
                            $updated_data = User::find($user->id);
                        }
                    }else{
                        return response()->json([
                            "success" => false,
                            "type"    => "error",
                            "reason"  => "duplicate error",
                            "msg"     => "New Email already in use",
                            "data"    => null
                        ], $this->status_badrequest);
                    }
                }

            }else{
                if(User::where('id', $user->id)->update($request->only(['username','fullname', 'twitter', 'linkedIn', 'github']))){
                    $updated_data = User::find($user->id);
                }
            }
            if(!is_null($updated_data)){
                return response()->json([
                    "success" => true,
                    "type"    => "success",
                    "reason"  => null,
                    "msg"     => "User details updated successfully",
                    "data"    => $updated_data
                ], $this->status_ok);

            }else{
                return response()->json([
                    "success" => false,
                    "type"    => "warning",
                    "reason"  => "badrequest",
                    "msg"     => "User details not updated",
                    "data"    => null
                ], $this->status_badrequest);
            }

        }else{
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "unauthorized",
                "msg"     => "unauthorized",
                "data"    =>  null
            ], $this->status_unauthorized);
        }
    }
}
