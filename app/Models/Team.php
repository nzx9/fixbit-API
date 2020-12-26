<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "leader_id",
        "is_active",
    ];

    protected $casts = [
        'leader_id' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Many to Many relationship defining
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(){
        return $this->belongsToMany(User::class);
    }

    /**
     * Create team member list table for given team
     *
     * @param int tid
     * @return void
     */
    public function createTeamTable(int $tid)
    {
        Schema::create('team_' . $tid, function (Blueprint $table) {
            $table->bigInteger('uid')->primary();
            $table->string('name')->length(30);
            $table->tinyInteger('is_leader')->default(false);
            $table->tinyInteger('is_available')->default(true);
            $table->string('role')->length('20')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

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
                $data[] = User::find($member->uid);
            }
        }
        return $data;
    }

    /**
     * Drop table table of given tid
     *
     * @param
     * @return void
     */

    public function dropTeamTable(int $tid){
        Schema::dropIfExists("team_".$tid);
    }

}
