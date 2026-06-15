@component('auth.layout', [
    'title' => 'Sign in',
    'heading' => 'Sign in',
    'description' => 'Use your YATTA account to continue.',
])
    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>

        <label class="checkbox-row" for="remember">
            <input id="remember" name="remember" type="checkbox" value="1">
            <span>Remember me</span>
        </label>

        <div class="actions">
            <a class="link" href="{{ route('password.request') }}">Forgot password?</a>
            <button class="button" type="submit">Sign in</button>
        </div>
    </form>
@endcomponent
