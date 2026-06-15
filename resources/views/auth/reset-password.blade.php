@component('auth.layout', [
    'title' => 'Choose new password',
    'heading' => 'Choose a new password',
    'description' => 'Use a strong password that you do not use anywhere else.',
])
    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input name="token" type="hidden" value="{{ request()->route('token') }}">

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email', request('email')) }}" autocomplete="email" required autofocus>
        </div>

        <div class="field">
            <label for="password">New password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>

        <div class="actions">
            <a class="link" href="{{ route('login') }}">Back to sign in</a>
            <button class="button" type="submit">Reset password</button>
        </div>
    </form>
@endcomponent
