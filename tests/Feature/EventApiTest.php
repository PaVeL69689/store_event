<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_a_paginated_event_list(): void
    {
        $category = Category::factory()->create([
            'name' => 'Conference',
            'slug' => 'conference',
        ]);

        Event::factory()
            ->create([
                'title' => 'First event',
                'description' => 'First description',
                'starts_at' => '2026-06-10 10:00:00',
                'ends_at' => '2026-06-10 12:00:00',
            ])
            ->categories()
            ->attach($category);

        Event::factory()
            ->create([
                'title' => 'Second event',
                'description' => 'Second description',
                'starts_at' => '2026-06-12 10:00:00',
                'ends_at' => '2026-06-12 12:00:00',
            ])
            ->categories()
            ->attach($category);

        $response = $this->getJson('/api/events?per_page=1');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.title', 'First event');
    }

    public function test_it_sorts_events_by_start_date(): void
    {
        [$conference] = $this->createCategories();

        $laterEvent = $this->createEvent('Later event', '2026-06-20 10:00:00', [$conference]);
        $earlierEvent = $this->createEvent('Earlier event', '2026-06-10 10:00:00', [$conference]);

        $response = $this->getJson('/api/events');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $earlierEvent->id)
            ->assertJsonPath('data.1.id', $laterEvent->id);
    }

    public function test_it_filters_events_by_one_category(): void
    {
        [$conference, $workshop] = $this->createCategories();

        $matchingEvent = $this->createEvent('Conference event', '2026-06-10 10:00:00', [$conference]);
        $this->createEvent('Workshop event', '2026-06-11 10:00:00', [$workshop]);

        $response = $this->getJson("/api/events?category_ids[]={$conference->id}");

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingEvent->id);
    }

    public function test_it_filters_events_by_multiple_categories(): void
    {
        [$conference, $workshop, $meetup] = $this->createCategories();

        $conferenceEvent = $this->createEvent('Conference event', '2026-06-10 10:00:00', [$conference]);
        $workshopEvent = $this->createEvent('Workshop event', '2026-06-11 10:00:00', [$workshop]);
        $this->createEvent('Meetup event', '2026-06-12 10:00:00', [$meetup]);

        $response = $this->getJson("/api/events?category_ids[]={$conference->id}&category_ids[]={$workshop->id}");

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $conferenceEvent->id)
            ->assertJsonPath('data.1.id', $workshopEvent->id);
    }

    public function test_it_filters_events_by_date_range(): void
    {
        [$conference] = $this->createCategories();

        $this->createEvent('Before range', '2026-05-31 10:00:00', [$conference]);
        $matchingEvent = $this->createEvent('Inside range', '2026-06-15 10:00:00', [$conference]);
        $this->createEvent('After range', '2026-07-01 10:00:00', [$conference]);

        $response = $this->getJson('/api/events?date_from=2026-06-01&date_to=2026-06-30');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingEvent->id);
    }

    public function test_it_combines_category_and_date_filters(): void
    {
        [$conference, $workshop] = $this->createCategories();

        $this->createEvent('Wrong category', '2026-06-10 10:00:00', [$workshop]);
        $this->createEvent('Wrong date', '2026-08-10 10:00:00', [$conference]);
        $matchingEvent = $this->createEvent('Matching event', '2026-06-20 10:00:00', [$conference]);

        $response = $this->getJson("/api/events?category_ids[]={$conference->id}&date_from=2026-06-01&date_to=2026-06-30");

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingEvent->id);
    }

    public function test_it_rejects_unknown_categories(): void
    {
        $response = $this->getJson('/api/events?category_ids[]=999');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category_ids.0');
    }

    public function test_it_rejects_invalid_date_ranges(): void
    {
        $response = $this->getJson('/api/events?date_from=2026-07-01&date_to=2026-06-01');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date_to');
    }

    private function createCategories(): array
    {
        return [
            Category::factory()->create(['name' => 'Conference', 'slug' => 'conference']),
            Category::factory()->create(['name' => 'Workshop', 'slug' => 'workshop']),
            Category::factory()->create(['name' => 'Meetup', 'slug' => 'meetup']),
        ];
    }

    private function createEvent(string $title, string $startsAt, array $categories): Event
    {
        $event = Event::factory()->create([
            'title' => $title,
            'description' => "{$title} description",
            'starts_at' => $startsAt,
            'ends_at' => null,
        ]);

        $event->categories()->attach(collect($categories)->pluck('id')->all());

        return $event;
    }
}
