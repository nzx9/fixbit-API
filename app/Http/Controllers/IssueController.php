<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Issue;
use App\Models\ProjectUserSearch;

class IssueController extends Controller
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
     * @param int pid
     * @return \Illuminate\Http\Response
     */
    public function index($pid)
    {
        $user = Auth::user();
        if(!is_null($user)){
            $pu = new ProjectUserSearch();
            $user_has_access = $pu->isUserHasAccessToProject($pid, $user->id);
            if($user_has_access){
                $issue = new Issue();
                $issues = $issue->getAllIssues($pid);
                return response()->json([
                "success" => true,
                "type"    => "success",
                "reason"  => null,
                "msg"     => "Issues fetched successfully",
                "data"    => $issues], $this->status_ok);
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
    public function store(Request $request,int $pid)
    {
        $user = Auth::user();
        if(!is_null($user)){
            $pu = new ProjectUserSearch();
            $user_has_access = $pu->isUserHasAccessToProject($pid, $user->id);
            if($user_has_access){
                $validator = Validator::make($request->all(), [
                    'title' => 'required|max:30',
                    'description' => 'required|max:500',
                    'attachments' => 'file',
                    'assign_to' => 'integer',
                    'priority'    => 'required|integer',
                    'type'        => 'required|integer',
                    'is_open'     => 'required|boolean'
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

                $issue = new Issue();
                $data = array(
                    "title"       => $request->title,
                    "description" => $request->description,
                    "attachments" => $request->attachments,
                    "assign_to" => $request->assigned_to,
                    "creator_id"  => $user->id,
                    "priority"    => $request->priority,
                    "type"        => $request->type,
                    "is_open"     => $request->is_open,
                    "created_at" => $issue->freshTimestamp(),
                    "updated_at" => $issue->freshTimestamp()
                );
                $is = $issue->createIssue($pid, $data);
                if($is){
                    return response()->json([
                        "success" => true,
                        "type"    => "success",
                        "reason"  => null,
                        "msg"     => "Issue created successfully",
                        "data"    => null
                    ], $this->status_created);
                }else{
                    return response()->json([
                        "success" => false,
                        "type"    => "error",
                        "reason"  => "unknown",
                        "msg"     => "Something went wrong, please contact support",
                        "data"    => null
                    ], $this->status_badrequest);
                }
            }else{
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "notfound",
                    "msg"     => "Project not found",
                    "data"    => null
                ], $this->status_notfound);
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
     * Display the specified resource.
     *
     * @param  int  $pid
     * @param  int  $iid
     * @return \Illuminate\Http\Response
     */
    public function show($pid, $iid)
    {
        $user = Auth::user();
        if(!is_null($user)){
            $pu = new ProjectUserSearch();
            $user_has_access = $pu->isUserHasAccessToProject($pid, $user->id);
            if($user_has_access){
                $issue = new Issue();
                $issue_data = $issue->getIssue($pid, $iid);
                if(count($issue_data) === 1){
                    return response()->json([
                        "success" => true,
                        "type"    => "success",
                        "reason"  => null,
                        "msg"     => "Issue data fetched successfully",
                        "data"    => $issue_data[0]
                    ], $this->status_created);
                }
            }
            return response()->json([
                "success" => false,
                "type"    => "error",
                "reason"  => "notfound",
                "msg"     => "Issue not found",
                "data"    => null
            ], $this->status_notfound);

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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $pid
     * @param  int  $iid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $pid, $iid)
    {
        $user = Auth::user();
        if(!is_null($user)){
            $pu = new ProjectUserSearch();
            $user_has_access = $pu->isUserHasAccessToProject($pid, $user->id);
            if($user_has_access){
                $validator = Validator::make($request->all(), [
                    'title' => 'max:30',
                    'description' => 'max:500',
                    'attachments' => 'file',
                    'assign_to'   => 'integer',
                    'priority'    => 'integer',
                    'type'        => 'integer',
                    'is_open'     => 'boolean'
                ]);

                if($validator->fails()){
                    return response()->json([
                        "success" => false,
                        "type"    => "error",
                        "reason"  => "validation error",
                        "msg"     => $validator->errors(),
                        "data"    => null], $this->status_badrequest);
                }

                $issue = new Issue();
                if(count($request->input()) === 1 && $request->is_open !== null){
                    $updated = $issue->updateIssueByColumn($pid, $iid, 'is_open', $request->is_open);
                    if($updated){
                        return response()->json([
                            "success" => true,
                            "type"    => "success",
                            "reason"  => null,
                            "msg"     => "Issue updated successfully",
                            "data"    => null
                        ], $this->status_ok);
                    }else{
                        return response()->json([
                            "success" => false,
                            "type"    => "error",
                            "reason"  => "unknown",
                            "msg"     => "Somtehing went wrong, please contact support",
                            "data"    => null], $this->status_badrequest);
                    }
                }else{
                    $request->request->add(['updated_at' => $issue->freshTimeStamp()]);
                    $updated = $issue->updateIssue($pid, $iid,
                    $request->only([
                        'title','description', 'is_open',
                        'priority', 'type', 'assign_to', 'attachments',
                        'comments', 'updated_at'
                        ])
                    );

                    if($updated){
                        return response()->json([
                            "success" => true,
                            "type"    => "success",
                            "reason"  => null,
                            "msg"     => "Issue updated successfully",
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
                }
            }else{
                return response()->json([
                    "success" => false,
                    "type"    => "error",
                    "reason"  => "notfound",
                    "msg"     => "Issue not found",
                    "data"    => null
                ], $this->status_notfound);
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
     * Remove the specified resource from storage.
     *
     * @param  int  $pid
     * @param  int  $iid
     * @return \Illuminate\Http\Response
     */
    public function destroy($pid, $iid)
    {
        $user = Auth::user();
        if(!is_null($user)){
            $pu = new ProjectUserSearch();
            $user_has_access = $pu->isUserHasAccessToProject($pid, $user->id);
            if($user_has_access){
                $issue = new Issue();
                $deleted = $issue->deleteIssue($pid, $iid);
                if($deleted){
                    return response()->json([
                        "success" => true,
                        "type"    => "success",
                        "reason"  => null,
                        "msg"     => "Issue deleted successfully",
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
                    "msg"     => "Issue not found",
                    "data"    => null
                ], $this->status_notfound);
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
}
