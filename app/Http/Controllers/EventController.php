<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\Facebook\FacebookConversionApiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function show(Request $request, Event $event, FacebookConversionApiService $facebook): View
    {
        if (! $event->is_active) {
            abort(404);
        }

        if ($facebook->isConfigured()) {
            $facebook->viewContent($event->id, $event->title, $request->fullUrl());
        }

        return view('events.show', [
            'event' => $event,
            'availableSeats' => $event->availableSeatsCount(),
        ]);
    }
}
