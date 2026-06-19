<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Request Leave - YATTA</title>

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
                width: min(100% - 2rem, 44rem);
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

            .card-head,
            .form-body {
                padding: 1.5rem;
            }

            .card-head {
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

            .form-body {
                display: grid;
                gap: 1rem;
            }

            .field-grid {
                display: grid;
                gap: 1rem;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .label {
                display: block;
                margin-bottom: .25rem;
                color: var(--muted);
                font-size: .78rem;
                font-weight: 800;
                text-transform: uppercase;
            }

            select,
            input,
            textarea {
                width: 100%;
                border: 1px solid var(--line);
                border-radius: .5rem;
                padding: .65rem .75rem;
                font: inherit;
            }

            textarea {
                min-height: 7rem;
                resize: vertical;
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

            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: .75rem;
            }

            .flash {
                margin-bottom: 1rem;
                border-radius: .6rem;
                padding: .85rem 1rem;
                font-weight: 650;
            }

            .flash.error {
                background: #fdecec;
                color: #991b1b;
            }

            @media (max-width: 38rem) {
                .topbar,
                .field-grid {
                    align-items: stretch;
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

            @if(session('error'))
                <div class="flash error">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="flash error">{{ $errors->first() }}</div>
            @endif

            <section class="card">
                <div class="card-head">
                    <h1>Request Leave</h1>
                    <p class="lead">Submit vacation or sick leave for manager review.</p>
                </div>

                <form class="form-body" method="POST" action="{{ route('leave-request.store') }}">
                    @csrf

                    <div>
                        <label class="label" for="type">Type</label>
                        <select id="type" name="type" required>
                            <option value="vacation" @selected(old('type', $defaultType) === 'vacation')>Vacation</option>
                            <option value="sick" @selected(old('type', $defaultType) === 'sick')>Sick leave</option>
                        </select>
                    </div>

                    <div class="field-grid">
                        <div>
                            <label class="label" for="start_date">Start date</label>
                            <input id="start_date" name="start_date" type="date" value="{{ old('start_date', $defaultDate) }}" required>
                        </div>

                        <div>
                            <label class="label" for="end_date">End date</label>
                            <input id="end_date" name="end_date" type="date" value="{{ old('end_date', $defaultDate) }}" required>
                        </div>
                    </div>

                    <div>
                        <label class="label" for="reason">Reason</label>
                        <textarea id="reason" name="reason" placeholder="Optional note for your manager">{{ old('reason') }}</textarea>
                    </div>

                    <div class="actions">
                        <button class="button" type="submit">Submit Request</button>
                        <a class="link-button" href="{{ route('schedule') }}">Cancel</a>
                    </div>
                </form>
            </section>
        </main>
    </body>
</html>
