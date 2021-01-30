<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "description",
        "is_public",
        "creator_id",
        "admin_id",
        "team_id",
    ];
    protected $casts = [
        'creator_id' => 'integer',
        'admin_id' => 'integer',
        'team_id' => 'integer',
        'is_public' => 'boolean',
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
     * Create issue table for given project
     *
     * @param int pid
     * @return void
     */
    public function createIssueTable(int $pid)
    {
        Schema::create('project_' . $pid, function (Blueprint $table) {
            $table->id();
            $table->string('title')->length(30);
            $table->string('description')->length(5000);
            $table->json('attachments')->nullable();
            $table->bigInteger('creator_id')->index();
            $table->bigInteger('assign_to')->nullable()->index();
            $table->integer('priority');
            $table->integer('type');
            $table->tinyInteger('is_open');
            $table->json('comments')->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Drop issue table of given pid
     *
     * @param
     * @return void
     */

    public function dropIssueTable(int $pid){
        Schema::dropIfExists("project_".$pid);
    }
}
