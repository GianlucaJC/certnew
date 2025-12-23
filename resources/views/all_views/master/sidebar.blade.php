<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Menu Navigazione</h5>
    </div>
    <div class="list-group list-group-flush">
        <a href="{{ route('firstpage') }}" class="list-group-item list-group-item-action {{ request()->routeIs('firstpage') ? 'active' : '' }}">
            <i class="fas fa-home fa-fw me-2"></i>Home
        </a>

        <a href="{{ route('elenco_lotti') }}" class="list-group-item list-group-item-action {{ request()->routeIs('elenco_lotti') ? 'active' : '' }} @if(!config('menu.full_menu_enabled', true)) disabled @endif">
            <i class="fas fa-industry fa-fw me-2"></i>Lotti
        </a>

        <a href="{{ route('elenco_provvisori') }}" class="list-group-item list-group-item-action {{ request()->routeIs('elenco_provvisori') ? 'active' : '' }} @if(!config('menu.full_menu_enabled', true)) disabled @endif">
            <i class="fas fa-edit fa-fw me-2"></i>Provvisori
        </a>

        <a href="#definitiviSubmenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('elenco_definitivi_idonei') || request()->routeIs('elenco_definitivi_non_idonei') ? 'true' : 'false' }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center @if(!config('menu.full_menu_enabled', true)) disabled @endif">
            <div>
                <i class="fas fa-check-double fa-fw me-2"></i>Definitivi
            </div>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse {{ request()->routeIs('elenco_definitivi_idonei') || request()->routeIs('elenco_definitivi_non_idonei') ? 'show' : '' }}" id="definitiviSubmenu">
            <div class="list-group list-group-flush">
                <a href="{{ route('elenco_definitivi_idonei', ['stato' => 'idoneo']) }}" class="list-group-item list-group-item-action ps-5 {{ request()->routeIs('elenco_definitivi_idonei') ? 'active' : '' }}">
                    <i class="fas fa-check-circle fa-fw me-2 text-success"></i>Idonei
                </a>
                <a href="{{ route('elenco_definitivi_non_idonei', ['stato' => 'non idoneo']) }}" class="list-group-item list-group-item-action ps-5 {{ request()->routeIs('elenco_definitivi_non_idonei') ? 'active' : '' }}">
                    <i class="fas fa-times-circle fa-fw me-2 text-danger"></i>Non Idonei
                </a>
            </div>
        </div>

        <a href="{{ route('elenco_master') }}" class="list-group-item list-group-item-action {{ request()->routeIs('elenco_master') ? 'active' : '' }}">
            <i class="fas fa-cogs fa-fw me-2"></i>Archivio Master
        </a>

        <a href="{{ route('sincro_master') }}" class="list-group-item list-group-item-action {{ request()->routeIs('sincro_master') ? 'active' : '' }}">
            <i class="fas fa-sync-alt fa-fw me-2"></i>Check Master
        </a>

        <a href="{{ route('guida_operatore') }}" class="list-group-item list-group-item-action {{ request()->routeIs('guida_operatore') ? 'active' : '' }}">
            <i class="fas fa-book-open fa-fw me-2"></i>Guida Operatore
        </a>
    </div>
</div>

{{-- Spazio per contenuto aggiuntivo specifico della pagina --}}
@yield('sidebar_extra_content')