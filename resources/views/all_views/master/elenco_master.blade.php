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
    .bg-danger-light {
        background-color: #f5c6cb !important; /* Un rosso più chiaro/sbiadito */
        color: #721c24; /* Testo scuro per leggibilità */
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
            <h1 class="m-0">ELENCO MASTER</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('elenco_lotti') }}">Home</a></li>
			  <li class="breadcrumb-item">Master</li>
              <li class="breadcrumb-item active">Elenco Master</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->


    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">

		<form method='post' action="{{ route('elenco_master') }}" id='frm_articolo' name='frm_articolo' autocomplete="off">
      <input name="_token" type="hidden" value="{{ csrf_token() }}" id='token_csrf'>
      <meta name="csrf-token" content="{{{ csrf_token() }}}">
      <meta name="base-url" content="{{ url('/') }}">
        <div class="row">
          <div class="col-md-12">
            <div class="card card-primary card-outline mb-3">
              <div class="card-header">
                <h5 class="card-title">Operazioni Master</h5>
              </div>
              <div class="card-body d-flex flex-wrap align-items-center">
                <button type="button" class="btn btn-primary btn-sm m-1" onclick='edit_rev(0)'><i class="fas fa-plus-circle"></i> Nuovo Master</button>
                <button type="button" class="btn btn-info btn-sm m-1" id="btn_verifica_tag_master" onclick="showTagSelectionModal()"><i class="fas fa-tags"></i> Verifica TAG</button>
                <button type="button" class="btn btn-secondary btn-sm m-1" id="filtra_mai_scansionati"><i class="fas fa-eye-slash"></i> Solo mai scansionati</button>
                <button type="button" class="btn btn-warning btn-sm m-1" id="filtra_tag_mancanti"><i class="fas fa-exclamation-triangle"></i> Filtra tag essenziali non rilevati</button>
                <button type="button" class="btn btn-default btn-sm m-1" id="reset_filtri"><i class="fas fa-undo"></i> Reset Filtri</button>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <table id='tbl_articoli' class="display">
                    <thead>
                      <tr>
                        <th style='min-width:210px'>Operazioni</th>
                        <th>Nome Master</th>
                        <th>Rev</th>
                        <th>Data Creazione</th>
                        <th style='max-width:200px'>Tag Rilevati</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- I dati verranno caricati da DataTables via AJAX -->
                    </tbody>
                    <tfoot>
                      <tr>
                        <th></th>
                        <th>Nome Master</th>
                        <th>Rev</th>
                        <th>Data Creazione</th>
                        <th>Tag Rilevati</th>
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

<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="modal_story" tabindex="-1" role="dialog" aria-labelledby="info" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="title_modal">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id='body_modal'>
        ...
      </div>
      <div class="modal-footer">
		    <div id='altri_btn'></div>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modale per la selezione dei TAG da verificare -->
<div class="modal fade" id="tagSelectionModal" tabindex="-1" role="dialog" aria-labelledby="tagSelectionModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tagSelectionModalLabel">Seleziona i TAG da Verificare</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Seleziona i tag standard da cercare o inseriscine di nuovi.</p>
        <div class="form-group">
            <h6>Tag Standard</h6>
            <div class="row">
                <div class="col-sm-4"><div class="form-check"><input class="form-check-input" type="checkbox" value="lt" id="tag_lt_check" checked><label class="form-check-label" for="tag_lt_check">lt (essenziale)</label></div></div>
                <div class="col-sm-4"><div class="form-check"><input class="form-check-input" type="checkbox" value="exp" id="tag_exp_check" checked><label class="form-check-label" for="tag_exp_check">exp</label></div></div>
                <div class="col-sm-4"><div class="form-check"><input class="form-check-input" type="checkbox" value="pdate" id="tag_pdate_check" checked><label class="form-check-label" for="tag_pdate_check">pdate</label></div></div>
                <div class="col-sm-4"><div class="form-check"><input class="form-check-input" type="checkbox" value="fcont" id="tag_fcont_check" checked><label class="form-check-label" for="tag_fcont_check">fcont</label></div></div>
                <div class="col-sm-4"><div class="form-check"><input class="form-check-input" type="checkbox" value="id" id="tag_id_check" checked><label class="form-check-label" for="tag_id_check">id</label></div></div>
                <div class="col-sm-4"><div class="form-check"><input class="form-check-input" type="checkbox" value="nid" id="tag_nid_check" checked><label class="form-check-label" for="tag_nid_check">nid</label></div></div>
            </div>
        </div>
        <hr>
        <div class="form-group">
            <label for="custom_tags"><h6>Tag Personalizzati</h6></label>
            <input type="text" class="form-control" id="custom_tags" placeholder="Es: aspetto, colore, ph (separati da virgola)">
            <small class="form-text text-muted">Inserisci i nomi dei tag senza parentesi, separati da una virgola.</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
        <button type="button" class="btn btn-primary" onclick="startVerificationProcess()">Avvia Verifica</button>
      </div>
    </div>
  </div>
</div>

<!-- Modale per la scelta della verifica -->
<div class="modal fade" id="verificationChoiceModal" tabindex="-1" role="dialog" aria-labelledby="verificationChoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="verificationChoiceModalLabel">Scegli l'ambito della verifica</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Vuoi verificare i tag per tutti i master filtrati o solo per quelli visibili in questa pagina?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
        <button type="button" class="btn btn-primary" onclick="verificaTagMaster('page')">Solo pagina corrente</button>
        <button type="button" class="btn btn-primary" onclick="verificaTagMaster('all')">Tutti i risultati filtrati</button>
      </div>
    </div>
  </div>
</div>

<!-- Modale per la progress bar -->
<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" aria-labelledby="progressModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="progressModalLabel">Verifica TAG in corso...</h5>
      </div>
      <div class="modal-body">
        <p id="progress-status">Inizializzazione...</p>
        <div class="progress">
          <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="cancel-verification-btn">Annulla</button>
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
	
	
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="{{ URL::asset('/') }}dist/js/elenco_master.js?ver=1.107"></script>

@endsection