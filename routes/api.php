<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\StatController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post("users/register", [UserController::class, "registerUser"]);
Route::post("users/login", [UserController::class, "loginUser"]);

Route::middleware("auth:api")->group(function () {
    Route::get("users/user", [UserController::class, "userDetails"]);
    Route::get("users/user/{uid}", [UserController::class, "specificUserDetails"]);
    Route::put("users/user", [UserController::class, "updateUserDetails"]);
    Route::resource("projects", ProjectController::class);
    Route::resource("projects/{pid}/issues", IssueController::class);
    Route::resource("teams", TeamController::class);
    Route::resource("teams/{tid}/members", MemberController::class);
    Route::get("stats", [StatController::class, "myStats"]);
    Route::get("stats/teams/{tid}", [StatController::class, "teamStats"]);
});

