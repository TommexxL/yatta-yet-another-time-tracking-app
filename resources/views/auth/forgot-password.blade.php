@component('auth.layout', [
    'title' => 'Reset password',
    'heading' => 'Reset your password',
    'description' => 'Enter your email address and we will send a password reset link.',
])
    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        </div>

        <div class="actions">
            <a class="link" href="{{ route('login') }}">Back to sign in</a>
            <button class="button" type="submit">Send reset link</button>
        </div>
    </form>
@endcomponent
