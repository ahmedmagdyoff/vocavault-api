<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use App\Models\Word;

class WordBulkService
{
    /**
     * Process bulk word import.
     *
     * @param string $rowsText
     * @param int $categoryId
     * @param int $videoId
     * @param User $user
     * @return array
     */
    public function process(string $rowsText, int $categoryId, int $videoId, User $user): array
    {
        $report = [];
        $category = Category::findOrFail($categoryId);
        $categoryName = $category->name;

        $expectedColumns = ['word', 'meaning'];
        $formFields = [];
        if ($categoryName === 'Verb') {
            $formFields = ['past_simple', 'past_participle', 'present_participle', 'third_person_singular'];
        } elseif ($categoryName === 'Noun') {
            $formFields = ['plural'];
        } elseif ($categoryName === 'Adjective') {
            $formFields = ['comparative', 'superlative'];
        }
        $expectedColumns = array_merge($expectedColumns, $formFields);
        $expectedCount = count($expectedColumns);

        $rows = explode("\n", $rowsText);
        
        // Performance: Extract words to fetch from DB in one go
        $wordsToFetch = [];
        foreach ($rows as $row) {
            if (trim($row) === '') continue;
            $fields = array_map('trim', explode(',', $row));
            if (!empty($fields[0])) {
                $wordsToFetch[] = $fields[0];
            }
        }
        
        // Fetch existing words with relationships
        $existingWords = $user->words()
            ->with(['forms', 'videos'])
            ->whereIn('word', $wordsToFetch)
            ->get()
            ->keyBy(function($item) {
                return strtolower($item->word);
            });

        $importedWordsLower = [];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 1;
            
            if (trim($row) === '') {
                continue;
            }

            try {
                $fields = explode(',', $row);
                $fields = array_map('trim', $fields);
                $wordText = $fields[0] ?? null;

                if (count($fields) !== $expectedCount) {
                    $report[] = [
                        'line' => $lineNumber,
                        'word' => $wordText,
                        'success' => false,
                        'status' => 'invalid_column_count',
                        'message' => 'Invalid column count.'
                    ];
                    continue;
                }

                $meaningText = $fields[1];

                if (empty($wordText)) {
                    $report[] = [
                        'line' => $lineNumber,
                        'word' => null,
                        'success' => false,
                        'status' => 'word_required',
                        'message' => 'Word is required.'
                    ];
                    continue;
                }

                if (empty($meaningText)) {
                    $report[] = [
                        'line' => $lineNumber,
                        'word' => $wordText,
                        'success' => false,
                        'status' => 'meaning_required',
                        'message' => 'Meaning is required.'
                    ];
                    continue;
                }

                $wordLower = strtolower($wordText);

                if (in_array($wordLower, $importedWordsLower, true)) {
                    $report[] = [
                        'line' => $lineNumber,
                        'word' => $wordText,
                        'success' => false,
                        'status' => 'duplicate_import',
                        'message' => 'Duplicate word inside import.'
                    ];
                    continue;
                }
                $importedWordsLower[] = $wordLower;

                $formsData = [];
                for ($i = 0; $i < count($formFields); $i++) {
                    $formValue = $fields[$i + 2];
                    if ($formValue !== '') {
                        $formsData[] = [
                            'form_type' => $formFields[$i],
                            'value' => $formValue
                        ];
                    }
                }

                $existingWord = $existingWords->get($wordLower);

                if (!$existingWord) {
                    $newWord = $user->words()->create([
                        'word' => $wordText,
                        'meaning' => $meaningText,
                        'category_id' => $categoryId
                    ]);

                    if (!empty($formsData)) {
                        $newWord->forms()->createMany($formsData);
                    }

                    if ($videoId) {
                        $newWord->videos()->sync([$videoId]);
                    }
                    
                    // Add to memory cache in case of edge cases, though duplicates handle it
                    $existingWords->put($wordLower, $newWord->load(['forms', 'videos']));

                    $report[] = [
                        'line' => $lineNumber,
                        'word' => $wordText,
                        'success' => true,
                        'status' => 'created',
                        'message' => 'Word created and linked.'
                    ];
                } else {
                    $updated = false;
                    
                    if (is_null($existingWord->meaning)) {
                        $existingWord->meaning = $meaningText;
                        $existingWord->save();
                        $updated = true;
                    }

                    $existingForms = $existingWord->forms->pluck('value', 'form_type')->toArray();
                    $formsToCreate = [];
                    
                    foreach ($formsData as $formData) {
                        $type = $formData['form_type'];
                        if (!array_key_exists($type, $existingForms)) {
                            $formsToCreate[] = $formData;
                            $updated = true;
                        }
                    }
                    
                    if (!empty($formsToCreate)) {
                        $newForms = $existingWord->forms()->createMany($formsToCreate);
                        // Update in-memory relationships for consistency
                        foreach ($newForms as $newForm) {
                            $existingWord->forms->push($newForm);
                        }
                    }

                    if ($videoId) {
                        $alreadyLinkedVideos = $existingWord->videos->pluck('id')->toArray();
                        
                        if (in_array($videoId, $alreadyLinkedVideos)) {
                            $report[] = [
                                'line' => $lineNumber,
                                'word' => $wordText,
                                'success' => false,
                                'status' => 'already_linked',
                                'message' => 'Word already linked to this video.'
                            ];
                            continue;
                        } else {
                            $existingWord->videos()->syncWithoutDetaching([$videoId]);
                            // Update in-memory relationships
                            $existingWord->load('videos');
                        }
                    }

                    $report[] = [
                        'line' => $lineNumber,
                        'word' => $wordText,
                        'success' => true,
                        'status' => $updated ? 'updated' : 'linked',
                        'message' => $updated ? 'Existing word updated and linked.' : 'Existing word linked.'
                    ];
                }
            } catch (\Exception $e) {
                $report[] = [
                    'line' => $lineNumber,
                    'word' => $wordText ?? null,
                    'success' => false,
                    'status' => 'error',
                    'message' => 'An unexpected error occurred: ' . $e->getMessage()
                ];
            }
        }

        return $report;
    }
}
