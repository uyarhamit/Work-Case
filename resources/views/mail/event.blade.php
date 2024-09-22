<p>You have been added to the meeting as a participant. Meeting details as below</p>
<p><strong>Title:</strong> {{ $event->title }}</p>
<p><strong>Created By:</strong> {{ $event->createdUser->name }}</p>
<p><strong>Speakers:</strong> {{ implode(', ', $speakers) }}</p>
<p><strong>Attendees:</strong> {{ implode(', ', $attendees) }}</p>
<p><strong>Start At:</strong> {{ $event->start_date }}</p>
<p><strong>Duration:</strong> {{ $event->duration }} minutes</p>
