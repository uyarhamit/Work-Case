<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterEventsRequest;
use App\Http\Requests\FindEventsRequest;
use App\Http\Requests\FutureEventsRequest;
use App\Http\Requests\JoinEventsRequest;
use App\Http\Requests\StoreEventsRequest;
use App\Http\Requests\UpdateEventsRequest;
use App\Mail\EventMail;
use App\Mail\EventReminder;
use App\Models\Events;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class EventsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventsRequest $request)
    {
        try {

            $data  = $request->validated();
            if (count($data['speakers']) == 1 && count($data['attendees']) == 1) {
                if ($data['speakers'][0] == $data['attendees'][0]) {
                    return response()->json(['error' => 'Speaker and attendees person can not be same!'], 400);
                }
            }

            $end_date = Carbon::parse($data['start_date']);
            $data['end_date'] = $end_date->addMinutes((int)$data['duration']);
            $data['created_user_id'] = $request->user()->id;

            $check_event_available = Events::where(function ($query) use ($data) {
                $query->where('start_date', '<=', $data['start_date']);
                $query->where('end_date', '>=', $data['start_date']);
            })->orWhere(function ($query) use ($data) {
                $query->where('start_date', '<=', $data['end_date']);
                $query->where('end_date', '>=', $data['end_date']);
            })->orWhere(function ($orQuery) use ($data) {
                $orQuery->whereBetween('start_date', [$data['start_date'], $data['end_date']]);
                $orQuery->orWhereBetween('end_date', [$data['start_date'], $data['end_date']]);
            })->count();

            if ($check_event_available) {
                return response()->json(['error' => 'overplanning_check_dates'], 400);
            }

            $event = Events::create($data);
            foreach ($data['speakers'] as $key => $speaker) {
                if (in_array($speaker, $data['attendees'])) {
                    $data['attendees'] = array_diff($data['attendees'], [$speaker]);
                }

                $event->eventAttendees()->create([
                    'events_id' => $event->id,
                    'users_id' => $speaker,
                    'speaker' => true
                ]);
            }

            foreach ($data['attendees'] as $key => $attendee) {
                $event->eventAttendees()->create([
                    'events_id' => $event->id,
                    'users_id' => $attendee,
                    'speaker' => false
                ]);
            }

            $emails = $event->eventAttendees()->with('user')->get()->pluck('user.email');

            Mail::to($emails)->send(new EventMail($event));

            $data = $event->with('createdUser', 'eventAttendees', 'eventAttendees.user')->where('id', $event->id)->first();

            return response()->json(['data' => $data, 'message' => 'event_created']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }

    public function find(FindEventsRequest $request)
    {
        $event = Events::where('id', $request->event_id)->with('createdUser', 'eventAttendees', 'eventAttendees.user')->first();
        return response()->json(['data' => $event]);
    }

    public function all()
    {
        $event = Events::with('createdUser', 'eventAttendees', 'eventAttendees.user')->orderBy('id', 'DESC')->get();
        return response()->json(['data' => $event]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Events $event)
    {
        $event = $event->with('createdUser', 'eventAttendees', 'eventAttendees.user')->where('id', $event->id)->first();
        return response()->json(['data' => $event]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventsRequest $request)
    {
        try {
            $data  = $request->validated();
            $end_date = Carbon::parse($data['start_date']);
            $data['end_date'] = $end_date->addMinutes($data['duration']);

            $event = Events::where('id', $data['event_id'])->first();

            $event->update($data);

            $event->eventAttendees()->delete();

            foreach ($data['speakers'] as $key => $speaker) {
                if (in_array($speaker, $data['attendees'])) {
                    $data['attendees'] = array_diff($data['attendees'], [$speaker]);
                    $event->eventAttendees()->create([
                        'events_id' => $event->id,
                        'users_id' => $speaker,
                        'speaker' => true
                    ]);
                }
            }

            foreach ($data['attendees'] as $key => $attendee) {
                $event->eventAttendees()->create([
                    'events_id' => $event->id,
                    'users_id' => $attendee,
                    'speaker' => false
                ]);
            }
            $data = $event->with('createdUser', 'eventAttendees', 'eventAttendees.user')->where('id', $event->id)->first();
            return response()->json(['data' => $data, 'message' => 'event_updated']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FindEventsRequest $request)
    {
        try {
            $event = Events::where('id', $request->event_id)->first();
            $event->delete();
            return response()->json(['message' => 'event_deleted']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }

    public function join(JoinEventsRequest $request)
    {
        $data = $request->validated();
        $event = Events::where('id', $data['event_id'])->first();
        $user_can_not_join = $event->eventAttendees()->where('users_id', $request->user()->id)->exists();
        if ($user_can_not_join) {
            return response()->json(['error' => 'You are already joined this event!'], 400);
        } else {
            $event->eventAttendees()->create([
                'events_id' => $event->id,
                'users_id' => $request->user()->id,
                'speaker' => false
            ]);
            Mail::to($request->user()->email)->send(new EventMail($event));
            return response()->json(['message' => 'added_successfully']);
        }
    }

    public function future(FutureEventsRequest $request)
    {
        $data = $request->validated();
        $events = Events::whereHas('eventAttendees', function ($query) use ($data) {
            $query->where('users_id', $data['user_id']);
        })
            ->where('start_date', '>=', now())
            ->with('createdUser', 'eventAttendees', 'eventAttendees.user')
            ->orderBy('id', 'DESC')
            ->get();
        return response()->json(['data' => $events]);
    }

    public function filter(FilterEventsRequest $request)
    {
        $filter_data = $request->validated();
        DB::enableQueryLog();
        $events = Events::when($filter_data, function ($query) use ($filter_data) {
            if (isset($filter_data['title'])) {
                $query->where('title', 'LIKE', '%' . $filter_data['title'] . '%');
            }
            if (isset($filter_data['start_date'])) {
                $query->whereDate('start_date', '>=', $filter_data['start_date']);
            }
            if (isset($filter_data['end_date'])) {
                $query->whereDate('end_date', '<=', $filter_data['end_date']);
            }
            if (isset($filter_data['speaker_name'])) {
                $query->whereHas('eventAttendees', function ($subQuery) use ($filter_data) {
                    $subQuery->whereHas('user', function ($userQuery) use ($filter_data) {
                        $userQuery->where('speaker', 1);
                        $userQuery->where('name', 'LIKE', '%' . $filter_data['speaker_name'] . '%');
                    });
                });
            }
        })
            ->with('createdUser', 'eventAttendees', 'eventAttendees.user')
            ->orderBy('id', 'DESC')
            ->get();
        // $events = Events::whereHas('eventAttendees', function ($query) use ($data) {
        //     $query->where('users_id', $data['user_id']);
        // })
        //     ->where('start_date', '>=', now())
        //     ->with('createdUser', 'eventAttendees', 'eventAttendees.user')
        //     ->orderBy('id', 'DESC')
        //     ->get();
        return response()->json(['data' => $events]);
    }
}
