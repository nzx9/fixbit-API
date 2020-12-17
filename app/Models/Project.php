<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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


    public function createIssueTable($pid)
    {
        Schema::create('project_' . $pid, function (Blueprint $table) {
            $table->id();
            $table->string('title')->length(50);
            $table->string('description')->length(500);
            $table->json('attachments');
            $table->integer('creator_id')->index();
            $table->integer('assign_to')->index();
            $table->integer('priority');
            $table->integer('type');
            $table->json('comments');
            $table->timestamps();
        });
    }
}
