@component('auth.layout', [
    'title' => 'Confirm password',
    'heading' => 'Confirm your password',
    'description' => 'For your security, please confirm your password before continuing.',
])
    <form method="POST" action="{{ route('password.confirm.store') }}">
        @csrf

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required autofocus>
        </div>

        <div class="actions">
            <a class="link" href="{{ url()->previous() }}">Cancel</a>
            <button class="button" type="submit">Confirm</button>
        </div>
    </form>
@endcomponent
