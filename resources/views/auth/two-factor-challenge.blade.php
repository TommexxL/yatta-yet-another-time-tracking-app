@component('auth.layout', [
    'title' => 'Two-factor verification',
    'heading' => 'Two-factor verification',
    'description' => 'Enter the code from your authenticator app, or use a recovery code.',
])
    <form method="POST" action="{{ route('two-factor.login.store') }}">
        @csrf

        <div class="field">
            <label for="code">Authenticator code</label>
            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" autofocus>
        </div>

        <div class="field">
            <label for="recovery_code">Recovery code</label>
            <input id="recovery_code" name="recovery_code" type="text" autocomplete="one-time-code">
        </div>

        <p class="helper">Use one field only. Recovery codes are single-use.</p>

        <div class="actions">
            <a class="link" href="{{ route('login') }}">Back to sign in</a>
            <button class="button" type="submit">Verify</button>
        </div>
    </form>
@endcomponent
