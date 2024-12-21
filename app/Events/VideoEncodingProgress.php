<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Video;

class VideoEncodingProgress implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Video $video, public int $percentage)
    {
    }

    public function broadcastWith(): array
    {
        return [
            'video_id' => $this->video->id,
            'percentage' => $this->percentage,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.'.$this->video->user_id),
        ];
    }
}