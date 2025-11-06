<?php
$uri_info = request()->path();
$arr_uri=explode("/",$uri_info);
$current_uri=$arr_uri[0];
$url_base=url('/');

if (!Auth::user()) {
	header("location: $url_base/login");
	exit;
}
?>
<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#dc3545"/>

  <title>@yield('title')</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="{{ URL::asset('/') }}plugins/fontawesome-free/css/all.min.css">
  

   <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.css">

	@yield('extra_style')  


  <!-- Theme style -->
  <link rel="stylesheet" href="{{ URL::asset('/') }}dist/css/styles.css">
  <link rel="stylesheet" href="{{ URL::asset('/') }}dist/css/adminlte.min.css">
</head>


<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      
	  <li class="nav-item  d-sm-inline-block">
		<?php
			if (1==2) {
			$referer = $_SERVER['HTTP_REFERER'] ?? null;
			$uri_complete = request()->path();
			//if ($uri_complete!="menu") {	
				$referer="#";
				echo "<span id='id_back'>";
					echo "<a href='$referer' onclick='history.back()' class='nav-link'>";	
						echo "<button type='button' class='btn btn-secondary btn-sm'>Indietro</button>";
					echo "</a>";
				echo "</span>";
			//}

			}
		?>
	</li>	
	@yield('extra_button_home')  
	<li class="nav-item  d-sm-inline-block">	
		@if (1==2)
        <a href="{{ route('elenco_provvisori') }}" class="nav-link">
			<button type="button" class="btn btn-primary btn-sm">Homepage</button>	
		</a>
		@endif
      </li>
	  <!--
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li>
	  !-->
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Navbar Search -->
      <li class="nav-item" style='display:none'>
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fas fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Cerca" aria-label="Cerca">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>

      <?php if (1==2) { ?>
		  <!-- Messages Dropdown Menu -->
		  <li class="nav-item dropdown">
			<a class="nav-link" data-toggle="dropdown" href="#">
			  <i class="far fa-comments"></i>
			  <span class="badge badge-danger navbar-badge">3</span>
			</a>
			<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
			  <a href="#" class="dropdown-item">
				<!-- Message Start -->
				<div class="media">
				  <img src="dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
				  <div class="media-body">
					<h3 class="dropdown-item-title">
					  Brad Diesel
					  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
					</h3>
					<p class="text-sm">Call me whenever you can...</p>
					<p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
				  </div>
				</div>
				<!-- Message End -->
			  </a>
			  <div class="dropdown-divider"></div>
			  <a href="#" class="dropdown-item">
				<!-- Message Start -->
				<div class="media">
				  <img src="{{ URL::asset('/') }}dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
				  <div class="media-body">
					<h3 class="dropdown-item-title">
					  John Pierce
					  <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
					</h3>
					<p class="text-sm">I got your message bro</p>
					<p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
				  </div>
				</div>
				<!-- Message End -->
			  </a>
			  <div class="dropdown-divider"></div>
			  <a href="#" class="dropdown-item">
				<!-- Message Start -->
				<div class="media">
				  <img src="{{ URL::asset('/') }}dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
				  <div class="media-body">
					<h3 class="dropdown-item-title">
					  Nora Silvester
					  <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
					</h3>
					<p class="text-sm">The subject goes here</p>
					<p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
				  </div>
				</div>
				<!-- Message End -->
			  </a>
			  <div class="dropdown-divider"></div>
			  <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
			</div>
		  </li>
	  <?php } ?>
      <!-- Notifications Dropdown Menu -->
		@yield('notifiche')

      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->
  

   @extends('all_views.viewmaster.sidemenu_new')

   <span id='credit_top'>
	<center>Sviluppo prototipale by <b>Custom Software</b></center>
   </span> 
   @yield('content_main')  

   @extends('all_views.viewmaster.sidebar')

  <!-- Main Footer -->
  <footer class="main-footer" style='display:none'>
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
       All rights reserved.
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; <?php echo date("Y"); ?>
	<a href="#">Misericordia</a></strong>
  </footer>
  
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->


@yield('content_plugin')  

</body>
</html>
