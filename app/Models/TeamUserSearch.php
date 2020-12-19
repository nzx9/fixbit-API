<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamUserSearch extends Model
{
    use HasFactory;

    /**
     * Teams, user is a member
     *
     * @param int uid
     * @return array
     */
    public function getTeamsUserIsAMember(int $uid){
        $tu = $this->where('uid', $uid)->get();
        return $tu;
    }

    /**
     * is user member of given team
     *
     * @param int tid
     * @param int uid
     * @return boolean
     */
    public function isUserMemberOfTeam(int $tid,int $uid){
        $tu = $this->where('tid', $tid)->where('uid', $uid)->get();
        if(count($tu) === 1){
            return true;
        }
        return false;
    }
}
