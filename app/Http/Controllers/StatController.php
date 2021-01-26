<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectUserSearch;
use App\Models\Project;
use App\Models\TeamUserSearch;
use App\Models\Team;

class statController extends Controller
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
            $project_ids = ProjectUserSearch::where('uid', $user->id)->get('pid');
            $team_ids = TeamUserSearch::where('uid', $user->id)->get('tid');
            $projects_admin = [];
            $teams_admin = [];
            $issues_open = [];
            $projects_in_c = count($project_ids);
            $teams_in_c = count($team_ids);
            $issues_total_c = 0;
            $issues_open_c = 0;
            if($projects_in_c > 0){
                foreach($project_ids as $project_id){
                    $issues_tmp = [];
                    $project = Project::find($project_id);
                    $project = $project[0];
                    if($project->admin_id === $user->id) $projects_admin[] = array(
                        'id'        => $project->id,
                        'name'      => $project->name,
                        'is_public' => $project->is_public);;
                    $issues = DB::table('project_'.$project->id)->get();
                    foreach($issues as $issue){
                        if($issue->assign_to === $user->id){
                            $issues_total_c++;
                            if($issue->is_open === 1){
                                $issues_open_c++;
                                $issues_tmp[] = array(
                                    'iid' => $issue->id,
                                    'title' => $issue->title,
                                    'priority' => $issue->priority
                                );
                            }
                        }
                    }
                    $issues_open[] = array('pid' => $project->id, 'p_name' => $project->name, 'issues' => $issues_tmp);
                }
            }
            if(count($team_ids) > 0){
                foreach($team_ids as $team_id){
                    $team = Team::find($team_id);
                    $team = $team[0];
                    $team_d = array(
                        'id'        => $team->id,
                        'name'      => $team->name,
                        'is_active' => $team->is_active,
                    );
                    if($team->leader_id === $user->id) $teams_admin[] = $team_d;
                }
            }
            return response()->json([
                "success" => true,
                "type"    => "success",
                "reason"  => null,
                "msg"     => "Stats fetched successfully",
                "data"    => array(
                    'projects_admin'    => $projects_admin,
                    'teams_admin'       => $teams_admin,
                    'open_issues'       => $issues_open,
                    'projects_in_count' => $projects_in_c,
                    'teams_in_count'    => $teams_in_c,
                    'open_issue_count'  => $issues_open_c,
                    'total_issue_count' => $issues_total_c
                    )],
                    $this->status_ok);
        }else{
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "unauthorized",
                "msg"     => "Unauthorized",
                "data"    => null], $this->status_unauthorized);
        }
    }

}
