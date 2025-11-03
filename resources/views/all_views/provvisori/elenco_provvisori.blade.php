<?php
use Illuminate\Support\Facades\Storage;
?>

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
            <h1 class="m-0">ELENCO PROVVISORI</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('elenco_lotti') }}">Home</a></li>
			  <li class="breadcrumb-item">Provvisori</li>
              <li class="breadcrumb-item active">Elenco Provvisori</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->


    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">

		<form method='post' action="{{ route('elenco_provvisori') }}" id='frm_articolo' name='frm_articolo' autocomplete="off">
			<input name="_token" type="hidden" value="{{ csrf_token() }}" id='token_csrf'>
      <meta name="csrf-token" content="{{{ csrf_token() }}}">
      <input type="hidden" value="{{url('/')}}" id="url" name="url">
      <div id='div_progress' class='mb-2'></div>
      
        <div class="row">
          <div class="col-md-12">
            
            <div class="form-check">
              <input class="form-check-input" type="checkbox" checked id='sele_all' onchange='seleall()'> 
              <label for='sele_all'>Seleziona/Deseleziona tutti i provvisori pronti</label>
              <a href="#">
                <button type="button" onclick="to_def_all(2)" class="btn btn-success btn-sm btn_def" id='btn_def_id' >
                  <i class="fas fa-check-circle"></i> Trasforma i selezionati in definitivi idonei</button>
              </a>
              <a href="#">
                <button type="button" onclick="to_def_all(3)" class="btn btn-danger btn-sm btn_def"  id='btn_def_nid'>
                <i class="fas fa-times-circle"></i> Trasforma i selezionati in definitivi NON idonei</button>
              </a>              
            </div>  
            <hr>


            <table id='tbl_articoli' class="display">
                <thead>
                  <tr>
                    <th style='width:70px'>Stato</th>
                    <th>Operazioni</th>
                    <th>LOTTO</th>
                    <th>Codice</th>
                    <th>Documento MASTER</th>
                    <th>Creato il</th>
                    <th>Aggiornato il</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($elenco_provvisori as $provvisorio)
                    <tr id='tr{{$provvisorio->id}}'>
                        <td style='width:70px;text-align:center'>
                            @if ($provvisorio->stato == 0)
                                @if ($provvisorio->perc_complete == 100)
                                    <button class='btn btn-info btn-sm' type='button' onclick="save_to_ready_from_list('{{$provvisorio->id_doc}}', '{{$provvisorio->id}}')" id='btn_ready_list{{$provvisorio->id}}'><i class="fas fa-arrow-circle-right"></i> Passa a Pronto</button>
                                @else
                                    <div class="progress" role="progressbar" aria-label="Completamento" aria-valuenow="{{ $provvisorio->perc_complete }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar bg-warning" style="width: {{ $provvisorio->perc_complete }}%">{{ $provvisorio->perc_complete }}%</div>
                                    </div>
                                    <small>Incompleto</small>
                                @endif
                            @endif
                            @if ($provvisorio->stato==1)
                              <center>
                                  <div class="form-check">
                                    <input class="form-check-input sele_ready" type="checkbox" 
                                      data-info_sele='{{$provvisorio->id}}' 
                                      data-id_provv='{{$provvisorio->id}}'
                                      data-id_doc='{{$provvisorio->id_doc}}'
                                      data-codice_master='{{$provvisorio->codice_associato_master}}'
                                      checked id='sele_{{$provvisorio->id}}'
                                    />

                                    <label for='sele_{{$provvisorio->id}}'>Pronto</label>
                                  </div>                                
                              </center>

                            @endif  
                        </td>
                        <td>
                        
                          @if ($provvisorio->stato==0)  
                            <a href="edit_provvisorio/{{$provvisorio->id}}/{{$provvisorio->id_doc}}">
                              <button type="button" class="btn btn-primary btn-sm" onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Attendere...';"><i class="fas fa-edit"></i> Compilazione</button>
                            </a>
                          @endif
                          @if ($provvisorio->stato==1)
                          <a href="#">
                              <button type="button" onclick="to_def({{$provvisorio->id}},2,'{{$provvisorio->id_doc}}','{{$provvisorio->codice_associato_master}}')" class="btn btn-success btn-sm btn_def btn_def{{$provvisorio->id}}" id='btn_def_id{{$provvisorio->id}}' style='width:250px'>
                               <i class="fas fa-check-circle"></i> Trasforma in definitivo idoneo</button>
                            </a><hr>
                            <a href="#">
                              <button type="button" onclick="to_def({{$provvisorio->id}},3,'{{$provvisorio->id_doc}}','{{$provvisorio->codice_associato_master}}')" class="btn btn-danger btn-sm btn_def btn_def{$provvisorio->id}}" style='width:250px'  id='btn_def_nid{{$provvisorio->id}}'>
                              <i class="fas fa-times-circle"></i> Trasforma in definitivo NON idoneo</button>
                            </a>
                          @endif
                        </td>
                        <td>

                          <a target='blank' href='https://docs.google.com/document/d/{{$provvisorio->id_doc}}/preview?usp=embed_googleplus'>
                            {{$provvisorio->lotto}}
                          </a>		
                        </td>
                        <td>{{$provvisorio->codice}}</td>
                        <td>{{$provvisorio->codice_associato_master}}</td>
                        <td>{{$provvisorio->created_at}}</td>
                        <td>{{$provvisorio->updated_at}}</td>
                    </tr>  
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th style='width:70px'>Stato</th>        
                    <th>Operazioni</th>  
                    <th>LOTTO</th>
                    <th>Codice</th>
                    <th>Documento MASTER</th>
                    <th>Creato il</th>
                    <th>Aggiornato il</th>
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

  <!-- Modal di conferma -->
  <div class="modal fade" id="confirmReadyModal" tabindex="-1" aria-labelledby="confirmReadyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmReadyModalLabel">Conferma Cambio Stato</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          Sei sicuro di voler passare il documento allo stato "pronto per trasformazione definitivo"?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
          <button type="button" class="btn btn-primary" id="confirmReadyBtn">Conferma</button>
        </div>
      </div>
    </div>
  </div>
  <!-- /.content-wrapper -->
  
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
	
	

	<script src="{{ URL::asset('/') }}dist/js/elenco_provvisori.js?ver=1.027"></script>

@endsection