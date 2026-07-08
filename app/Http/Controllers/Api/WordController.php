<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WordRequest;
use App\Http\Resources\WordResource;
use App\Models\Word;
use App\Services\WordBulkService;
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
        $word = $request->user()->words()->create($request->safe()->except(['video_id', 'forms']));

        $validationError = $this->syncVideo($word, $request);
        if ($validationError) {
            $word->delete();
            return $validationError;
        }

        $this->syncForms($word, $request);

        return new WordResource($word->load(['category', 'videos', 'forms']));
    }

    /**
     * Store words in bulk.
     */
    public function bulkStore(Request $request, WordBulkService $service): JsonResponse
    {
        $request->validate([
            'rows' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'video_id' => ['required', 'integer', 'exists:videos,id'],
        ]);

        $submittedId = $request->video_id;
        $validVideo = $request->user()->videos()->where('id', $submittedId)->exists();
        if (!$validVideo) {
            return response()->json([
                'message' => 'The video ID is invalid or does not belong to you.',
            ], 422);
        }

        $report = $service->process(
            $request->input('rows'),
            $request->input('category_id'),
            $submittedId,
            $request->user()
        );

        return response()->json(['report' => $report]);
    }

    /**
     * Update the specified word in storage.
     */
    public function update(WordRequest $request, Word $word): WordResource|JsonResponse
    {
        if ($word->user_id !== $request->user()->id) {
            abort(403);
        }

        $word->update($request->safe()->except(['video_id', 'forms']));

        $validationError = $this->syncVideo($word, $request);
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
     * Sync video association, rejecting if video_id doesn't belong to the user.
     */
    private function syncVideo(Word $word, WordRequest $request): ?JsonResponse
    {
        if (!$request->has('video_id')) {
            return null;
        }

        $submittedId = $request->video_id;
        $validVideo = $request->user()->videos()->where('id', $submittedId)->exists();

        if (!$validVideo) {
            return response()->json([
                'message' => 'The video ID is invalid or does not belong to you.',
            ], 422);
        }

        $word->videos()->sync([$submittedId]);
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
