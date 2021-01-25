<?php

namespace Waterdhavian\NovaCalendarTool\Observers;

use Illuminate\Support\Facades\Auth;
use Waterdhavian\NovaCalendarTool\Models\Event;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class EventObserver
{
    /**
     * Handle the event "created" event.
     *
     * @param  \Waterdhavian\NovaCalendarTool\Models\Event $event
     * @return void
     */
    public function created(Event $event)
    {
        $gid = Auth::user()->google_calendar_id;
        if(empty($gid) && Auth::user()->company && Auth::user()->company->google_calendar_id) {
            $gid = Auth::user()->company->google_calendar_id;
        } else if(empty($gid)) {
            $gid = config('google-calendar.calendar_id');
        }

        $googleEvent = GoogleEvent::create([
            'name' => $event->title,
            'startDateTime' => $event->start,
            'endDateTime' => $event->end
        ], $gid);

        if ( ! empty($googleEvent->googleEvent->id))
        {
            $event->update([
                'google_calendar_id' => $googleEvent->googleEvent->id
            ]);
        }
    }

    /**
     * Handle the event "updated" event.
     *
     * @param  \Waterdhavian\NovaCalendarTool\Models\Event $event
     * @return void
     */
    public function updated(Event $event)
    {
        if ( ! empty($event->google_calendar_id))
        {
            $gid = Auth::user()->google_calendar_id;
            if(empty($gid) && Auth::user()->company && Auth::user()->company->google_calendar_id) {
                $gid = Auth::user()->company->google_calendar_id;
            } else if(empty($gid)) {
                $gid = config('google-calendar.calendar_id');
            }

            $googleEvent = GoogleEvent::find($event->google_calendar_id, $gid);

            if ( ! empty($googleEvent))
            {
                $googleEvent->update([
                    'name' => $event->title,
                    'startDateTime' => $event->start,
                    'endDateTime' => $event->end
                ]);
            }
        }
    }

    /**
     * Handle the event "deleted" event.
     *
     * @param  \Waterdhavian\NovaCalendarTool\Models\Event $event
     * @return void
     */
    public function deleted(Event $event)
    {
        if ( ! empty($event->google_calendar_id))
        {
            $gid = Auth::user()->google_calendar_id;
            if(empty($gid) && Auth::user()->company && Auth::user()->company->google_calendar_id) {
                $gid = Auth::user()->company->google_calendar_id;
            } else if(empty($gid)) {
                $gid = config('google-calendar.calendar_id');
            }

            $googleEvent = GoogleEvent::find($event->google_calendar_id, $gid);

            if ( ! empty($googleEvent))
            {
                $googleEvent->delete();
            }
        }
    }

    /**
     * Handle the event "restored" event.
     *
     * @param  \Waterdhavian\NovaCalendarTool\Models\Event $event
     * @return void
     */
    public function restored(Event $event)
    {
        //
    }

    /**
     * Handle the event "force deleted" event.
     *
     * @param  \Waterdhavian\NovaCalendarTool\Models\Event $event
     * @return void
     */
    public function forceDeleted(Event $event)
    {
        //
    }
}
