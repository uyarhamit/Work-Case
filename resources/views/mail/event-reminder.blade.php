<p>Dear {{ $user->name }}, you have meeting today. Meeting details as below</p>
<p><strong>Title:</strong> {{ $event->title }}</p>
<p><strong>Created By:</strong> {{ $event->createdUser->name }}</p>
<p><strong>Start At:</strong> {{ $event->start_date }}</p>
<p><strong>Duration:</strong> {{ $event->duration }} minutes</p>
