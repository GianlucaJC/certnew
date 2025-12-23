<?php
use Illuminate\Support\Facades\Storage;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    @php($title = "Elenco Master")
    @include('layouts.bootstrap_partials.head', ['title' => $title])
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><path d='M6 2h15l5 5v21H6V2zm14 1v4h4' fill='%23fff' stroke='%23000' stroke-width='2' stroke-linejoin='round'/><path d='m12 18 4 4 8-8' fill='none' stroke='%2304a24c' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/></svg>" type="image/svg+xml">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/datatables.min.css"/>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        tfoot input {
            width: 100%;
            padding: 3px;
            box-sizing: border-box;
        }
        .bg-danger-light {
            background-color: #f5c6cb !important;
            color: #721c24;
        }
        /* Stili per tabella più compatta */
        #tbl_articoli {
            font-size: 0.85rem;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100" style="padding-bottom: 60px;"> <!-- Aggiunto padding per non sovrapporre il footer -->

    @include('layouts.bootstrap_partials.navbar')

@section('sidebar_extra_content')
    <div class="card shadow-sm mt-3">
        <div class="card-header">
            <h5 class="card-title m-0">Operazioni Master</h5>
        </div>
        <div class="card-body">
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary btn-sm text-start" onclick='edit_rev(0)'><i class="fas fa-plus-circle fa-fw me-2"></i>Nuovo Master</button>
                <button type="button" class="btn btn-info btn-sm text-start" id="btn_verifica_tag_master" onclick="showTagSelectionModal()"><i class="fas fa-tags fa-fw me-2"></i>Verifica TAG</button>
                <hr class="my-2">
                <button type="button" class="btn btn-secondary btn-sm text-start" id="filtra_mai_scansionati"><i class="fas fa-eye-slash fa-fw me-2"></i>Solo mai scansionati</button>
                <button type="button" class="btn btn-warning btn-sm text-start" id="filtra_tag_mancanti"><i class="fas fa-exclamation-triangle fa-fw me-2"></i>Filtra tag mancanti</button>
                <button type="button" class="btn btn-outline-success btn-sm text-start" id="filtra_sistemati"><i class="fas fa-check fa-fw me-2"></i>Solo Sistemati</button>
                <button type="button" class="btn btn-outline-danger btn-sm text-start" id="filtra_non_sistemati"><i class="fas fa-times fa-fw me-2"></i>Solo Non Sistemati</button>
                <button type="button" class="btn btn-light btn-sm text-start border" id="reset_filtri"><i class="fas fa-undo fa-fw me-2"></i>Reset Filtri</button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header">
            <h5 class="card-title m-0">Filtra per Archivio</h5>
        </div>
        <div class="card-body">
            <div class="btn-group w-100">
                <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-archive"></i> <span id="current-archivio-filter">Attivi</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" id="archivio-filter-menu">
                    <li><a class="dropdown-item archivio-filter" href="#" data-archivio="attivo">Attivi</a></li>
                    <li><a class="dropdown-item archivio-filter" href="#" data-archivio="obsoleto">Obsoleti</a></li>
                    <li><a class="dropdown-item archivio-filter" href="#" data-archivio="confermato">Confermati</a></li>
                    <li><a class="dropdown-item archivio-filter" href="#" data-archivio="eliminati">Eliminati</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item archivio-filter" href="#" data-archivio="all">Tutti</a></li>
                </ul>
            </div>
        </div>
    </div>
@endsection

    <main class="container-fluid mt-3 flex-grow-1">
        <div class="row">
            <div class="col-md-2">
                @include('all_views.master.sidebar')
            </div>

            <div class="col-md-10">


                <form method='post' action="{{ route('elenco_master') }}" id='frm_articolo' name='frm_articolo' autocomplete="off">
                    <input name="_token" type="hidden" value="{{ csrf_token() }}" id='token_csrf'>
                    <meta name="csrf-token" content="{{{ csrf_token() }}}">
                    <meta name="base-url" content="{{ url('/') }}">

                    <table id='tbl_articoli' class="display table table-sm table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th style='min-width:210px'>Operazioni</th>
                                <th>Nome Master</th>
                                <th>Rev</th>
                                <th>Data Creazione</th>
                                <th style='max-width:200px'>Tag Rilevati</th>
                                <th>Archivio</th>
                                <th>Stato</th>
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
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                    <input type='hidden' id='dele_contr' name='dele_contr'>
                    <input type='hidden' id='restore_contr' name='restore_contr'>
                </form>
            </div>
        </div>

<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="modal_story" tabindex="-1" role="dialog" aria-labelledby="info" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="title_modal">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id='body_modal'>
        ...
      </div>
      <div class="modal-footer">
		    <div id='altri_btn'></div>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
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
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
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
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Vuoi verificare i tag per tutti i master filtrati o solo per quelli visibili in questa pagina?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
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
 
    <div class="mt-auto fixed-bottom">
        @include('layouts.bootstrap_partials.footer')
    </div>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/datatables.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ URL::asset('/') }}dist/js/elenco_master.js?ver=<?php echo time();?>"></script>

</body>
</html>