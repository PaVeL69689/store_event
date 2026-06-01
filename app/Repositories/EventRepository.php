<?php

namespace App\Repositories;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventRepository
{
    public function paginateFiltered(array $filters, int $perPage): LengthAwarePaginator
    {
        return Event::query()
            ->with('categories')
            ->when($filters['category_ids'] ?? null, function ($query, array $categoryIds): void {
                $query->whereHas('categories', function ($query) use ($categoryIds): void {
                    $query->whereIn('categories.id', $categoryIds);
                });
            })
            ->when($filters['date_from'] ?? null, function ($query, string $dateFrom): void {
                $query->where('starts_at', '>=', "{$dateFrom} 00:00:00");
            })
            ->when($filters['date_to'] ?? null, function ($query, string $dateTo): void {
                $query->where('starts_at', '<=', "{$dateTo} 23:59:59");
            })
            ->orderBy('starts_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
