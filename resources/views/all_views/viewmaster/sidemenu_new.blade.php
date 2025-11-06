

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
<?php
// Helper per controllare se una rotta è attiva
$currentRoute = request()->route()->getName();

// Legge il valore dal file di configurazione
$fullMenuEnabled = config('menu.full_menu_enabled', false);

// Controlla se una delle voci del sottomenu è attiva
$isMainMenuActive = in_array($currentRoute, [
    'elenco_master',
    'elenco_provvisori',
    'elenco_definitivi_idonei',
    'elenco_definitivi_non_idonei',
    'elenco_lotti',
    'firstpage',
    'guida_operatore',
]);
?>
    <a href="#" class="brand-link">
      <img src="{{ URL::asset('/') }}dist/img/logo1.png" alt="CoA Logo" class="brand-image  elevation-5" style="opacity: 5;" >
	  <span class="brand-text font-weight-light">Certificati</span>
    </a>

    <!-- Sidebar -->

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
			 <li class="nav-item menu {{ $isMainMenuActive ? 'menu-open' : '' }}">
				<a href="#" class="nav-link">
				  <i class="fas fa-bars"></i>
				  <p>Main Menu
					<i class="right fas fa-angle-left"></i>
				  </p>
				</a>
				<ul class="nav nav-treeview" style="{{ $isMainMenuActive ? 'display: block;' : '' }}">
				@if ($fullMenuEnabled)
					<li class="nav-item">
					<a href="{{ route('guida_operatore') }}" class="nav-link {{ request()->routeIs('guida_operatore') ? 'active' : '' }}">
						<i class="nav-icon fas fa-question-circle"></i>
						<p>
						Guida Operatore
						</p>
					</a>
					</li>
				@endif


				@if ($fullMenuEnabled)
				  <li class="nav-item">
					<a href="{{route('firstpage')}}" class="nav-link {{ request()->routeIs('firstpage') ? 'active' : '' }}">
					  <i class="fas fa-home nav-icon"></i>
					  <p>Home</p>
					</a>
				  </li>
				  <hr>
				@endif
				  <li class="nav-item">
					<a href="{{ route('sincro_master') }}" class="nav-link {{ request()->routeIs('sincro_master') ? 'active' : '' }}">
					  <i class="fas fa-sync-alt nav-icon"></i>
					  <p>Check Master</p>
					</a>
				  </li>
				  <hr>

				  <li class="nav-item">
					<a href="{{route('elenco_master')}}" class="nav-link {{ request()->routeIs('elenco_master') ? 'active' : '' }}">
					  <i class="fas fa-file-alt nav-icon"></i>
					  <p>Elenco Master</p>
					</a>
				  </li>				

				@if ($fullMenuEnabled)
				  <li class="nav-item">
					<a href="{{route('elenco_provvisori')}}" class="nav-link {{ request()->routeIs('elenco_provvisori') ? 'active' : '' }}">
					  <i class="fas fa-file-signature nav-icon"></i>
					  <p>Elenco provvisori</p>
					</a>
				  </li>

				  <li class="nav-item">
					<a href="{{route('elenco_definitivi_idonei')}}" class="nav-link {{ request()->routeIs('elenco_definitivi_idonei') ? 'active' : '' }}">
					  <i class="fas fa-check-circle nav-icon"></i>
					  <p>Definitivi idonei</p>
					</a>
				  </li>

				  <li class="nav-item">
					<a href="{{route('elenco_definitivi_non_idonei')}}" class="nav-link {{ request()->routeIs('elenco_definitivi_non_idonei') ? 'active' : '' }}">
					  <i class="fas fa-times-circle nav-icon"></i>
					  <p>Definitivi non idonei</p>
					</a>
				  </li>				  

				  <hr>

				  <li class="nav-item">
					<a href="{{route('elenco_lotti')}}" class="nav-link {{ request()->routeIs('elenco_lotti') ? 'active' : '' }}">
					<i class="fas fa-boxes nav-icon"></i>
					  <p>Elenco lotti</p>
					</a>
				  </li>		
				@endif
				  
				  <HR>
				<!--
					 <li class="nav-item menu">
						<a href="#" class="nav-link">
						  <i class="fas fa-cogs"></i>
						  <p>Archivi
							<i class="right fas fa-angle-left"></i>
						  </p>
						</a>
						<ul class="nav nav-treeview">
						  <li class="nav-item">
							<a href="{{route('elenco_lotti')}}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Test1</p>
							</a>
						  </li>
						
						  <li class="nav-item">
							<a href="{{route('elenco_lotti')}}" class="nav-link ">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Test2</p>
							</a>
						  </li>

						</ul>

					 </li>		
				!-->	 			  



				</ul>
			  </li>
		
			



		  
			 
		  <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="#" class="nav-link" onclick="event.preventDefault(); this.closest('form').submit();">
                    <i class="fas fa-sign-out-alt nav-icon"></i>
                    <p>Logout</p>
                </a>
            </form>
          </li>
		</ul>  
      </nav>


    <!-- /.sidebar -->
  </aside>
