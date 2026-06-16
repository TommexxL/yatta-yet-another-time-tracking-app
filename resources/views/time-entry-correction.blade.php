<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Request Correction - YATTA</title>

        <style>
            :root {
                --bg: #f4f7fb;
                --panel: #ffffff;
                --text: #172033;
                --muted: #667085;
                --line: #d8dee9;
                --primary: #0f766e;
                --primary-dark: #115e59;
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
                width: min(100% - 2rem, 46rem);
                margin: 0 auto;
                padding: 2rem 0;
            }

            .topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
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

            .card {
                border: 1px solid rgba(216, 222, 233, .9);
                border-radius: .75rem;
                background: var(--panel);
                box-shadow: 0 .75rem 2rem rgba(23, 32, 51, .07);
                overflow: hidden;
            }

            .card-header,
            form {
                padding: 1.5rem;
            }

            .card-header {
                border-bottom: 1px solid var(--line);
            }

            h1 {
                margin: 0;
                font-size: 1.65rem;
                line-height: 1.2;
            }

            .lead,
            .muted {
                color: var(--muted);
            }

            .lead {
                margin: .4rem 0 0;
            }

            .current {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
                margin-bottom: 1.25rem;
            }

            .item {
                border: 1px solid var(--line);
                border-radius: .6rem;
                padding: .9rem;
            }

            .label {
                display: block;
                margin-bottom: .35rem;
                color: var(--muted);
                font-size: .82rem;
                font-weight: 800;
                text-transform: uppercase;
            }

            input,
            textarea {
                width: 100%;
                border: 1px solid var(--line);
                border-radius: .5rem;
                padding: .65rem .75rem;
            }

            textarea {
                min-height: 8rem;
            }

            .field {
                margin-bottom: 1rem;
            }

            .errors {
                margin-bottom: 1rem;
                border-radius: .6rem;
                background: #fdecec;
                color: #991b1b;
                padding: .85rem 1rem;
            }

            .actions {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: .75rem;
            }

            .button,
            .link-button {
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

            @media (max-width: 38rem) {
                .topbar,
                .actions {
                    align-items: stretch;
                    flex-direction: column;
                }

                .current {
                    grid-template-columns: 1fr;
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

                <a class="link-button" href="{{ route('schedule') }}">Back to schedule</a>
            </header>

            <section class="card">
                <div class="card-header">
                    <h1>Request Correction</h1>
                    <p class="lead">{{ $date->format('l d M Y') }}</p>
                </div>

                <form method="POST" action="{{ route('time-entry.correction.store', ['date' => $date->toDateString()]) }}">
                    @csrf

                    @if($errors->any())
                        <div class="errors">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if($pendingCorrection)
                        <p class="muted">A correction request for this day is already waiting for manager review.</p>
                    @else
                        <div class="current">
                            <div class="item">
                                <span class="label">Current Clock In</span>
                                {{ $entry?->clock_in ? substr($entry->clock_in, 0, 5) : 'No entry' }}
                            </div>
                            <div class="item">
                                <span class="label">Current Clock Out</span>
                                {{ $entry?->clock_out ? substr($entry->clock_out, 0, 5) : 'No entry' }}
                            </div>
                        </div>

                        <div class="field">
                            <label class="label" for="requested_clock_in">Requested Clock In</label>
                            <input
                                id="requested_clock_in"
                                name="requested_clock_in"
                                type="time"
                                value="{{ old('requested_clock_in', $entry?->clock_in ? substr($entry->clock_in, 0, 5) : '') }}"
                            >
                        </div>

                        <div class="field">
                            <label class="label" for="requested_clock_out">Requested Clock Out</label>
                            <input
                                id="requested_clock_out"
                                name="requested_clock_out"
                                type="time"
                                value="{{ old('requested_clock_out', $entry?->clock_out ? substr($entry->clock_out, 0, 5) : '') }}"
                            >
                        </div>

                        <div class="field">
                            <label class="label" for="reason">Reason</label>
                            <textarea id="reason" name="reason" required>{{ old('reason') }}</textarea>
                        </div>

                        <div class="actions">
                            <a class="link-button" href="{{ route('schedule') }}">Cancel</a>
                            <button class="button" type="submit">Submit Request</button>
                        </div>
                    @endif
                </form>
            </section>
        </main>
    </body>
</html>
