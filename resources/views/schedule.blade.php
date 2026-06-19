<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Schedule - YATTA</title>

        <style>
            :root {
                --bg: #f4f7fb;
                --panel: #ffffff;
                --text: #172033;
                --muted: #667085;
                --line: #d8dee9;
                --primary: #0f766e;
                --primary-dark: #115e59;
                --soft: #eef6f5;
                --warning: #9a6700;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                background: #f4f7fb;
                color: var(--text);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                line-height: 1.5;
            }

            .shell {
                width: min(100% - 2rem, 72rem);
                margin: 0 auto;
                padding: 2rem 0;
            }

            .topbar,
            .page-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
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

            h1 {
                margin: 0;
                font-size: 1.75rem;
                line-height: 1.2;
            }

            .lead {
                margin: .4rem 0 0;
                color: var(--muted);
            }

            .button,
            .link-button {
                display: inline-flex;
                min-height: 2.4rem;
                align-items: center;
                justify-content: center;
                border: 0;
                border-radius: .5rem;
                cursor: pointer;
                font: inherit;
                font-weight: 750;
                padding: .55rem .85rem;
                text-decoration: none;
                white-space: nowrap;
            }

            .button {
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

            tbody tr.today {
                background: var(--soft);
            }

            .day {
                min-width: 8rem;
                font-weight: 800;
            }

            .date {
                display: block;
                margin-top: .2rem;
                color: var(--muted);
                font-size: .9rem;
                font-weight: 600;
            }

            .muted {
                color: var(--muted);
            }

            .badge {
                display: inline-flex;
                align-items: center;
                border-radius: 999px;
                background: #fff7df;
                color: var(--warning);
                font-size: .82rem;
                font-weight: 800;
                padding: .25rem .6rem;
            }

            .action-stack {
                display: flex;
                flex-wrap: wrap;
                gap: .5rem;
            }

            @media (max-width: 44rem) {
                .topbar,
                .page-head {
                    align-items: flex-start;
                    flex-direction: column;
                }

                th,
                td {
                    padding: .85rem;
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

            <section class="card">
                <div class="page-head">
                    <div>
                        <h1>This Week</h1>
                        <p class="lead">
                            {{ $weekStart->format('d M Y') }} - {{ $weekEnd->format('d M Y') }}
                            @if($schedule)
                                · {{ $schedule->name }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Scheduled</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Status</th>
                                <th>Correction</th>
                                <th>Leave</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($weekDays as $day)
                                @php
                                    $date = $day['date'];
                                    $scheduleDay = $day['scheduleDay'];
                                    $entry = $day['entry'];
                                    $correction = $day['correction'];
                                    $leaveRequest = $day['leaveRequest'];
                                    $sickLeave = $day['sickLeave'];
                                    $approvedLeaveRequest = $day['approvedLeaveRequest'];
                                    $approvedSickLeave = $day['approvedSickLeave'];
                                @endphp
                                <tr @class(['today' => $date->isToday()])>
                                    <td class="day">
                                        {{ $date->format('l') }}
                                        <span class="date">{{ $date->format('d/m') }}</span>
                                    </td>
                                    <td>
                                        @if($approvedSickLeave)
                                            <span class="badge">Sick</span>
                                        @elseif($approvedLeaveRequest)
                                            <span class="badge">Vacation</span>
                                        @elseif($scheduleDay)
                                            {{ substr($scheduleDay->start_time, 0, 5) }}
                                            -
                                            {{ substr($scheduleDay->end_time, 0, 5) }}
                                            <span class="date">{{ $scheduleDay->break_minutes }} min break</span>
                                        @else
                                            <span class="muted">No planned hours</span>
                                        @endif
                                    </td>
                                    <td>{{ $entry?->clock_in ? substr($entry->clock_in, 0, 5) : '-' }}</td>
                                    <td>{{ $entry?->clock_out ? substr($entry->clock_out, 0, 5) : '-' }}</td>
                                    <td>
                                        @if($entry)
                                            {{ ucfirst($entry->status->value) }}
                                        @else
                                            <span class="muted">No entry</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($correction)
                                            <span class="badge">Pending</span>
                                        @else
                                            <a class="link-button" href="{{ route('time-entry.correction.create', ['date' => $date->toDateString()]) }}">
                                                Request Change
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        @if($approvedSickLeave)
                                            <span class="badge">Sick leave approved</span>
                                        @elseif($approvedLeaveRequest)
                                            <span class="badge">Vacation approved</span>
                                        @elseif($leaveRequest)
                                            <span class="badge">Vacation pending</span>
                                        @elseif($sickLeave)
                                            <span class="badge">Sick leave pending</span>
                                        @else
                                            <div class="action-stack">
                                                <a class="link-button" href="{{ route('leave-request.create', ['date' => $date->toDateString(), 'type' => 'vacation']) }}">
                                                    Vacation
                                                </a>
                                                <a class="link-button" href="{{ route('leave-request.create', ['date' => $date->toDateString(), 'type' => 'sick']) }}">
                                                    Sick
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </body>
</html>
