<?php

namespace Waterdhavian\NovaCalendarTool\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Waterdhavian\NovaCalendarTool\Models\Event;
use Illuminate\Http\Request;

class EventsController
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $events = Event::where('user_id', $user->id)
            ->OrWhere('company_id', $user->company->id)
            ->filter($request->query())
            ->get(['id', 'title', 'start', 'end'])
            ->toJson();

        return response($events);
    }

    public function store(Request $request)
    {
        $validation = Event::getModel()->validate($request->input(), 'create');

        if ($validation->passes())
        {
            $event = Event::create($request->input());

            if(Auth::user()->company && Auth::user()->company->google_calendar_id) {
                $event->company_id = Auth::user()->company_id;
                $event->save();
            } else {
                $event->user_id = Auth::user()->id;
                $event->save();
            }

            if ($event)
            {
                return response()->json([
                    'success' => true,
                    'event' => $event
                ]);
            }
        }

        return response()->json([
            'error' => true,
            'message' => $validation->errors()->first()
        ]);
    }

    public function update(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);
        $validation = Event::getModel()->validate($request->input(), 'update');

        if ($validation->passes())
        {
            $event->update($request->input());

            return response()->json([
                'success' => true,
                'event' => $event
            ]);
        }

        return response()->json([
            'error' => true,
            'message' => $validation->errors()->first()
        ]);
    }

    public function destroy(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);

        if ( ! is_null($event))
        {
            $event->delete();

            return response()->json(['success' => true]);
        }

        return response()->json(['error' => true]);
    }
}
