<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->get();

        Event::factory()
            ->count(10)
            ->create()
            ->each(function (Event $event) use ($categories): void {
                $event->categories()->attach(
                    $categories
                        ->random(fake()->numberBetween(1, min(3, $categories->count())))
                        ->pluck('id')
                        ->all()
                );
            });
    }
}
