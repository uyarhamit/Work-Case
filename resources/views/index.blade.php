@extends('app.layout')
@section('content')
    <div class="col-md-12">
        <div class="row">
            <button id="top_login" class="btn btn-primary ml-auto d-none"
                onclick="$('#loginModal').modal('show');">Login</button>
            <button id="top_logout" class="btn btn-danger ml-auto d-none" onclick="logOut()">Logout</button>
        </div>
    </div>
    <div class="col-md-10 m-auto">
        <div class="row mt-5">
            <div class="col-md-12 text-center">
                <strong>ADD EVENTS</strong>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form id="createEvent">
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="col-form-label">Title:</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="col-form-label">Attendance Limit:</label>
                            <input type="number" name="attendance_limit" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="col-form-label">Start Date:</label>
                            <input type="datetime-local" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="col-form-label">Duration</label>
                            <select id="duration" class="form-control" name="duration">
                                @for ($i = 5; $i <= 60;)
                                    <option value="{{ $i }}">{{ $i }} minutes</option>
                                    @php
                                        $i += 5;
                                    @endphp
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-froup col-md-6">
                            <label class="col-form-label">Attendees</label>
                            <select id="attendees" class="js-example-basic-multiple form-control" name="attendees[]"
                                multiple="multiple">
                            </select>
                        </div>
                        <div class="form-froup col-md-6">
                            <label class="col-form-label">Speakers</label>
                            <select id="speakers" class="js-example-basic-multiple form-control" name="speakers[]"
                                multiple="multiple">
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary float-right" onclick="addEvent()">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-md-12 text-center">
                <strong>EVENTS FUTURE EVENTS</strong>
            </div>
            <div class="col-md-12 mt-1">
                <div class="row">
                    <div class="form-froup col-md-6">
                        <label class="col-form-label">Users</label>
                        <select id="future_user_id" class="form-control" name="future_user_id">
                            <option value="0">Select User</option>
                        </select>
                    </div>
                    <div class="col-md-6" style="align-content: end;">
                        <button class="btn btn-primary btn-sm" type="button" onclick="userFutureEvents()">Future
                            Search</button>
                    </div>
                </div>
            </div>
            <div class="col-md-12 text-center mt-5">
                <strong>EVENTS FILTER</strong>
            </div>
            <div class="col-md-12 mt-1">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="col-form-label">Event name:</label>
                        <input type="text" name="event_title" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="col-form-label">Speaker name:</label>
                        <input type="text" name="event_speaker_name" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="col-form-label">Start Date</label>
                        <input type="date" name="event_start_date" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="col-form-label">End Date</label>
                        <input type="date" name="event_end_date" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button class="btn btn-primary btn-sm" type="button" onclick="filterEvents()">Filter
                            Events</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-md-12 text-center">
                <strong>EVENTS</strong>
            </div>
        </div>
        <div class="row">
            <table id="events_table" class="table">
                <thead>
                    <tr>
                        <th scope="col">Created By</th>
                        <th scope="col">Title</th>
                        <th scope="col">Start</th>
                        <th scope="col">Duration</th>
                        <th scope="col">Attendance Limit</th>
                        <th scope="col">Speakers</th>
                        <th scope="col">Attendances</th>
                        <th scope="col">Status</th>
                        <th scope="col">#</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function loginOrRegister() {
            if ($('#register-tab').hasClass('active')) {
                register();
            } else {
                login();
            }
        }

        function login() {
            let email = $('[name=email]').val();
            let password = $('[name=password]').val();
            $.ajax({
                type: 'POST',
                url: '{{ route('api.login') }}',
                dataType: 'json',
                data: {
                    email,
                    password
                },
                beforeSend: function() {
                    $('#loginBtn').attr('disabled', true);
                },
                success: function(msg) {
                    if (msg.access_token) {
                        localStorage.access_token_active = true;
                        localStorage.access_token = msg.access_token;
                        $('#loginModal').modal('hide');
                        $('#top_login').addClass('d-none');
                        $('#top_logout').removeClass('d-none');
                        runFunctions();
                    }
                },
                error: function(err) {
                    if (err.responseJSON && err.responseJSON.message) {
                        alert(err.responseJSON.message);
                    } else if (err.responseJSON && err.responseJSON.error) {
                        alert(err.responseJSON.error);
                    }
                },
                complete: function() {
                    $('#loginBtn').attr('disabled', false);
                }
            })
        }

        function register() {
            let name = $('[name=register_name]').val();
            let email = $('[name=register_email]').val();
            let password = $('[name=register_password]').val();
            let password_confirmation = $('[name=register_password_confirmation]').val();
            $.ajax({
                type: 'POST',
                url: '{{ route('api.register') }}',
                dataType: 'json',
                data: {
                    name,
                    email,
                    password,
                    password_confirmation
                },
                beforeSend: function() {
                    $('#loginBtn').attr('disabled', true);
                },
                success: function(msg) {
                    if (msg.access_token) {
                        localStorage.access_token_active = true;
                        localStorage.access_token = msg.access_token;
                        $('#loginModal').modal('hide');
                        $('#top_login').addClass('d-none');
                        $('#top_logout').removeClass('d-none');
                        runFunctions();
                    }
                },
                error: function(err) {
                    if (err.responseJSON && err.responseJSON.message) {
                        alert(err.responseJSON.message);
                    } else if (err.responseJSON && err.responseJSON.error) {
                        alert(err.responseJSON.error);
                    }
                },
                complete: function() {
                    $('#loginBtn').attr('disabled', false);
                }
            })
        }

        function getEvents() {
            $.ajax({
                type: 'POST',
                url: '{{ route('api.events.get') }}',
                dataType: 'json',
                data: {},
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', `Bearer ${localStorage.access_token}`);
                },
                success: function(msg) {
                    if (msg.data) {
                        createEventsRow(msg.data);
                    }
                },
                error: function(err) {
                    if (err.status == 401) {
                        localStorage.access_token_active = false;
                        $('#loginModal').modal('show');
                    }
                    if (err.responseJSON && err.responseJSON.message) {
                        alert(err.responseJSON.message);
                    } else if (err.responseJSON && err.responseJSON.error) {
                        alert(err.responseJSON.error);
                    }
                },
                complete: function() {

                }
            })
        }

        function createEventsRow(events) {
            let body = $('tbody', '#events_table').empty();
            let tr, td;
            events.forEach(event => {
                let start_date = event.start_date.split('T');
                let speakers = [];
                let attendees = [];
                event.event_attendees.forEach(attendee => {
                    if (attendee.speaker) {
                        speakers.push(attendee.user.name);
                    } else {
                        attendees.push(attendee.user.name);
                    }
                });
                tr = $('<tr/>', {}).appendTo(body);
                td = $('<td/>', {
                    html: (event.created_user && event.created_user.name) ? event.created_user.name : ''
                }).appendTo(tr);
                td = $('<td/>', {
                    html: event.title
                }).appendTo(tr);
                td = $('<td/>', {
                    html: `${start_date[0]} ${start_date[1].substr(0,5)}`
                }).appendTo(tr);
                td = $('<td/>', {
                    html: event.duration
                }).appendTo(tr);
                td = $('<td/>', {
                    html: event.attendance_limit
                }).appendTo(tr);
                td = $('<td/>', {
                    html: speakers.join(', ')
                }).appendTo(tr);
                td = $('<td/>', {
                    html: attendees.join(', ')
                }).appendTo(tr);
                td = $('<td/>', {
                    html: (event.is_expired == '1') ? 'Active' : 'Expired'
                }).appendTo(tr);
                td = $('<td/>', {
                    html: `<button class="btn btn-primary btn-sm" type='button' onclick="joinToEvent(${event.id})">Join</button>`
                }).appendTo(tr);
            });
        }

        function createUserOption(users) {
            let attendees = $('#attendees').empty();
            let speakers = $('#speakers').empty();
            let allUsers = $('#future_user_id');
            let option;
            users.forEach(user => {
                option = $('<option/>', {
                    value: user.id,
                    html: user.name
                }).appendTo([speakers, attendees, allUsers]);
            });
        }

        function getUsers() {
            $.ajax({
                type: 'GET',
                url: '{{ route('api.users.get') }}',
                dataType: 'json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', `Bearer ${localStorage.access_token}`);
                },
                success: function(msg) {
                    if (msg.data) {
                        createUserOption(msg.data);
                    }
                },
                error: function(err) {
                    if (err.status == 401) {
                        localStorage.access_token_active = false;
                        $('#loginModal').modal('show');
                    }
                    if (err.responseJSON && err.responseJSON.message) {
                        alert(err.responseJSON.message);
                    } else if (err.responseJSON && err.responseJSON.error) {
                        alert(err.responseJSON.error);
                    }
                },
                complete: function() {

                }
            })
        }

        function makeInputClear() {
            $('input,select', '#createEvent').each(function() {
                $(this).val('');
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).trigger('change.select2')
                }
            })
        }

        function addEvent() {
            let title = $('[name=title]').val();
            let start_date = $('[name=start_date]').val();
            if (start_date != '') {
                start_date = start_date.replace('T', ' ');
            }
            let duration = $('[name=duration]').val();
            let attendance_limit = $('[name=attendance_limit]').val();
            let attendees = $('#attendees').val();
            let speakers = $('#speakers').val();
            $.ajax({
                type: 'POST',
                url: '{{ route('api.events.create') }}',
                dataType: 'json',
                data: {
                    title,
                    start_date,
                    duration,
                    attendance_limit,
                    attendees,
                    speakers
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', `Bearer ${localStorage.access_token}`);
                },
                success: function(msg) {
                    if (msg.data) {
                        getEvents();
                        makeInputClear();
                        if (msg.message) {
                            alert(msg.message);
                        }
                    }
                },
                error: function(err) {
                    if (err.status == 401) {
                        localStorage.access_token_active = false;
                        $('#loginModal').modal('show');
                    }
                    if (err.responseJSON && err.responseJSON.message) {
                        alert(err.responseJSON.message);
                    } else if (err.responseJSON && err.responseJSON.error) {
                        alert(err.responseJSON.error);
                    }
                }
            })
        }

        function joinToEvent(event_id) {
            $.ajax({
                type: 'POST',
                url: '{{ route('api.events.join') }}',
                dataType: 'json',
                data: {
                    event_id
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', `Bearer ${localStorage.access_token}`);
                },
                success: function(msg) {
                    getEvents();
                    if (msg.message) {
                        alert(msg.message);
                    }
                },
                error: function(err) {
                    if (err.status == 401) {
                        localStorage.access_token_active = false;
                        $('#loginModal').modal('show');
                    }
                    if (err.responseJSON && err.responseJSON.message) {
                        alert(err.responseJSON.message);
                    } else if (err.responseJSON && err.responseJSON.error) {
                        alert(err.responseJSON.error);
                    }
                }
            })
        }

        function userFutureEvents() {
            let user_id = $('#future_user_id').val();
            $.ajax({
                type: 'POST',
                url: '{{ route('api.events.future') }}',
                dataType: 'json',
                data: {
                    user_id
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', `Bearer ${localStorage.access_token}`);
                },
                success: function(msg) {
                    if (msg.data) {
                        createEventsRow(msg.data);
                    }
                },
                error: function(err) {
                    if (err.status == 401) {
                        localStorage.access_token_active = false;
                        $('#loginModal').modal('show');
                    }
                    if (err.responseJSON && err.responseJSON.message) {
                        alert(err.responseJSON.message);
                    } else if (err.responseJSON && err.responseJSON.error) {
                        alert(err.responseJSON.error);
                    }
                }
            })
        }

        function filterEvents() {
            let title = $('[name=event_title]').val();
            let speaker_name = $('[name=event_speaker_name]').val();
            let start_date = $('[name=event_start_date]').val();
            let end_date = $('[name=event_end_date]').val();
            $.ajax({
                type: 'POST',
                url: '{{ route('api.events.filter') }}',
                dataType: 'json',
                data: {
                    title,
                    speaker_name,
                    start_date,
                    end_date
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', `Bearer ${localStorage.access_token}`);
                },
                success: function(msg) {
                    if (msg.data) {
                        createEventsRow(msg.data);
                    }
                },
                error: function(err) {
                    if (err.status == 401) {
                        localStorage.access_token_active = false;
                        $('#loginModal').modal('show');
                    }
                    if (err.responseJSON && err.responseJSON.message) {
                        alert(err.responseJSON.message);
                    } else if (err.responseJSON && err.responseJSON.error) {
                        alert(err.responseJSON.error);
                    }
                }
            })
        }

        function logOut() {
            $.ajax({
                type: 'POST',
                url: '{{ route('api.logout') }}',
                dataType: 'json',
                data: {},
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('Authorization', `Bearer ${localStorage.access_token}`);
                },
                success: function(msg) {
                    if (msg.message) {
                        alert(msg.message);
                    }
                    localStorage.access_token_active = false;
                    $('#loginModal').modal('show');
                    $('#top_login').removeClass('d-none');
                    $('#top_logout').addClass('d-none');
                },
                error: function(err) {
                    if (err.status == 401) {
                        localStorage.access_token_active = false;
                        $('#loginModal').modal('show');
                    }
                    if (err.responseJSON && err.responseJSON.message) {
                        alert(err.responseJSON.message);
                    } else if (err.responseJSON && err.responseJSON.error) {
                        alert(err.responseJSON.error);
                    }
                }
            })
        }

        function runFunctions() {
            getEvents();
            getUsers();
        }

        $(function() {
            $('.js-example-basic-multiple').select2();
            if (typeof localStorage.access_token_active === 'undefined' || localStorage.access_token_active == 'false') {
                $('#loginModal').modal('show');
                $('#top_login').removeClass('d-none');
            } else {
                runFunctions();
                $('#top_logout').removeClass('d-none');
            }
        })
    </script>
@endsection
