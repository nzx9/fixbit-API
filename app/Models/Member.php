<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Member extends Model
{
    use HasFactory;

    /**
     * Add team member to team table
     *
     * @param int tid
     * @param array data
     * @return boolean
     */
    public function addToTeamTable(int $tid,array $data)
    {
        $data_inserted = DB::table('team_'.$tid)->insert($data);
        if($data_inserted) return true;
        return false;
    }

    /**
     * get all information of members in given team table
     *
     * @param int tid
     * @param array data
     * @return boolean
     */
    public function getInfoOfTeam(int $tid)
    {
        $data = null;
        $members = DB::table('team_'.$tid)->get();
        if(!is_null($members)){
            foreach($members as $member){
                $data[] = array(
                    "member" => $member,
                    "info"   => User::find($member->uid)
                );

            }
        }
        return $data;
    }

    /**
     * get information of member in given team table
     *
     * @param int tid
     * @param int uid
     * @param array data
     * @return boolean
     */
    public function getInfoOfTeamMember(int $tid, int $uid)
    {
        $member = DB::table('team_'.$tid)->where('uid', $uid)->get();
        if(!is_null($member) && count($member) > 0){
            return $member[0];
        }
        return null;
    }

    /**
     * Update member
     *
     * @param int tid
     * @param int uid
     * @param array data
     * @return boolean
     */
    public function updateMember(int $tid,int $uid, $data){
        $updated = DB::table('team_'.$tid)->where('uid', $uid)->update($data);

        if(!is_null($updated)){
            return true;
        }
        return false;
    }

    /**
     * remove member from team
     *
     * @param int tid
     * @param int uid
     * @return boolean
     */
    public function removeTeamMember(int $tid, int $uid)
    {
        return DB::table('team_'.$tid)->where('uid', $uid)->delete();
    }

}
