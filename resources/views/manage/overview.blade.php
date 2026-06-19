<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Manage - YATTA</title>

        <style>
            :root {
                --bg: #f4f7fb;
                --panel: #ffffff;
                --text: #172033;
                --muted: #667085;
                --line: #d8dee9;
                --primary: #0f766e;
                --primary-dark: #115e59;
                --danger: #b42318;
                --danger-bg: #fff1f0;
                --soft: #eef6f5;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                background: var(--bg);
                color: var(--text);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                line-height: 1.5;
            }

            .shell {
                width: min(100% - 2rem, 78rem);
                margin: 0 auto;
                padding: 2rem 0;
            }

            .topbar,
            .page-head,
            .row-actions,
            .schedule-form {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .topbar,
            .page-head {
                justify-content: space-between;
            }

            .topbar {
                margin-bottom: 2rem;
            }

            .brand {
                display: inline-flex;
                align-items: center;
                gap: .65rem;
                font-weight: 800;
                letter-spacing: .08em;
            }

            .brand-mark {
                display: grid;
                width: 2.25rem;
                height: 2.25rem;
                place-items: center;
                border-radius: .5rem;
                background: var(--primary);
                color: #fff;
                font-weight: 800;
            }

            .nav {
                display: flex;
                align-items: center;
                gap: .75rem;
            }

            .card {
                margin-bottom: 1.5rem;
                border: 1px solid rgba(216, 222, 233, .9);
                border-radius: .75rem;
                background: var(--panel);
                box-shadow: 0 .75rem 2rem rgba(23, 32, 51, .07);
                overflow: hidden;
            }

            .page-head {
                padding: 1.5rem;
                border-bottom: 1px solid var(--line);
            }

            h1,
            h2 {
                margin: 0;
                line-height: 1.2;
            }

            h1 {
                font-size: 1.75rem;
            }

            h2 {
                font-size: 1.2rem;
            }

            .lead {
                margin: .4rem 0 0;
                color: var(--muted);
            }

            .button,
            .link-button,
            .danger-button {
                display: inline-flex;
                min-height: 2.4rem;
                align-items: center;
                justify-content: center;
                border-radius: .5rem;
                cursor: pointer;
                font: inherit;
                font-weight: 750;
                padding: .55rem .85rem;
                text-decoration: none;
                white-space: nowrap;
            }

            .button {
                border: 0;
                background: var(--primary);
                color: #fff;
            }

            .button:hover {
                background: var(--primary-dark);
            }

            .link-button {
                border: 1px solid var(--line);
                background: #fff;
                color: var(--text);
            }

            .danger-button {
                border: 1px solid #f1b5b0;
                background: var(--danger-bg);
                color: var(--danger);
            }

            .flash {
                margin-bottom: 1rem;
                border-radius: .6rem;
                padding: .85rem 1rem;
                font-weight: 650;
            }

            .flash.success {
                background: #e8f7ef;
                color: #166534;
            }

            .flash.error {
                background: #fdecec;
                color: #991b1b;
            }

            .table-wrap {
                overflow-x: auto;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                padding: 1rem;
                border-bottom: 1px solid var(--line);
                text-align: left;
                vertical-align: middle;
            }

            th {
                color: var(--muted);
                font-size: .78rem;
                font-weight: 800;
                text-transform: uppercase;
            }

            tbody tr:last-child td {
                border-bottom: 0;
            }

            .muted {
                color: var(--muted);
            }

            .empty {
                padding: 1.5rem;
                color: var(--muted);
            }

            .requested {
                display: inline-block;
                border-radius: .5rem;
                background: var(--soft);
                padding: .35rem .5rem;
                font-weight: 750;
            }

            textarea,
            select,
            input {
                width: 100%;
                border: 1px solid var(--line);
                border-radius: .5rem;
                padding: .55rem .65rem;
            }

            textarea {
                min-width: 13rem;
                min-height: 4.2rem;
            }

            .schedule-form {
                align-items: flex-end;
                flex-wrap: wrap;
            }

            .field {
                min-width: 9rem;
            }

            .field.schedule {
                min-width: 13rem;
            }

            .split-grid {
                display: grid;
                gap: 1rem;
                padding: 1.5rem;
            }

            .manager-form {
                display: grid;
                gap: 1rem;
            }

            .form-grid {
                display: grid;
                gap: 1rem;
                grid-template-columns: minmax(14rem, 1fr) 9rem auto;
                align-items: end;
            }

            .check-field {
                display: inline-flex;
                align-items: center;
                gap: .45rem;
                min-height: 2.4rem;
                font-weight: 750;
            }

            .check-field input {
                width: auto;
            }

            .day-grid {
                display: grid;
                gap: .75rem;
            }

            .day-row {
                display: grid;
                grid-template-columns: minmax(7rem, 1fr) repeat(3, minmax(6rem, .65fr));
                gap: .75rem;
                align-items: end;
                border-top: 1px solid var(--line);
                padding-top: .75rem;
            }

            .schedule-stack {
                display: grid;
                gap: 1rem;
            }

            .schedule-editor {
                border: 1px solid var(--line);
                border-radius: .6rem;
                padding: 1rem;
            }

            .schedule-editor h3 {
                margin: 0 0 .2rem;
                font-size: 1rem;
            }

            .schedule-editor-head {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .label {
                display: block;
                margin-bottom: .25rem;
                color: var(--muted);
                font-size: .78rem;
                font-weight: 800;
                text-transform: uppercase;
            }

            @media (max-width: 48rem) {
                .topbar,
                .page-head,
                .row-actions {
                    align-items: stretch;
                    flex-direction: column;
                }

                .nav,
                .schedule-form {
                    align-items: stretch;
                    flex-direction: column;
                }

                .form-grid,
                .day-row {
                    grid-template-columns: 1fr;
                }

                .schedule-editor-head {
                    align-items: stretch;
                    flex-direction: column;
                }

                .field {
                    min-width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <main class="shell">
            <header class="topbar">
                <div class="brand">
                    <span class="brand-mark">Y</span>
                    <span>YATTA</span>
                </div>

                <div class="nav">
                    <a class="link-button" href="{{ route('profile') }}">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="button" type="submit">Sign out</button>
                    </form>
                </div>
            </header>

            @if(session('success'))
                <div class="flash success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="flash error">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="flash error">{{ $errors->first() }}</div>
            @endif

            <section class="card">
                <div class="page-head">
                    <div>
                        <h1>Manage</h1>
                        <p class="lead">Incoming requests and employee schedule assignments.</p>
                    </div>
                </div>
            </section>

            <section class="card">
                <div class="page-head">
                    <div>
                        <h2>Schedules</h2>
                        <p class="lead">Create, edit, or remove work schedules for {{ $manager->company?->name ?? 'your company' }}.</p>
                    </div>
                </div>

                <div class="split-grid">
                    <form class="manager-form" method="POST" action="{{ route('manage.schedules.store') }}">
                        @csrf

                        <div class="form-grid">
                            <div class="field">
                                <label class="label" for="schedule_name_new">Name</label>
                                <input id="schedule_name_new" name="name" value="{{ old('name') }}" placeholder="Operations day shift" required>
                            </div>

                            <div class="field">
                                <label class="label" for="weekly_hours_new">Weekly hours</label>
                                <input id="weekly_hours_new" name="weekly_hours" type="number" min="0" max="168" step="0.25" value="{{ old('weekly_hours', 38) }}" required>
                            </div>

                            <label class="check-field">
                                <input name="active" type="checkbox" value="1" @checked((bool) old('active', true))>
                                Active
                            </label>
                        </div>

                        <div class="day-grid">
                            @foreach($weekdays as $weekday => $weekdayName)
                                <div class="day-row">
                                    <label class="check-field">
                                        <input name="days[{{ $weekday }}][enabled]" type="checkbox" value="1" @checked((bool) old("days.{$weekday}.enabled", $weekday <= 5))>
                                        {{ $weekdayName }}
                                    </label>

                                    <div class="field">
                                        <label class="label" for="start_time_new_{{ $weekday }}">Start</label>
                                        <input id="start_time_new_{{ $weekday }}" name="days[{{ $weekday }}][start_time]" type="time" value="{{ old("days.{$weekday}.start_time", '09:00') }}">
                                    </div>

                                    <div class="field">
                                        <label class="label" for="end_time_new_{{ $weekday }}">End</label>
                                        <input id="end_time_new_{{ $weekday }}" name="days[{{ $weekday }}][end_time]" type="time" value="{{ old("days.{$weekday}.end_time", '17:00') }}">
                                    </div>

                                    <div class="field">
                                        <label class="label" for="break_minutes_new_{{ $weekday }}">Break</label>
                                        <input id="break_minutes_new_{{ $weekday }}" name="days[{{ $weekday }}][break_minutes]" type="number" min="0" max="1440" step="1" value="{{ old("days.{$weekday}.break_minutes", 30) }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button class="button" type="submit">Add Schedule</button>
                    </form>

                    @if($managedSchedules->isEmpty())
                        <div class="empty">No schedules have been created yet.</div>
                    @else
                        <div class="schedule-stack">
                            @foreach($managedSchedules as $managedSchedule)
                                @php($scheduleDays = $managedSchedule->days->keyBy('weekday'))

                                <article class="schedule-editor">
                                    <div class="schedule-editor-head">
                                        <div>
                                            <h3>{{ $managedSchedule->name }}</h3>
                                            <div class="muted">
                                                {{ $managedSchedule->weekly_hours }} hours weekly
                                                @if(! $managedSchedule->active)
                                                    · inactive
                                                @endif
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('manage.schedules.destroy', $managedSchedule) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="danger-button" type="submit">Remove</button>
                                        </form>
                                    </div>

                                    <form class="manager-form" method="POST" action="{{ route('manage.schedules.update', $managedSchedule) }}">
                                        @csrf
                                        @method('PUT')

                                        <div class="form-grid">
                                            <div class="field">
                                                <label class="label" for="schedule_name_{{ $managedSchedule->id }}">Name</label>
                                                <input id="schedule_name_{{ $managedSchedule->id }}" name="name" value="{{ old('name', $managedSchedule->name) }}" required>
                                            </div>

                                            <div class="field">
                                                <label class="label" for="weekly_hours_{{ $managedSchedule->id }}">Weekly hours</label>
                                                <input id="weekly_hours_{{ $managedSchedule->id }}" name="weekly_hours" type="number" min="0" max="168" step="0.25" value="{{ old('weekly_hours', $managedSchedule->weekly_hours) }}" required>
                                            </div>

                                            <label class="check-field">
                                                <input name="active" type="checkbox" value="1" @checked((bool) old('active', $managedSchedule->active))>
                                                Active
                                            </label>
                                        </div>

                                        <div class="day-grid">
                                            @foreach($weekdays as $weekday => $weekdayName)
                                                @php($day = $scheduleDays->get($weekday))
                                                <div class="day-row">
                                                    <label class="check-field">
                                                        <input name="days[{{ $weekday }}][enabled]" type="checkbox" value="1" @checked((bool) old("days.{$weekday}.enabled", $day !== null))>
                                                        {{ $weekdayName }}
                                                    </label>

                                                    <div class="field">
                                                        <label class="label" for="start_time_{{ $managedSchedule->id }}_{{ $weekday }}">Start</label>
                                                        <input id="start_time_{{ $managedSchedule->id }}_{{ $weekday }}" name="days[{{ $weekday }}][start_time]" type="time" value="{{ old("days.{$weekday}.start_time", $day ? substr($day->start_time, 0, 5) : '09:00') }}">
                                                    </div>

                                                    <div class="field">
                                                        <label class="label" for="end_time_{{ $managedSchedule->id }}_{{ $weekday }}">End</label>
                                                        <input id="end_time_{{ $managedSchedule->id }}_{{ $weekday }}" name="days[{{ $weekday }}][end_time]" type="time" value="{{ old("days.{$weekday}.end_time", $day ? substr($day->end_time, 0, 5) : '17:00') }}">
                                                    </div>

                                                    <div class="field">
                                                        <label class="label" for="break_minutes_{{ $managedSchedule->id }}_{{ $weekday }}">Break</label>
                                                        <input id="break_minutes_{{ $managedSchedule->id }}_{{ $weekday }}" name="days[{{ $weekday }}][break_minutes]" type="number" min="0" max="1440" step="1" value="{{ old("days.{$weekday}.break_minutes", $day?->break_minutes ?? 30) }}">
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <button class="button" type="submit">Save Schedule</button>
                                    </form>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            <section class="card">
                <div class="page-head">
                    <div>
                        <h2>Time Corrections</h2>
                        <p class="lead">{{ $pendingCorrections->count() }} pending</p>
                    </div>
                </div>

                @if($pendingCorrections->isEmpty())
                    <div class="empty">No pending time correction requests.</div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Current</th>
                                    <th>Requested</th>
                                    <th>Reason</th>
                                    <th>Decision</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingCorrections as $correction)
                                    <tr>
                                        <td>
                                            <strong>{{ $correction->user->name }}</strong>
                                            <div class="muted">{{ $correction->user->employee_number }}</div>
                                        </td>
                                        <td>{{ $correction->date->format('d/m/Y') }}</td>
                                        <td>
                                            {{ $correction->current_clock_in ? substr($correction->current_clock_in, 0, 5) : '-' }}
                                            -
                                            {{ $correction->current_clock_out ? substr($correction->current_clock_out, 0, 5) : '-' }}
                                        </td>
                                        <td>
                                            <span class="requested">
                                                {{ $correction->requested_clock_in ? substr($correction->requested_clock_in, 0, 5) : '-' }}
                                                -
                                                {{ $correction->requested_clock_out ? substr($correction->requested_clock_out, 0, 5) : '-' }}
                                            </span>
                                        </td>
                                        <td>{{ $correction->reason }}</td>
                                        <td>
                                            <div class="row-actions">
                                                <form method="POST" action="{{ route('manage.time-entry-corrections.approve', $correction) }}">
                                                    @csrf
                                                    <textarea name="manager_notes" placeholder="Manager notes"></textarea>
                                                    <button class="button" type="submit">Approve</button>
                                                </form>

                                                <form method="POST" action="{{ route('manage.time-entry-corrections.deny', $correction) }}">
                                                    @csrf
                                                    <textarea name="manager_notes" placeholder="Manager notes"></textarea>
                                                    <button class="danger-button" type="submit">Deny</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="card">
                <div class="page-head">
                    <div>
                        <h2>Employee Schedules</h2>
                        <p class="lead">Assign the active schedule for employees in {{ $manager->company?->name ?? 'your company' }}.</p>
                    </div>
                </div>

                @if($employees->isEmpty())
                    <div class="empty">No employees found for this company.</div>
                @elseif($schedules->isEmpty())
                    <div class="empty">No active schedules are available for this company.</div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Current Schedule</th>
                                    <th>Set Schedule</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                    @php($activeSchedule = $employee->activeSchedule())
                                    <tr>
                                        <td>
                                            <strong>{{ $employee->name }}</strong>
                                            <div class="muted">{{ $employee->department ?? 'No department' }}</div>
                                        </td>
                                        <td>
                                            @if($activeSchedule)
                                                {{ $activeSchedule->name }}
                                            @else
                                                <span class="muted">No active schedule</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form class="schedule-form" method="POST" action="{{ route('manage.employees.schedule', $employee) }}">
                                                @csrf
                                                <div class="field schedule">
                                                    <label class="label" for="schedule_id_{{ $employee->id }}">Schedule</label>
                                                    <select id="schedule_id_{{ $employee->id }}" name="schedule_id" required>
                                                        @foreach($schedules as $schedule)
                                                            <option value="{{ $schedule->id }}" @selected($activeSchedule?->id === $schedule->id)>
                                                                {{ $schedule->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="field">
                                                    <label class="label" for="starts_at_{{ $employee->id }}">Starts</label>
                                                    <input id="starts_at_{{ $employee->id }}" name="starts_at" type="date" value="{{ now()->toDateString() }}">
                                                </div>

                                                <div class="field">
                                                    <label class="label" for="ends_at_{{ $employee->id }}">Ends</label>
                                                    <input id="ends_at_{{ $employee->id }}" name="ends_at" type="date">
                                                </div>

                                                <button class="button" type="submit">Set</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </main>
    </body>
</html>
