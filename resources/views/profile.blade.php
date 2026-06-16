<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Profile - YATTA</title>

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
                background: linear-gradient(135deg, #eef4f8, var(--bg));
                color: var(--text);
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                line-height: 1.5;
            }

            .shell {
                width: min(100% - 2rem, 64rem);
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
                background: rgba(255, 255, 255, .94);
                box-shadow: 0 1rem 2.5rem rgba(23, 32, 51, .08);
                overflow: hidden;
            }

            .card-header {
                padding: 1.5rem;
                border-bottom: 1px solid var(--line);
            }

            h1 {
                margin: 0;
                font-size: 1.75rem;
                line-height: 1.2;
            }

            .lead {
                margin: .5rem 0 0;
                color: var(--muted);
            }

            .grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
                padding: 1.5rem;
            }

            .item {
                border: 1px solid var(--line);
                border-radius: .6rem;
                padding: 1rem;
                background: #fff;
            }

            .label {
                color: var(--muted);
                font-size: .85rem;
                font-weight: 700;
                text-transform: uppercase;
            }

            .value {
                margin-top: .25rem;
                font-size: 1rem;
                font-weight: 650;
                overflow-wrap: anywhere;
            }

            .button {
                display: inline-flex;
                min-height: 2.5rem;
                align-items: center;
                justify-content: center;
                border: 0;
                border-radius: .55rem;
                background: var(--primary);
                color: #fff;
                cursor: pointer;
                font: inherit;
                font-weight: 750;
                padding: .65rem 1rem;
                text-decoration: none;
            }

            .button:hover {
                background: var(--primary-dark);
            }

            @media (max-width: 42rem) {
                .topbar {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .grid {
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

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button" type="submit">Sign out</button>
                </form>
            </header>

            <section class="card">
                <div class="card-header">
                    <h1>{{ $user->name }}</h1>
                    <p class="lead">You are signed in through the regular Fortify authentication flow.</p>
                </div>

                <div class="card-header">
                    <h2>Time Tracking</h2>

                    <div style="display:flex; gap:1rem; margin-top:1rem;">
                        <a class="button" href="{{ route('schedule') }}">View Schedule</a>

                        @if($user->hasRole('manager'))
                            <a class="button" href="{{ route('manage.overview') }}">Manage</a>
                        @endif

                        @if(! $user->isClockedIn())
                            
                            <form method="POST" action="{{ route('clock-in') }}">
                                @csrf                                
                                <button class="button" type="submit">
                                    Clock In
                                </button>                                
                            </form>
                        @else
                            <div>
                                <p>
                                Clocked in at:
                                {{ \Carbon\Carbon::parse($user->activeTimeEntry()?->clock_in)->format('H:i') }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('clock-out') }}">
                                @csrf
                                <button class="button" type="submit">
                                    Clock Out
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="grid">
                    <div class="item">
                        <div class="label">Email</div>
                        <div class="value">{{ $user->email }}</div>
                    </div>

                    <div class="item">
                        <div class="label">Employee number</div>
                        <div class="value">{{ $user->employee_number }}</div>
                    </div>

                    <div class="item">
                        <div class="label">Company</div>
                        <div class="value">{{ $user->company?->name ?? 'No company assigned' }}</div>
                    </div>

                    <div class="item">
                        <div class="label">Department</div>
                        <div class="value">{{ $user->department ?? 'No department assigned' }}</div>
                    </div>

                    <div class="item">
                        <div class="label">Roles</div>
                        <div class="value">{{ $user->roles->pluck('name')->join(', ') ?: 'No roles assigned' }}</div>
                    </div>

                    <div class="item">
                        <div class="label">Account status</div>
                        <div class="value">{{ $user->active ? 'Active' : 'Inactive' }}</div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
