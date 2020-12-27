<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->tinyInteger('is_available')->default(true);
            $table->string('role')->length('20')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
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
