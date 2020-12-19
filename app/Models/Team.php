<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

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
    public function createIssueTable(int $tid)
    {
        Schema::create('team_' . $tid, function (Blueprint $table) {
            $table->bigInteger('uid')->primary();
            $table->string('name')->length(30);
            $table->tinyInteger('is_leader')->nullable()->index();
            $table->tinyInteger('is_available');
            $table->string('role')->length('20')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Drop table table of given tid
     *
     * @param
     * @return void
     */

    public function dropIssueTable(int $tid){
        Schema::dropIfExists("team_".$tid);
    }

}
