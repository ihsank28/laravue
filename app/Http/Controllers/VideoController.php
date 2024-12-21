<?php

namespace App\Http\Controllers;

use App\Jobs\EncodeVideo;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateVideoRequest;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Pion\Laravel\ChunkUpload\Handler\ContentRangeUploadHandler;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    public function store(Request $request)
    {
        $video = $request
            ->user()
            ->videos()
            ->create([
                'title' => $request->title,
            ]);

        return response()->json([
            'id' => $video->id,
        ]);
    }
    public function update(Request $request, Video $video)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $video->update($validated);

        return redirect()->back()->with('success', 'Video başarıyla güncellendi.');
    }
    public function upload(Request $request, Video $video){
        $reciever = new FileReceiver(
            UploadedFile::fake()->createWithContent('file', $request->getContent()),
            $request,
            ContentRangeUploadHandler::class,
        );

        $save = $reciever->receive();
        if ($save->isFinished()) {
            return $this->saveAndStoreFile($save->getFile(), $video);
        };
        $save->handler();
    }

    protected function saveAndStoreFile(UploadedFile $file, Video $video){
        $video->update([
            'path' => $file->storeAs('videos', Str::uuid(), 'public'),
        ]);

        EncodeVideo::dispatch($video);
    }
}
