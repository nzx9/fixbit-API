<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\IssueController;

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
    Route::get("user", [UserController::class, "userDetails"]);
    Route::resource("projects", ProjectController::class);
    Route::resource("projects/{pid}/issues", IssueController::class);
});

