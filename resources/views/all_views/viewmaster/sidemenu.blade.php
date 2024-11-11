
<?php

use App\Models\User;
	$id = Auth::user()->id;
	$user = User::find($id);
?>

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
			@if ($user->hasRole('admin'))
				<img src="{{ URL::asset('/') }}dist/img/AdminLTELogo.png" class="img-circle elevation-2" alt="User Image">
			@elseif ($user->hasRole('coord'))
				<img src="{{ URL::asset('/') }}dist/img/coord.png" class="img-circle elevation-2" alt="User Image">
			@elseif ($user->hasRole('resp'))
				<img src="{{ URL::asset('/') }}dist/img/resp.png" class="img-circle elevation-2" alt="User Image">
			@else	
				<img src="{{ URL::asset('/') }}dist/img/avatar1.png" class="img-circle elevation-2" alt="User Image">
			@endif
        </div>
        <div class="info">
          <a href="#" class="d-block">{{ Auth::user()->name }}</a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Cerca" aria-label="Cerca">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
			@if ($user->hasRole('coord'))
			 <li class="nav-item menu">
				<a href="#" class="nav-link">
				  <i class="nav-icon fas fa-users"></i>
				  <p>Risorse Umane
					<i class="right fas fa-angle-left"></i>
				  </p>
				</a>
				<ul class="nav nav-treeview">
				  <li class="nav-item">
					<a href="{{ route('registro') }}" class="nav-link">
					  <i class="far fa-circle nav-icon"></i>
					  <p>Registro Servizi</p>
					</a>
				  </li>	
				</ul>  
			 </li>
			@endif
			
			
			@if ($user->hasRole('admin'))
			 <li class="nav-item menu">
				<a href="#" class="nav-link">
				  <i class="nav-icon fas fa-users"></i>
				  <p>Risorse Umane
					<i class="right fas fa-angle-left"></i>
				  </p>
				</a>
				<ul class="nav nav-treeview">
				  <li class="nav-item">
					<a href="{{ route('newcand') }}" class="nav-link">
					  <i class="far fa-circle nav-icon"></i>
					  <p>Nuova candidatura</p>
					</a>
				  </li>
				  <li class="nav-item">
					<a href="{{ route('listcand') }}" class="nav-link">
					  <i class="far fa-circle nav-icon"></i>
					  <p>Lista candidature</p>
					</a>
				  </li>
				  
				  <li class="nav-item">
					<a href="{{ route('listpers') }}" class="nav-link">
					  <i class="far fa-circle nav-icon"></i>
					  <p>Gestione Personale</p>
					</a>
				  </li>				  

				  <li class="nav-item">
					<a href="{{ route('scadenze_contratti') }}" class="nav-link">
					  <i class="far fa-circle nav-icon"></i>
					  <p>Scadenze Contratti</p>
					</a>
				  </li>	

				  <li class="nav-item">
					<a href="{{ route('registro') }}" class="nav-link">
					  <i class="far fa-circle nav-icon"></i>
					  <p>Registro Servizi</p>
					</a>
				  </li>	

				
				  <li class="nav-item">
					<a href="{{route('giustificativi')}}" class="nav-link">
					  <i class="far fa-circle nav-icon" nav-icon"></i>
					  <p>Giustificativi</p>
					</a>
				  </li>	
					  

				  <li class="nav-item">
					<a href="{{ route('cedolini_up') }}" class="nav-link">
					  <i class="far fa-circle nav-icon"></i>
					  <p>Upload/Sospesi Cedolini</p>
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
							<a href="{{ route('tipologia_contr') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Tipologie di Contratto</p>
							</a>
						  </li>
						
						  <li class="nav-item">
							<a href="{{ route('tipo_contratto') }}" class="nav-link ">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Tipo Contratto</p>
							</a>
						  </li>
						  <li class="nav-item">
							<a href="{{ route('mansione') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Mansioni</p>
							</a>
						  </li>
						  <li class="nav-item">
							<a href="{{ route('frm_attestati') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Corsi Formazione</p>
							</a>
						  </li>
						  
						  <li class="nav-item">
							<a href="{{ route('societa_assunzione') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Società di assunzione</p>
							</a>
						  </li>
						  <li class="nav-item">
							<a href="{{ route('costo') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Centri di Costo</p>
							</a>
						  </li>
						  <li class="nav-item">
							<a href="{{ route('area_impiego') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Aree di impiego</p>
							</a>
						  </li>
						  <li class="nav-item">
							<a href="{{ route('ccnl') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Contratti CCNL</p>
							</a>
						  </li>

						  <li class="nav-item">
							<a href="{{ route('tipo_documento') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Tipologie di Documento</p>
							</a>
						  </li>				  

						  <li class="nav-item">
							<a href="{{ route('sotto_tipo_documento') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>SottoTipo di Documento</p>
							</a>
						  </li>

						  <li class="nav-item">
							<a href="{{ route('documenti') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Area Documenti</p>
							</a>
						  </li>
						  <li class="nav-item">
							<a href="{{ route('contatti') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Contatti interni</p>
							</a>
						  </li>
						</ul>

					 </li>					  



				</ul>
			  </li>
			 @endif 
			

			@if ($user->hasRole('resp'))
			 <li class="nav-item menu">
				<a href="#" class="nav-link">
				  <i class="nav-icon fas fa-cubes"></i>
				  <p>Amministrazione
					<i class="right fas fa-angle-left"></i>
				  </p>
				</a>
				<ul class="nav nav-treeview">

				 <li class="nav-item menu">
					<a href="#" class="nav-link">
					  <i class="nav-icon fas fa-database"></i>
					  <p>Appalti
						<i class="right fas fa-angle-left"></i>
					  </p>
					</a>
					<ul class="nav nav-treeview">
					  <li class="nav-item">

					  
						<a href="{{ route('newapp',['id'=>0,'from'=>1,'num_send'=>0]) }}" class="nav-link">
						  <i class="far fa-circle nav-icon" nav-icon"></i>
						  <p>Nuovo Appalto</p>
						</a>
					  </li>	
					</ul>
					<ul class="nav nav-treeview">
					  <li class="nav-item">
						<a href="{{route('listapp')}}" class="nav-link">
						  <i class="far fa-circle nav-icon" nav-icon"></i>
						  <p>Lista Appalti</p>
						</a>
					  </li>	
					</ul>
					<ul class="nav nav-treeview">
					  <li class="nav-item">
						<a href="{{route('rifornimenti')}}" class="nav-link">
						  <i class="far fa-circle nav-icon" nav-icon"></i>
						  <p>Lista Rifornimenti</p>
						</a>
					  </li>	
					</ul>
				  </li>
				 </ul> 
			    </li>	
			@endif

			@if ($user->hasRole('admin') || $user->hasRole('coord'))
				
			 <li class="nav-item menu">
			 <a href="#" class="nav-link">
			   <i class="nav-icon fas fa-cubes"></i>
			   <p>Amministrazione
				<i class="right fas fa-angle-left"></i>
			   </p>
			 </a>
			 <ul class="nav nav-treeview">
					 <li class="nav-item menu">
						<a href="#" class="nav-link">
						  <i class="nav-icon fas fa-industry"></i>
						  <p>Aziende di Proprietà
							<i class="right fas fa-angle-left"></i>
						  </p>
						</a>
						<ul class="nav nav-treeview">

						  <li class="nav-item">
							<a href="{{route('sezionali')}}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Anagrafiche</p>
							</a>
						  </li>	
						</ul>
					</li>	
					
					<li class="nav-item">
						<a href="{{route('ditte')}}" class="nav-link">
						  <i class="far fa-circle nav-icon"></i>
						  <p>Anagrafica clienti</p>
						</a>
					</li>
					
					<li class="nav-item">
						<a href="{{route('lista_preventivi')}}" class="nav-link">
						  <i class="far fa-circle nav-icon"></i>
						  <p>Elenco Preventivi</p>
						</a>
					</li>


					<li class="nav-item">
						<a href="{{route('preventivo')}}" class="nav-link">
						  <i class="far fa-circle nav-icon"></i>
						  <p>Nuovo Preventivo</p>
						</a>
					</li>


					<li class="nav-item">
						<a href="{{route('servizi')}}" class="nav-link">
						  <i class="far fa-circle nav-icon"></i>
						  <p>Listino Clienti</p>
						</a>
					</li>	
				  <li class="nav-item">
					<a href="{{route('aliquote')}}" class="nav-link">
					  <i class="far fa-circle nav-icon"></i>
					  <p>Aliquote Iva</p>
					</a>
				  </li>	
				  <li class="nav-item">
					<a href="{{route('gestione_servizi')}}" class="nav-link">
					  <i class="far fa-circle nav-icon"></i>
					  <p>Gestione Servizi</p>
					</a>
				  </li>						
			 
			 
			 

			<li class="nav-item menu">
				<a href="#" class="nav-link">
				  <i class="nav-icon fas fa-database"></i>
				  <p>Appalti
					<i class="right fas fa-angle-left"></i>
				  </p>
				</a>
				<ul class="nav nav-treeview">
				  <li class="nav-item">

				  
					<a href="{{ route('newapp',['id'=>0,'from'=>1,'num_send'=>0]) }}" class="nav-link">
					  <i class="far fa-circle nav-icon" nav-icon"></i>
					  <p>Nuovo Appalto</p>
					</a>
				  </li>	
				</ul>
				<ul class="nav nav-treeview">
				  <li class="nav-item">
					<a href="{{route('listapp')}}" class="nav-link">
					  <i class="far fa-circle nav-icon" nav-icon"></i>
					  <p>Lista Appalti</p>
					</a>
				  </li>	
				</ul>
				<ul class="nav nav-treeview">
				  <li class="nav-item">
					<a href="{{route('listrep')}}" class="nav-link">
					  <i class="far fa-circle nav-icon" nav-icon"></i>
					  <p>Lista Reperibilità</p>
					</a>
				  </li>	
				</ul>
				<ul class="nav nav-treeview">
				  <li class="nav-item">
					<a href="{{route('rifornimenti')}}" class="nav-link">
					  <i class="far fa-circle nav-icon" nav-icon"></i>
					  <p>Lista Rifornimenti</p>
					</a>
				  </li>	
				</ul>
			  </li>
				 

				 
				 <li class="nav-item menu">
					<a href="#" class="nav-link">
					  <i class="nav-icon fas fa-file-invoice"></i>
					  <p>Inviti a fatturare
						<i class="right fas fa-angle-left"></i>
					  </p>
					</a>
					<ul class="nav nav-treeview">
			
					  <li class="nav-item">
						<a href="{{route('invito')}}" class="nav-link">
						  <i class="far fa-circle nav-icon"></i>
						  <p>Nuovo Invito a fatturare</p>
						</a>
					  </li>
					  <li class="nav-item">
						<a href="{{route('lista_inviti')}}" class="nav-link">
						  <i class="far fa-circle nav-icon"></i>
						  <p>Lista Inviti a fatturare</p>
						</a>
					  </li>
					</ul>
				 </li>	
				@if ($user->hasRole('admin') || $user->hasRole('coord') || $user->hasRole('resp'))
				 <li class="nav-item menu">
					<a href="#" class="nav-link">
					  <i class="nav-icon fas fa-car"></i>
					  <p>Parco Auto
						<i class="right fas fa-angle-left"></i>
					  </p>
					</a>
					<ul class="nav nav-treeview">
			
					  <li class="nav-item">
						<a href="{{route('scheda_mezzo')}}" class="nav-link">
						  <i class="far fa-circle nav-icon"></i>
						  <p>Nuovo mezzo</p>
						</a>
					  </li>

					  <li class="nav-item">
						<a href="{{route('inventario_flotta')}}" class="nav-link">
						  <i class="far fa-circle nav-icon"></i>
						  <p>Inventario della flotta</p>
						</a>
					  </li>
					
					  <li class="nav-item">
						<a href="{{route('riparazioni')}}" class="nav-link">
						  <i class="far fa-circle nav-icon"></i>
						  <p>Riparazione mezzi</p>
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
							<a href="{{ route('marca') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Marca mezzi</p>
							</a>
						  </li>
						  <li class="nav-item">
							<a href="{{ route('modello') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Modello mezzi</p>
							</a>
						  </li>
						  <li class="nav-item">
							<a href="{{ route('cartac') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Carta carburante</p>
							</a>
						  </li>
						  <li class="nav-item">
							<a href="{{ route('badge') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Badge cisterna</p>
							</a>
						  </li>
						  <li class="nav-item">
							<a href="{{ route('telepass') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Telepass</p>
							</a>
						  </li>						  
						  <li class="nav-item">
							<a href="{{ route('servizi_noleggio') }}" class="nav-link">
							  <i class="far fa-circle nav-icon"></i>
							  <p>Servizi noleggio</p>
							</a>
						  </li>							
						</ul>
					</li>	
					</ul>
				 </li>	



				 <li class="nav-item menu">
					<a href="#" class="nav-link">
					  <i class="nav-icon fas fa-euro-sign"></i>
					  <p>Ufficio acquisti
						<i class="right fas fa-angle-left"></i>
					  </p>
					</a>
					<ul class="nav nav-treeview">

					
						 <li class="nav-item menu">
							<a href="#" class="nav-link">
							  <i class="nav-icon fas fa-file-alt"></i>
							  <p>Gestione prodotti
								<i class="right fas fa-angle-left"></i>
							  </p>
							</a>
							<ul class="nav nav-treeview">

							  <li class="nav-item">
								<a href="{{route('elenco_articoli')}}" class="nav-link">
								  <i class="far fa-circle nav-icon"></i>
								  <p>Elenco prodotti</p>
								</a>
							  </li>	
							</ul>  
						
							<ul class="nav nav-treeview">
							  <li class="nav-item">
								<a href="{{route('magazzini')}}" class="nav-link">
								  <i class="far fa-circle nav-icon"></i>
								  <p>Magazzini</p>
								</a>
							  </li>	
							</ul>  
						</li>

						
						<li class="nav-item menu">
							<a href="#" class="nav-link">
							  <i class="nav-icon fas fa-address-book"></i>
							  <p>Gestione fornitori
								<i class="right fas fa-angle-left"></i>
							  </p>
							</a>
							<ul class="nav nav-treeview">
							  <li class="nav-item">
								<a href="{{route('elenco_fornitori')}}" class="nav-link">
								  <i class="far fa-circle nav-icon"></i>
								  <p>Elenco fornitori</p>
								</a>
							  </li>
							</ul>  

							
							<ul class="nav nav-treeview">
							  <li class="nav-item">
								<a href="{{route('elenco_ordini_fornitori')}}" class="nav-link">
								  <i class="far fa-circle nav-icon"></i>
								  <p>Evasione Ordini fornitori</p>
								</a>
							  </li>
							</ul> 		
						</li>
					</ul>
				 </li>	
 
				@endif 
				</ul>
			   </li>
			  @endif  

		  
			 
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
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>
