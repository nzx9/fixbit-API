<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Team;
use App\Models\TeamUserSearch;

class TeamController extends Controller
{
    private $status_ok = 200;
    private $status_created = 201;
    private $status_accepted = 202;
    private $status_badrequest = 400;
    private $status_unauthorized = 401;
    private $status_forbidden = 403;
    private $status_notfound = 404;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        if(!is_null($user)){
            $tus = new TeamUserSearch();
            return response()->json([
                "success" => true,
                "type"    => "success",
                "reason"  => null,
                "msg"     => "team data fetched successfully",
                "data"    => $tus->getTeamsUserIsAMember($user->id)
            ], $this->status_ok);
        }
        return response()->json([
            "success" => false,
            "type"    => "error",
            "reason"  => "unathorized",
            "msg"     => "Unathorized",
            "data"    => null
        ], $this->status_unauthorized);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if(!is_null($user)){
            Team::delete($id);
            return response()-json([], $this->status_ok);
        }
    }
}
