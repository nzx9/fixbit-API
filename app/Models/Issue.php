<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "description",
        "attachments",
        "creator_id",
        "assigned_to",
        "priority",
        "type",
        "is_open",
        "comments"
    ];

    protected $casts = [
        'creator_id' => 'integer',
        'assigned_to' => 'integer',
        'priority' => 'integer',
        'type' => 'integer',
        'is_open' => 'boolean',
    ];

    /**
     * Get all issues from issue table
     *
     * @param int pid
     * @return array
     */
    public function getAllIssues($pid){
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
    public function getIssue($pid, $iid){
        $issue = DB::table('project_'.$pid)->where('id', $iid)->get();
        return $issue;
    }

    /**
     * Add issue to table
     *
     * @param int pid
     * @param array data
     * @return boolean
     */
    public function createIssue(int $pid,array $data){
        $iid = DB::table('project_'.$pid)->insertGetId(
            $data
        );
        if(!is_null($iid)){
            return true;
        }
        return false;
    }

    /**
     * Update issue
     *
     * @param int pid
     * @param int iid
     * @param array data
     * @return boolean
     */
    public function updateIssue(int $pid,int $iid,array $data){
        $updated = DB::table('project_'.$pid)->where('id', $iid)->update([
            'title'       => $data->title,
            'description' => $data->description,
            'attachments' => $data->attachments,
            'assign_to'   => $data->assign_to,
            'priority'    => $data->priority,
            'type'        => $data->type,
            'is_open'     => $data->is_open,
            'comments'    => $data->comments
        ]);

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
