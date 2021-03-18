<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectUserSearch;
use App\Models\Member;
use App\Models\Project;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "description",
        "attachments",
        "creator_id",
        "assign_to",
        "priority",
        "type",
        "is_open",
        "comments"
    ];

    protected $casts = [
        'creator_id' => 'integer',
        'assign_to' => 'integer',
        'priority' => 'integer',
        'type' => 'integer',
        'is_open' => 'boolean',
        'comments' => AsArrayObject::class
    ];

    /**
     * Get all issues from issue table
     *
     * @param int pid
     * @return array
     */
    public function getAllIssues(int $pid){
        $issues = DB::table('project_'.$pid)->get();
        return $issues;
    }

    /**
     * Get issue from issue table
     *
     * @param int pid
     * @param int iid
     * @return array
     */
    public function getIssue(int $pid,int $iid){
        $issue = DB::table('project_'.$pid)->where('id', $iid)->get();
        return $issue;
    }

    /**
     * Add issue to table
     *
     * @param int pid
     * @param array data
     * @return $iid || NULL
     */
    public function createIssue(int $pid,array $data){
        return DB::table('project_'.$pid)->insertGetId(
            $data
        );
    }

    /**
     * Update issue
     *
     * @param int pid
     * @param int iid
     * @param array data
     * @return boolean
     */
    public function updateIssue(int $pid,int $iid, $data){
        $updated = DB::table('project_'.$pid)->where('id', $iid)->update($data);

        if(!is_null($updated)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Update issue column
     *
     * @param int pid
     * @param int iid
     * @param string column
     * @param mixed value
     * @return boolean
     */
    public function updateIssueByColumn(int $pid,int $iid,string $column, $value){
        $updated = DB::table('project_'.$pid)->where('id', $iid)->update([
            $column => $value
        ]);

        if(!is_null($updated)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Update issue comments column
     *
     * @param int pid
     * @param int iid
     * @param array comments
     * @return boolean
     */
    public function updateCommentsColumn(int $pid,int $iid, array $comment, $time){
        $comments = DB::table('project_'.$pid)->where('id', $iid)->get("comments");
        $value = [];
        if(!is_null($value)) $value = json_decode($comments[0]->comments);
        $value[] = $comment;
        $updated = DB::table('project_'.$pid)->where('id', $iid)->update([
            'comments' => $value,
            'updated_at' => $time,
        ]);

        if(!is_null($updated)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Check user has access to the issue
     *
     * @param int pid
     * @param int uid
     * @param int iid
     * @return boolean
     */
    public function isUserHasAccessToIssue(int $pid,int $uid,int $iid){
        $pu_cls = new ProjectUserSearch();
        $has_access = $pu_cls->isUserHasAccessToProject($pid, $uid);
        if($has_access){
            $project_info = Project::find($pid);
            if($project_info->admin_id === $uid) return true;

            $issue_data = $this->getIssue($pid, $iid);
            if($issue_data[0]->creator_id === $uid) return true;

            if(!is_null($project_info->team_id)){
                $member_cls = new Member();
                $member = $member_cls->getInfoOfTeamMember($project_info->team_id, $uid);
                if(!is_null($member)) return true;
            }
        }
        return false;
    }

    /**
     * Delete issue
     *
     * @param int pid
     * @param int iid
     * @return void
     */
    public function deleteIssue(int $pid,int $iid){
        return DB::table('project_'.$pid)->where('id', $iid)->delete();
    }
}
