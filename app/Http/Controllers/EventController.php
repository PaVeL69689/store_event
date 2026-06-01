<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventIndexRequest;
use App\Http\Resources\EventResource;
use App\Services\EventService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController extends Controller
{
    public function index(EventIndexRequest $request, EventService $eventService): AnonymousResourceCollection
    {
        
        return EventResource::collection(
            $eventService->getPaginatedEvents($request->validated())
        );
    }
}
