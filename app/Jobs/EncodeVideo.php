<?php

namespace App\Jobs;

use App\Events\VideoEncodingStarted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Video;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Illuminate\Support\Str;
use App\Events\VideoEncodingProgress;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ProtoneMedia\LaravelFFMpeg\Filesystem\Media;

class EncodeVideo implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Video $video)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            VideoEncodingStarted::dispatch($this->video);
            $path = 'videos/' . Str::uuid() . '.mp4';
            FFMpeg::fromDisk('public')
                ->open($this->video->path)
                ->export()
                ->onProgress(function ($percentage) {
                    VideoEncodingProgress::dispatch($this->video, $percentage);
                })
                ->toDisk('public')
                ->inFormat(new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'))
                ->save($path);
                Storage::disk('public')->delete($this->video->path);
            $this->video->update([
                'path' => $path,
                'encoded' => true,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error encoding video: ' . $e->getMessage());
        }
    }
}
