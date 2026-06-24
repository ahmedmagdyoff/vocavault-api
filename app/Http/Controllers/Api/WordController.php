<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WordRequest;
use App\Http\Resources\WordResource;
use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WordController extends Controller
{
    /**
     * Display a listing of the user's words.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $words = $request->user()->words()->with(['category', 'videos', 'forms'])->latest()->get();
        return WordResource::collection($words);
    }

    /**
     * Store a newly created word in storage.
     */
    public function store(WordRequest $request): WordResource
    {
        $word = $request->user()->words()->create($request->safe()->except(['video_ids', 'forms']));

        if ($request->has('video_ids')) {
            // Ensure videos belong to the user
            $validVideoIds = $request->user()->videos()->whereIn('id', $request->video_ids)->pluck('id');
            $word->videos()->sync($validVideoIds);
        }

        if ($request->has('forms')) {
            $word->forms()->createMany($request->forms);
        }

        return new WordResource($word->load(['category', 'videos', 'forms']));
    }

    /**
     * Update the specified word in storage.
     */
    public function update(WordRequest $request, Word $word): WordResource
    {
        if ($word->user_id !== $request->user()->id) {
            abort(403);
        }

        $word->update($request->safe()->except(['video_ids', 'forms']));

        if ($request->has('video_ids')) {
            $validVideoIds = $request->user()->videos()->whereIn('id', $request->video_ids)->pluck('id');
            $word->videos()->sync($validVideoIds);
        }

        if ($request->has('forms')) {
            $word->forms()->delete();
            $word->forms()->createMany($request->forms);
        }

        return new WordResource($word->load(['category', 'videos', 'forms']));
    }

    /**
     * Remove the specified word from storage.
     */
    public function destroy(Request $request, Word $word)
    {
        if ($word->user_id !== $request->user()->id) {
            abort(403);
        }

        $word->delete();
        return response()->noContent();
    }
}
