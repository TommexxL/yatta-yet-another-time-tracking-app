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
