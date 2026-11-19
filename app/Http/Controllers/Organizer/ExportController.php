<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(private readonly EventService $eventService) {}

    public function csv(Request $request, Event $event): \Illuminate\Http\Response
    {
        return $this->eventService->exportCsv($event, $request->query('q', ''));
    }
}
