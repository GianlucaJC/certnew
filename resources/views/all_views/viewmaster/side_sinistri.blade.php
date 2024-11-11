  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('menu') }}" class="brand-link">
      <img src="{{ URL::asset('/') }}dist/img/4.png" alt="IngFUN Logo" class="brand-image img-circle elevation-5" style="opacity: 5;" >
      
	  
	  
	  <span class="brand-text font-weight-light">DayByDay</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">

			<img src="{{ URL::asset('/') }}dist/img/avatar1.png" class="img-circle elevation-2" alt="User Image">
		
        </div>
        <div class="info">
          <a href="#" class="d-block"></a>
		
		<ul class="nav nav-pills nav-sidebar" role="menu" data-accordion="false">
		  <li class="nav-item">
				<form method="POST" action="{{ route('logout') }}">
					@csrf
					 <!--
					  <li class="nav-item">
						<a href="#" class="nav-link" onclick="event.preventDefault();this.closest('form').submit();">
						  <i class="far fa-circle nav-icon"></i>
						  <p>Logout</p>
						</a>
					  </li>
					  !-->

				</form>	
          </li>
		</ul>
        </div>


		
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="true">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
			

			 

		</ul>  
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
