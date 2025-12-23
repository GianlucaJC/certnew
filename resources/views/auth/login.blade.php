<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><path d='M6 2h15l5 5v21H6V2zm14 1v4h4' fill='%23fff' stroke='%23000' stroke-width='2' stroke-linejoin='round'/><path d='m12 18 4 4 8-8' fill='none' stroke='%2304a24c' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/></svg>" type="image/svg+xml">
<x-guest-layout>
    <div class="card-body login-card-body">
        <p class="login-box-msg">{{ __('Accedi per iniziare la sessione') }}</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success mb-3" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Userid -->
            <div class="input-group mb-3">
                <input id="userid" type="text" name="userid" class="form-control @error('userid') is-invalid @enderror" placeholder="{{ __('Userid') }}" value="{{ old('userid') }}" required autofocus autocomplete="username">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-user"></span>
                    </div>
                </div>
                @error('userid')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <!-- Password -->
            <div class="input-group mb-3">
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ __('Password') }}" required autocomplete="current-password">
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                    </div>
                </div>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="row">
                <div class="col-8">
                    <div class="icheck-primary">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">
                            {{ __('Ricordami') }}
                        </label>
                    </div>
                </div>
                <!-- /.col -->
                <div class="col-4">
                    <button type="submit" class="btn btn-primary btn-block">{{ __('Accedi') }}</button>
                </div>
                <!-- /.col -->
            </div>
        </form>

        <p class="mb-1 mt-3" style='display:none'>
            <a href="{{ route('password.request') }}">{{ __('Password dimenticata?') }}</a>
        </p>
        <div class="mt-3 text-center">
            <small class="text-muted">© Liofilchem Srl</small>
            <br><small class="text-muted">Software development by Custom Software</small>
        </div>
    </div>
</x-guest-layout>
