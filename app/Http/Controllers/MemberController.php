<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Team;
use App\Models\User;
use App\Models\Member;
use App\Models\TeamUserSearch;
use App\Models\Project;
use App\Models\ProjectUserSearch;


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
                $member_cls = new Member();
                $data = $member_cls->getInfoOfTeam($tid);
                return response()->json([
                    "success" => true,
                    "type"    => "info",
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
                'is_available' => 'boolean|nullable',
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
                    $member_cls = new Member();
                    if(is_null($member_cls->getInfoOfTeamMember($tid, $request->uid))){
                        $member_data = array(
                            "uid"          => $request->uid,
                            "name"         => $member->username,
                            "role"         => $request->role,
                            "is_available" => $request->is_available,
                            "created_at"   => $team_d->freshTimestamp(),
                            "updated_at"   => $team_d->freshTimestamp()
                        );
                        $inserted = $member_cls->addToTeamTable($tid, $member_data);
                        if($inserted){
                            $tu_data = array(
                                "tid"       => $tid,
                                "uid"       => $request->uid,
                            );
                            $tu_search = TeamUserSearch::create($tu_data);
                            $projects_team_in_use = Project::where('team_id', $tid)->distinct()->get(['id', 'is_public']);

                            foreach($projects_team_in_use as $p){
                                $pu_data = array(
                                    'uid'       => $request->uid,
                                    'pid'       => $p->id,
                                    'is_public' => $p->is_public,
                                    'created_at' => $member_cls->freshTimeStamp(),
                                    'updated_at' => $member_cls->freshTimeStamp(),

                                );
                                ProjectUserSearch::insertOrIgnore($pu_data);
                            }
                            return response()->json([
                                "success" => true,
                                "type"    => "success",
                                "reason"  => null,
                                "msg"     => "Member added to the team",
                                "data"    => null
                            ], $this->status_created);
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
                            "reason"  => "not inserted",
                            "msg"     => "Member already in the team",
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
     * @param  int $tid
     * @param  int $uid
     * @return \Illuminate\Http\Response
     */
    public function show(int $tid,int $uid)
    {
        $user = Auth::user();
        if(!is_null($user)){
            $member_cls = new Member();
            $member = $member_cls->getInfoOfTeamMember($tid, $uid);
            if(!is_null($member)){
                $user_info = User::find($member->uid);
                if(!is_null($user_info)){
                    $data = array(
                        'membership' => $member,
                        'info'       => $user_info
                    );
                    return response()->json([
                        "success" => true,
                        "type"    => "info",
                        "reason"  => null,
                        "msg"     => "Member data fetched successfully",
                        "data"    => $data
                    ], $this->status_ok);
                }else{
                    return response()->json([
                        "success" => false,
                        "type"    => "error",
                        "reason"  => "unknown",
                        "msg"     => "Something went wrong",
                        "data"    => null
                    ], $this->status_badrequest);
                }
            }else{
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "notfound",
                    "msg"     => "Member not found in the team",
                    "data"    => null
                ], $this->status_notfound);
            }
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $tid
     * @param  int  $uid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $tid, int $uid)
    {
        $user = Auth::user();
        if(!is_null($user)){
            $validator = Validator::make($request->all(), [
                'is_available' => 'boolean',
                'role'         => 'string|nullable'
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
                $member_cls = new Member();
                $member_i = $member_cls->getInfoOfTeamMember($tid, $uid);
                if(!is_null($member_i)){
                    $request->request->add(['updated_at' => $member_cls->freshTimeStamp()]);
                    $updated = $member_cls->updateMember($tid, $uid,
                    $request->only([
                        'is_available', 'role', 'updated_at'
                        ])
                    );
                    if($updated){
                        return response()->json([
                            "success" => true,
                            "type"    => "success",
                            "reason"  => null,
                            "msg"     => "Member updated successfully",
                            "data"    => null
                        ], $this->status_ok);
                    }else{
                        return response()->json([
                            "success" => false,
                            "type"    => "error",
                            "reason"  => "unknown",
                            "msg"     => "Somtehing went wrong, please contact support",
                            "data"    => null
                        ], $this->status_badrequest);
                    }
                }else{
                    return response()->json([
                        "success" => false,
                        "type"    => "error",
                        "reason"  => "notfound",
                        "msg"     => "Member not in the team",
                        "data"    => null
                    ], $this->status_notfound);
                }
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $tid
     * @param  int  $mid
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $tid,int $uid)
    {
        $user = Auth::user();
        if(!is_null($user)){
            $team_d = Team::find($tid);
            if((!is_null($team_d) && ($team_d->leader_id === $user->id)) || ($user->id === $uid)){
                if($team_d->leader_id === $uid){
                    return response()->json([
                        "success" => false,
                        "type"    => "error",
                        "reason"  => "forbidden",
                        "msg"     => "Leader can't delete own accout. Tansfer leadership before delete",
                        "data"    => null
                    ], $this->status_forbidden);
                }else{
                    $member_cls = new Member();
                    if($member_cls->removeTeamMember($tid, $uid)){
                        $tus = TeamUserSearch::where('tid', $tid)->where('uid', $uid)->delete();
                        $projects_team_member_in = Project::where('team_id', $tid)->where('admin_id', '!=', $uid)->distinct()->get('id');

                        foreach($projects_team_member_in as $p){
                            ProjectUserSearch::where('pid', $p->id)->where('uid', $uid)->delete();

                        }

                        return response()->json([
                            "success" => true,
                            "type"    => "success",
                            "reason"  => null,
                            "msg"     => "Member deleted successfully",
                            "data"    => null
                        ], $this->status_ok);
                    }else{
                        return response()->json([
                            "success" => false,
                            "type"    => "error",
                            "reason"  => "notfound",
                            "msg"     => "Member not in the team",
                            "data"    => null
                        ], $this->status_notfound);
                    }
                }
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
