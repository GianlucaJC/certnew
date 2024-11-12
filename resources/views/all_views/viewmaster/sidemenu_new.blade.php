

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
      <img src="{{ URL::asset('/') }}dist/img/logo.png" alt="CoA Logo" class="brand-image  elevation-5" style="opacity: 5;" >
      
	  
	  
	  <span class="brand-text font-weight-light">Certificati</span>
    </a>

    <!-- Sidebar -->

    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
			
			 <li class="nav-item menu">
				<a href="#" class="nav-link">
				  <i class="fas fa-bars"></i>
				  <p>Main Menu
					<i class="right fas fa-angle-left"></i>
				  </p>
				</a>
				<ul class="nav nav-treeview">

				  <li class="nav-item">
					<a href="{{route('elenco_master')}}" class="nav-link">
					  <i class="far fa-list-alt"></i>
					  <p>Elenco Master</p>
					</a>
				  </li>				

				  <li class="nav-item">
					<a href="{{route('elenco_provvisori')}}" class="nav-link">
					  <i class="far fa-list-alt"></i>
					  <p>Elenco provvisori</p>
					</a>
				  </li>

				  <li class="nav-item">
					<a href="{{route('elenco_lotti')}}" class="nav-link">
					<i class="far fa-list-alt"></i>
					  <p>Elenco lotti</p>
					</a>
				  </li>				  
		
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



				</ul>
			  </li>
		
			



		  
			 
		  <li class="nav-item">
				<form method="POST" action="{{ route('logout') }}">
					@csrf
					  <li class="nav-item">
						<a href="#" class="nav-link" onclick="event.preventDefault();this.closest('form').submit();">
						  <i class="far fa-circle nav-icon"></i>
						  <p>Logout</p>
						</a>
					  </li>

				</form>	
          </li>
		</ul>  
      </nav>


    <!-- /.sidebar -->
  </aside>

