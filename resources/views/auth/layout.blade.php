<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'YATTA' }} - YATTA</title>

        <style>
            :root {
                color-scheme: light;
                --bg: #f4f7fb;
                --panel: #ffffff;
                --text: #172033;
                --muted: #667085;
                --line: #d8dee9;
                --primary: #0f766e;
                --primary-dark: #115e59;
                --danger: #b42318;
                --focus: rgba(15, 118, 110, .18);
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                background:
                    radial-gradient(circle at top left, rgba(15, 118, 110, .16), transparent 30rem),
                    linear-gradient(135deg, #eef4f8, var(--bg));
                color: var(--text);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                line-height: 1.5;
            }

            .auth-shell {
                display: grid;
                min-height: 100vh;
                place-items: center;
                padding: 2rem 1rem;
            }

            .auth-card {
                width: min(100%, 28rem);
                border: 1px solid rgba(216, 222, 233, .9);
                border-radius: .75rem;
                background: rgba(255, 255, 255, .94);
                box-shadow: 0 1.5rem 4rem rgba(23, 32, 51, .12);
                overflow: hidden;
            }

            .auth-header {
                padding: 2rem 2rem 1rem;
            }

            .brand {
                display: inline-flex;
                align-items: center;
                gap: .65rem;
                margin-bottom: 1.5rem;
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

            h1 {
                margin: 0;
                font-size: 1.65rem;
                line-height: 1.2;
            }

            .lead {
                margin: .7rem 0 0;
                color: var(--muted);
                font-size: .96rem;
            }

            .auth-body {
                padding: 0 2rem 2rem;
            }

            .status {
                margin-bottom: 1rem;
                border: 1px solid rgba(15, 118, 110, .22);
                border-radius: .5rem;
                background: rgba(15, 118, 110, .08);
                color: var(--primary-dark);
                padding: .8rem .9rem;
                font-size: .9rem;
            }

            .errors {
                margin-bottom: 1rem;
                border: 1px solid rgba(180, 35, 24, .2);
                border-radius: .5rem;
                background: rgba(180, 35, 24, .07);
                color: var(--danger);
                padding: .8rem .9rem;
                font-size: .9rem;
            }

            .errors ul {
                margin: 0;
                padding-left: 1.2rem;
            }

            .field {
                margin-top: 1rem;
            }

            label {
                display: block;
                margin-bottom: .4rem;
                font-size: .9rem;
                font-weight: 650;
            }

            input {
                width: 100%;
                border: 1px solid var(--line);
                border-radius: .55rem;
                background: #fff;
                color: var(--text);
                font: inherit;
                padding: .78rem .85rem;
                outline: none;
                transition: border-color .16s ease, box-shadow .16s ease;
            }

            input:focus {
                border-color: var(--primary);
                box-shadow: 0 0 0 .25rem var(--focus);
            }

            .checkbox-row {
                display: flex;
                align-items: center;
                gap: .6rem;
                margin-top: 1rem;
                color: var(--muted);
                font-size: .92rem;
            }

            .checkbox-row input {
                width: 1rem;
                height: 1rem;
                padding: 0;
            }

            .actions {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-top: 1.35rem;
                flex-wrap: wrap;
            }

            .button {
                display: inline-flex;
                min-height: 2.75rem;
                align-items: center;
                justify-content: center;
                border: 0;
                border-radius: .55rem;
                background: var(--primary);
                color: #fff;
                cursor: pointer;
                font: inherit;
                font-weight: 750;
                padding: .72rem 1rem;
                text-decoration: none;
            }

            .button:hover {
                background: var(--primary-dark);
            }

            .link {
                color: var(--primary-dark);
                font-weight: 650;
                text-decoration: none;
            }

            .link:hover {
                text-decoration: underline;
            }

            .helper {
                margin-top: 1.25rem;
                color: var(--muted);
                font-size: .9rem;
            }

            @media (max-width: 36rem) {
                .auth-header,
                .auth-body {
                    padding-left: 1.25rem;
                    padding-right: 1.25rem;
                }
            }
        </style>
    </head>
    <body>
        <main class="auth-shell">
            <section class="auth-card" aria-labelledby="auth-title">
                <header class="auth-header">
                    <div class="brand">
                        <span class="brand-mark">Y</span>
                        <span>YATTA</span>
                    </div>

                    <h1 id="auth-title">{{ $heading ?? $title ?? 'Welcome' }}</h1>

                    @isset($description)
                        <p class="lead">{{ $description }}</p>
                    @endisset
                </header>

                <div class="auth-body">
                    @if (session('status'))
                        <div class="status">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="errors">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </section>
        </main>
    </body>
</html>
