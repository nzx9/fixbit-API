<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\Team;
use App\Models\ProjectUserSearch;

class ProjectController extends Controller
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
            $project_ids = ProjectUserSearch::where('uid', $user->id)->orWhere('is_public', true)->get();
            if(count($project_ids) >= 0){
                $projects = [];
                $data = [];
                foreach($project_ids as $project_id){
                    $projects[] = Project::find($project_id->pid);
                }
                foreach($projects as $project){
                    $team_data = null;
                    $member_data = null;
                    if($project->team_id !== null){
                        $team_data = Team::find($project->team_id);
                        $member_data = DB::table("team_".$project->team_id)->get();
                    }
                    $issue_total_count = count(DB::table("project_".$project->id)->get());
                    $issue_open_count = count(DB::table("project_".$project->id)->where("is_open", true)->get());
                    $data[] = array(
                        'project' => $project,
                        'team' => array('info' => $team_data, 'members' => $member_data,
                        'issue' => array(
                            'total' => $issue_total_count,
                            'open'  => $issue_open_count
                        ))
                    );
                }
                return response()->json([
                    "success" => true,
                    "type"    => "success",
                    "reason"  => null,
                    "msg"     => "Projects fetched successfully",
                    "data"    => $data], $this->status_ok);
            }else{
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "notfound",
                    "msg"     => "Project not found",
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
                "name"        => "required|unique:projects|max:30",
                "description" => "required|max:500",
                "is_public"   => "required|boolean",
                "team_id"     => "integer"
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

            $project_data = array(
                "name"        =>$request->name,
                "description" => $request->description,
                "is_public"   => $request->is_public,
                "creator_id"  => $user->id,
                "admin_id"    => $user->id,
                "team_id"     => $request->team_id,
            );

            $project = Project::create($project_data);

            if(is_null($project)){
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "not create",
                    "msg"     => "Project create failed",
                    "data"    => null
                ], $this->status_forbidden);
            }

            $proj = new Project();
            $proj->createIssueTable($project->id);

            $pu_data = array(
                "pid"       => $project->id,
                "uid"       => $user->id,
                "is_public" => $project->is_public
            );

            $pu_search = ProjectUserSearch::create($pu_data);

            if(!is_null($pu_search)){
                return response()->json([
                    "success" => true,
                    "type"    => "success",
                    "reason"  => null,
                    "msg"     => "Project created successfully",
                    "data"    => $project
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
            $pu = new ProjectUserSearch();
            $pu_ids = $pu->projectIdsUserCanAccess($id, $user->id);
            if(count($pu_ids) === 1 && $project = Project::find($pu_ids[0]->pid)){
                $team_data = null;
                $member_data = null;
                if($project->team_id !== null) {
                    $team_data = Team::find($project->team_id);
                    $member_data = DB::table("team_".$project->team_id)->get();
                }
                $data = array(
                    'project' => $project,
                    'team' => array(
                        'info' => $team_data,
                        'members' => $member_data
                    ));
                return response()->json([
                    "success" => true,
                    "type"    => "success",
                    "reason"  => null,
                    "msg"     => "Project view success",
                    "data"    => $data], $this->status_ok);
            }else{
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "not fetched",
                    "msg"     => "No such project to view",
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
        $project= Project::find($id);
        if(!is_null($project)){
            if(!is_null($user) && ($project->admin_id === $user->id)){
                $validator = Validator::make($request->all(), [
                    "name" => "unique:projects|max:30",
                    "description" => "max:500",
                    "is_public" => "boolean",
                    "admin_id"  => "integer",
                    "team_id"   => "integer"
                ]);

                if($validator->fails()){
                    return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "validation error",
                    "msg"     => $validator->errors(),
                    "data"    => null], $this->status_badrequest);
                }

                if(!is_null($project) && $project->update($request->all()) === true){
                    return response()->json([
                        "success" => true,
                        "type"    => "success",
                        "reason"  => null,
                        "msg"     => "Project updated successfully",
                        "data"    => $project], $this->status_ok);
                }else{
                    return response()->json([
                        "success" => false,
                        "type"    => "error",
                        "reason"  => "not updated",
                        "msg"     => "No such project to update",
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
                "msg"     => "No such project to update",
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
            $project = Project::find($id);
            if(!is_null($project) && ($project->admin_id === $user->id) && ($project->delete() === true)){
                $proj = new Project();
                $proj->dropIssueTable($id);
                return response()->json([
                    "success" => true,
                    "type"    => "success",
                    "reason"  => null,
                    "msg"     => "Project deleted successfully",
                    "data"    => null], $this->status_ok);
            }else{
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "notfound",
                    "msg"     => "No such project to delete",
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
