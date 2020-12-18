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
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "invalid",
                "msg"     => "Invalid login credientials",
                "data"    => null
            ], $this->status_unauthorized);
        }
    }

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
}
