<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Team;
use App\Models\User;
use App\Models\TeamUserSearch;

class MemberController extends Controller
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
     * @param int $tid
     * @return \Illuminate\Http\Response
     */
    public function index(int $tid)
    {
        $user = Auth::user();
        if(!is_null($user)){
            $t = Team::find($tid);
            if(!is_null($t)){
                $team = new Team();
                $data = $team->getInfoOfTeam($tid);
                return response()->json([
                    "success" => true,
                    "type"    => "success",
                    "reason"  => null,
                    "msg"     => "Member information fetched",
                    "data"    => $data], $this->status_ok);
            }else{
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "notfound",
                    "msg"     => "Team not found",
                    "data"    => null], $this->status_notfound);
            }
        }else{
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "Unauthorized",
                "msg"     => "Unauthorized",
                "data"    => $data], $this->status_unauthorized);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int $tid
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,int $tid)
    {
        $user = Auth::user();
        if(!is_null($user)){
            $validator = Validator::make($request->all(), [
                'uid'          => 'required|integer',
                'is_leader'    => 'boolean',
                'is_available' => 'boolean',
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

            $team_d = Team::find($tid);
            if(!is_null($team_d) && ($team_d->leader_id === $user->id)){
                $member = User::find($request->uid);
                if(!is_null($member)){
                    $member_data = array(
                        "uid"          => $request->uid,
                        "name"         => $member->username,
                        "role"         => $request->role,
                        "is_available" => $request->is_available,
                        "is_leader"    => $request->is_leader,
                        "created_at"   => $team_d->freshTimestamp(),
                        "updated_at"   => $team_d->freshTimestamp()
                    );
                    $team = new Team();
                    $inserted = $team->addToTeamTable($tid, $member_data);
                    if($inserted){
                        $tu_data = array(
                            "tid"       => $tid,
                            "uid"       => $request->uid,
                        );
                        $tu_search = TeamUserSearch::create($tu_data);
                        return response()->json([
                            "success" => true,
                            "type"    => "success",
                            "reason"  => null,
                            "msg"     => "Member added to the team",
                            "data"    => null
                        ], $this->status_ok);
                    }else{
                        return response()->json([
                            "success" => false,
                            "type"    => "error",
                            "reason"  => "not inserted",
                            "msg"     => "Member not added to the team",
                            "data"    => null
                        ], $this->status_badrequest);
                    }

                }else{
                    return response()->json([
                        "success" => false,
                        "type"    => "error",
                        "reason"  => "notfound",
                        "msg"     => "User not found to add as member",
                        "data"    => null
                    ], $this->status_notfound);
                }

            }
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "unauthorized",
                "msg"     => "Unauthorized",
                "data"    => null
            ], $this->status_unauthorized);

        }
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
