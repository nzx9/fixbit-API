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
     * Display the stats of the user
     *
     * @return \Illuminate\Http\Response
     */
    public function myStats()
    {
        $user = Auth::user();
        if(!is_null($user)){
            $project_ids = ProjectUserSearch::where('uid', $user->id)->get('pid');
            $team_ids = TeamUserSearch::where('uid', $user->id)->get('tid');
            $projects_in = [];
            $teams_in = [];
            $issues_open = [];
            $projects_in_c = count($project_ids);
            $teams_in_c = count($team_ids);
            $issues_total_c = 0;
            $issues_open_c = 0;
            $teams_leader_c = 0;
            $projects_admin_c = 0;
            if($projects_in_c > 0){
                foreach($project_ids as $project_id){
                    $issues_tmp = [];
                    $project = Project::find($project_id);
                    $project = $project[0];
                    $projects_in[] = array(
                        'id'        => $project->id,
                        'name'      => $project->name,
                        'is_admin'  => ($project->admin_id === $user->id),
                        'is_public' => $project->is_public
                    );
                    if($project->admin_id === $user->id) $projects_admin_c++;
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
                    if(count($issues_tmp) > 0)
                        $issues_open[] = array(
                            'pid' => $project->id,
                            'p_name' => $project->name,
                            'issues' => $issues_tmp
                        );
                }
            }
            if(count($team_ids) > 0){
                foreach($team_ids as $team_id){
                    $team = Team::find($team_id);
                    $team = $team[0];
                    $teams_in[] = array(
                        'id'        => $team->id,
                        'name'      => $team->name,
                        'is_active' => $team->is_active,
                        'is_leader' => ($team->leader_id === $user->id)
                    );
                    if($team->leader_id === $user->id) $teams_leader_c++;
                }
            }
            return response()->json([
                "success" => true,
                "type"    => "success",
                "reason"  => null,
                "msg"     => "Stats fetched successfully",
                "data"    => array(
                    'projects_in'          => $projects_in,
                    'projects_in_count'    => $projects_in_c,
                    'projects_admin_count' => $projects_admin_c,
                    'teams_in'             => $teams_in,
                    'teams_in_count'       => $teams_in_c,
                    'teams_leader_count'   => $teams_leader_c,
                    'open_issues'          => $issues_open,
                    'open_issue_count'     => $issues_open_c,
                    'total_issue_count'    => $issues_total_c,
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

    /**
     * Display the stats of the team
     *
     * @param  int  $tid
     * @return \Illuminate\Http\Response
     */

     public function teamStats (int $tid) {
        $user = Auth::user();
        if(!is_null($user)){
                $projects = Project::where('team_id', $tid)->get();
                $data = [];
                if(count($projects) > 0){
                    foreach($projects as $project){
                        $issue_total_count = count(DB::table("project_".$project->id)->get());
                        $issue_open_count = count(DB::table("project_".$project->id)->where("is_open", true)->get());
                        $data[] = array(
                            "info"            => $project,
                            "open_issue_count"   => $issue_open_count,
                            "closed_issue_count" => $issue_total_count - $issue_open_count
                        );
                    }
                }
                return response()->json([
                    "success" => true,
                    "type"    => "success",
                    "reason"  => null,
                    "msg"     => "Stats fetched successfully",
                    "data"    => $data
                ],$this->status_ok);
        }else{
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "unauthorized",
                "msg"     => "Unauthorized",
                "data"    => null
            ], $this->status_unauthorized);
        }
    }
}
