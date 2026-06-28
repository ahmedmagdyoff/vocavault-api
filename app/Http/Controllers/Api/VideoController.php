<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VideoRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VideoController extends Controller
{
    /**
     * Display a listing of the user's videos.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $videos = $request->user()->videos()->latest()->get();
        return VideoResource::collection($videos);
    }

    /**
     * Store a newly created video in storage.
     */
    public function store(VideoRequest $request): VideoResource
    {
        $video = $request->user()->videos()->create($request->validated());
        return new VideoResource($video);
    }

    /**
     * Update the specified video in storage.
     */
    public function update(VideoRequest $request, Video $video): VideoResource
    {
        if ($video->user_id !== $request->user()->id) {
            abort(403);
        }

        $video->update($request->validated());
        return new VideoResource($video);
    }

    /**
     * Remove the specified video from storage.
     */
    public function destroy(Request $request, Video $video)
    {
        if ($video->user_id !== $request->user()->id) {
            abort(403);
        }

        $video->delete();
        return response()->noContent();
    }
}
