<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IssueAssignNotifyEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $assigneeId;
    public $priority;
    public $username;
    public $pid;
    public $iid;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $assigneeId,int $priority,string $username, int $pid,int $iid)
    {
        $this->assigneeId = $assigneeId;
        $this->priority = $priority;
        $this->username = $username;
        $this->pid = $pid;
        $this->iid = $iid;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return ['isu-assign.'.$this->assigneeId];
    }

     /**
     * Get the event name to broadcast as.
     *
     * @return string
     */
    public function broadcastAs() {
        return 'isu-assign.ed';
    }
}
