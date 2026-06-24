<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Noun',
            'Verb',
            'Adjective',
            'Adverb',
            'Pronoun',
            'Preposition',
            'Conjunction',
            'Interjection',
            'Expression',
            'Phrasal Verb'
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category]);
        }
    }
}
