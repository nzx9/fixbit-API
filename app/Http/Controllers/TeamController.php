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
            $team_ids = $tus->getTeamsUserIsAMember($user->id);
            if(count($team_ids) >= 0){
                foreach($team_ids as $team_id){
                    $teams[] = Team::find($team_id->tid);
                }
            return response()->json([
                "success" => true,
                "type"    => "success",
                "reason"  => null,
                "msg"     => "team data fetched successfully",
                "data"    => $teams
            ], $this->status_ok);
        }
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
        $user = Auth::user();
        if(!is_null($user)){
            $validator = Validator::make($request->all(), [
                "name"        => "required|unique:teams|max:30",
                "description" => "required|max:500",
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

            $team_data = array(
                "name"        =>$request->name,
                "description" => $request->description,
                "leader_id"  => $user->id,
                "is_active"    => true,
            );

            $team = Team::create($team_data);

            if(is_null($team)){
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "not create",
                    "msg"     => "Team creation failed",
                    "data"    => null
                ], $this->status_forbidden);
            }

            $team_cls = new Team();
            $team_cls->createTeamTable($team->id);
            $data = array(
                'uid'          => $user->id,
                'name'         => $user->username,
                'is_leader'    => true,
                'is_available' => true,
                'role'         => 'admin',
                'created_at' => $team_cls->freshTimestamp(),
                'updated_at' => $team_cls->freshTimestamp()
            );
            $team_cls->addToTeamTable($team->id, $data);

            $tu_data = array(
                "tid"       => $team->id,
                "uid"       => $user->id,
            );

            $tu_search = TeamUserSearch::create($tu_data);

            if(!is_null($tu_search)){
                return response()->json([
                    "success" => true,
                    "type"    => "success",
                    "reason"  => null,
                    "msg"     => "Team created successfully",
                    "data"    => $team
                ], $this->status_created);
            }else{
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "unknown",
                    "msg"     => "Something went wrong, please contact support",
                    "data"    => null
                ], $this->status_forbidden);
            }
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
        $user = Auth::user();
        if(!is_null($user)){
            $team = Team::find($id);
            if(!is_null($team)){
                return response()->json([
                    "success" => true,
                    "type"    => "success",
                    "reason"  => null,
                    "msg"     => "Team view success",
                    "data"    => $team], $this->status_ok);
            }else{
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "not fetched",
                    "msg"     => "No such team to view",
                    "data"    => null], $this->status_notfound);
            }
        }else{
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "unauthorized",
                "msg"     => "Unauthorized",
                "data"    => null], $this->status_unauthorized);
        }
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
        $user = Auth::user();
        $team= Team::find($id);
        if(!is_null($team)){
            if(!is_null($user) && ($team->leader_id === $user->id)){
                $validator = Validator::make($request->all(), [
                    "name" => "unique:teams|max:30",
                    "description" => "max:500",
                    "leader_id" => "integer",
                    "is_active"  => "boolean",
                ]);

                if($validator->fails()){
                    return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "validation error",
                    "msg"     => $validator->errors(),
                    "data"    => null
                    ],$this->status_badrequest);
                }

                if(!is_null($team) && $team->update($request->all()) === true){
                    return response()->json([
                        "success" => true,
                        "type"    => "success",
                        "reason"  => null,
                        "msg"     => "Team updated successfully",
                        "data"    => $team], $this->status_ok);
                }else{
                    return response()->json([
                        "success" => false,
                        "type"    => "error",
                        "reason"  => "not updated",
                        "msg"     => "No such team to update",
                        "data"    => null], $this->status_badrequest);
                }
            }else{
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "unauthorized",
                    "msg"     => "Unauthorized",
                    "data"    => null], $this->status_unauthorized);
            }
        }else{
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "notfound",
                "msg"     => "No such team to update",
                "data"    => null], $this->status_notfound);
        }
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
            $team = Team::find($id);
            if(!is_null($team) && ($team->leader_id === $user->id) && ($team->delete() === true)){
                $tus = TeamUserSearch::where('tid', $id)->delete();
                $team_cls = new Team();
                $team_cls->dropTeamTable($id);
                return response()->json([
                    "success" => true,
                    "type"    => "success",
                    "reason"  => null,
                    "msg"     => "Team deleted successfully",
                    "data"    => null], $this->status_ok);
            }else{
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "notfound",
                    "msg"     => "No such team to delete",
                    "data"    => null], $this->status_notfound);
            }
        }
        return response()->json([
            "success" => false,
            "type"    => "error",
            "reason"  => "unauthorized",
            "msg"     => "Unauthorized",
            "data"    => null], $this->status_unauthorized);
    }
}
