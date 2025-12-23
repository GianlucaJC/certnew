<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="{{ URL::asset('/') }}dist/img/logo1.png" alt="CoA Logo" style="height: 40px;">
            Gestionale Certificati <span class="text-secondary fw-light ms-2">| {{ $title ?? '' }}</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a href="{{ route('logout') }}" class="btn btn-outline-primary"
               onclick="event.preventDefault(); this.closest('form').submit();">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </form>
    </div>
</nav>