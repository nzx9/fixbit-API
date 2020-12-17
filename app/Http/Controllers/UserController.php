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
            'username'   => 'required',
            'fullname'   => 'required',
            'email'      => 'required|unique:users|email',
            'password'   => 'required|min:8',
            'c_password' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status"  => $this->status_badrequest,
                "success" => false,
                "type"    => "error",
                "reason"  => "validation error",
                "msg"     => $validator->errors(),
                "data"    => null
            ]);
        }

        $inputs = $request->except('c_password');
        $inputs['password'] = bcrypt($inputs['password']);  // encrypt passwords

        $user = User::create($inputs);    // create new

        if (!is_null($user)) {
            return response()->json([
                "status"  => $this->status_created,
                "success" => true,
                "type"    => "success",
                "reason"  => "registration ok",
                "msg"     => "User created successfully",
                "data"    => $user,
            ]);
        } else {
            return response()->json([
                "status"  => $this->status_badrequest,
                "success" => false,
                "type"    => "error",
                "reason"  => "registration failed",
                "msg"     => "User not created",
                "data"    => null
            ]);
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
                "status"  => $this->status_badrequest,
                "success" => false,
                "type"    => "error",
                "reason"  => "validation error",
                "msg"     => $validator->errors(),
                "data"    => null,
            ]);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken("token")->accessToken;

            return response()->json([
                "status"  => $this->status_ok,
                "success" => true,
                "type"    => "success",
                "reason"  => "login ok",
                "msg"     => "login successed",
                "data"    => $user,
                "token"   => $token
            ]);
        } else {
            return response()->json([
                "status"  => $this->status_badrequest,
                "success" => false,
                "type"    => "error",
                "reason"  => "login failed",
                "msg"     => "Invalid login credientials",
                "data"    => null
            ]);
        }
    }

    public function userDetails()
    {
        $user = Auth::user();
        if (!is_null($user)) {
            return response()->json([
                "status"  => $this->status_ok,
                "success" => true,
                "type"    => "success",
                "reason"  => "user data",
                "msg"     => "user found",
                "data"    =>  $user
            ]);
        } else {
            return response()->json([
                "status"  => $this->status_badrequest,
                "success" => false,
                "type"    => "error",
                "reason"  => "user not found",
                "msg"     => "user not found",
                "data"    =>  null
            ]);
        }
    }
}
