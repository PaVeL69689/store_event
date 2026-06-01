<?php

namespace App\Services;

use App\Repositories\EventRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventService
{
    public function __construct(
        private readonly EventRepository $events,
    ) {
    }

    public function getPaginatedEvents(array $filters): LengthAwarePaginator
    {

        $perPage = (int) ($filters['per_page'] ?? 15);

  

        return $this->events->paginateFiltered($filters, $perPage);
    }
}
