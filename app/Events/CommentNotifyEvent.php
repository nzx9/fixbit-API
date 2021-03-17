<?php
namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CommentNotifyEvent implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $assigneeId;
  public $comment;
  public $pid;
  public $iid;
  /**
   * Create a new event instance.
   *
   * @return void
   */
  public function __construct(int $assigneeId,array $comment,int $pid, int $iid)
  {
      $this->assigneeId = $assigneeId;
      $this->pid = $pid;
      $this->iid = $iid;
      $this->comment = $comment;
  }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return ['comment.'.$this->assigneeId];
    }

    /**
     * Get the event name to broadcast as.
     *
     * @return string
     */
    public function broadcastAs() {
        return 'comment.created';
    }
}
