<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WordRequest;
use App\Http\Resources\WordResource;
use App\Models\Word;
use Illuminate\Http\JsonResponse;
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
    public function store(WordRequest $request): WordResource|JsonResponse
    {
        $word = $request->user()->words()->create($request->safe()->except(['video_ids', 'forms']));

        $validationError = $this->syncVideos($word, $request);
        if ($validationError) {
            $word->delete();
            return $validationError;
        }

        $this->syncForms($word, $request);

        return new WordResource($word->load(['category', 'videos', 'forms']));
    }

    /**
     * Update the specified word in storage.
     */
    public function update(WordRequest $request, Word $word): WordResource|JsonResponse
    {
        if ($word->user_id !== $request->user()->id) {
            abort(403);
        }

        $word->update($request->safe()->except(['video_ids', 'forms']));

        $validationError = $this->syncVideos($word, $request);
        if ($validationError) {
            return $validationError;
        }

        $this->syncForms($word, $request);

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

    /**
     * Sync video associations, rejecting if any video_ids don't belong to the user.
     */
    private function syncVideos(Word $word, WordRequest $request): ?JsonResponse
    {
        if (!$request->has('video_ids')) {
            return null;
        }

        $submittedIds = $request->video_ids;
        $validVideoIds = $request->user()->videos()->whereIn('id', $submittedIds)->pluck('id');

        if ($validVideoIds->count() !== count($submittedIds)) {
            return response()->json([
                'message' => 'Some video IDs are invalid or do not belong to you.',
            ], 422);
        }

        $word->videos()->sync($validVideoIds);
        return null;
    }

    /**
     * Replace all grammatical forms on a word with the submitted set.
     */
    private function syncForms(Word $word, WordRequest $request): void
    {
        if (!$request->has('forms')) {
            return;
        }

        $word->forms()->delete();
        $word->forms()->createMany($request->forms);
    }
}
