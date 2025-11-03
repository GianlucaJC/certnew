@extends('all_views.viewmaster.index')

@section('title', 'Certificati')
@section('extra_style') 
<!-- x button export -->

<link href="https://cdn.datatables.net/buttons/1.7.0/css/buttons.dataTables.min.css" rel="stylesheet">
<!-- -->
@endsection



<style>
	tfoot input {
        width: 100%;
        padding: 3px;
        box-sizing: border-box;
    }
</style>
@section('content_main')
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">ELENCO LOTTI</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
			  <li class="breadcrumb-item">Lotti</li>
              <li class="breadcrumb-item active">Elenco Lotti</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->


    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">

		<form method='post' action="{{ route('elenco_lotti') }}" id='frm_lotti' name='frm_lotti' autocomplete="off">
			<input name="_token" type="hidden" value="{{ csrf_token() }}" id='token_csrf'>
      <meta name="csrf-token" content="{{{ csrf_token() }}}">

      <div class="form-group">
      <div class="row">
          <div class="col-md-2">
            <label for='data_lotti'>Carica lotti del</label>
            <input type='date' onkeydown="return false"  name='data_lotti' id='data_lotti' class="form-control" value="{{$data_lotti}}" onchange="$('#frm_lotti').submit();">
          </div>  
         
          <div class="col-md-6" id='div_crea_provv'>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" value="" id="new_provv_if_exist" name="new_provv_if_exist">
              <label class="form-check-label" for="new_provv_if_exist">
                Crea nuovo provvisorio se già esistente
              </label>
            </div>
            <button type="button" class="btn btn-primary" onclick="crea_provv()" id='btn_crea_provv'>Crea Certificati Provvisori da elementi selezionati</button>          
       </div>  
      <div id='div_progress' class='mt-2'></div>      

    </div>  
        

      </div>  
      
      <hr>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" value="" id="sele_all" checked>
        <label class="form-check-label" for="sele_all">
          Seleziona/Deseleziona tutti
        </label>
      </div>
        <div class="row">
          <div class="col-md-12">
		  
            <table id='tbl_articoli' class="display">
              <thead>
                <tr>
                <th style='max-width:60px'>Sele</th>
                <th style='max-width:30px'>MASTER</th>
                <th>LOTTO</th>
                <th>Data</th>
                <th>Codice</th>
                <th>Prodotto</th>
              </tr>
              </thead>
              <tbody>
                @foreach($elenco_lotti as $info_lotto)
                    <tr>
                        <td style='text-align:center;max-width:60px'>
                          <div class="form-check">
                            <input class="form-check-input sele_lotti" type="checkbox" data-lotto='{{$info_lotto->DBlotto}}' data-codice='{{$info_lotto->DBcodice}}'  checked>
                          </div>
                        </td>
                        <td style='text-align:center;max-width:30px'>
                          @if($info_lotto->check_master===0)
                            <i class="fas fa-times-circle fa-lg" style="color: #ff0000;"></i>
                          @endif  
                          @if($info_lotto->check_master==1)
                            <i class="fas fa-clipboard-check fa-lg" style="color: #008442;"></i>
                          @endif  
                        </td>
                        <td>{{$info_lotto->DBlotto}}</td>
                        <td>{{$info_lotto->DBdata}}</td>
                        <td>{{$info_lotto->DBcodice}}</td>
                        <td>{{$info_lotto->DBprodotto}}</td>
                    </tr>  
                @endforeach
              </tbody>
              <tfoot>
                <tr>
                  <th style='max-width:60px'>Sele</th>  
                  <th style='max-width:30px'>MASTER</th>
                  <th>LOTTO</th>
                  <th>Data</th>
                  <th>Codice</th>
                  <th>Prodotto</th>
                </tr>
              </tfoot>					
            </table>



            <input type='hidden' id='dele_contr' name='dele_contr'>
            <input type='hidden' id='restore_contr' name='restore_contr'>
                 

          </div>

        </div>
		</form>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  
  <!-- Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalLabel">Conferma Creazione Provvisori</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Sicuri di creare i certificati provvisori per gli elementi selezionati?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
        <button type="button" class="btn btn-primary" onclick="crea_provv_confirm()">Conferma</button>
      </div>
    </div>
  </div>
</div>
 @endsection
 
 @section('content_plugin')
	<!-- jQuery -->
	<script src="{{ URL::asset('/') }}plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="{{ URL::asset('/') }}plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- AdminLTE App -->
	<script src="{{ URL::asset('/') }}dist/js/adminlte.min.js"></script>


	
	<!-- inclusione standard
		per personalizzare le dipendenze DataTables in funzione delle opzioni da aggiungere: https://datatables.net/download/
	!-->
	
	<!-- dipendenze DataTables !-->
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/datatables.min.css"/>
		 
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
		<script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/datatables.min.js"></script>
	<!-- fine DataTables !-->
	
	

	<script src="{{ URL::asset('/') }}dist/js/elenco_lotti.js?ver=1.057"></script>

@endsection