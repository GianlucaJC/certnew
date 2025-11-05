<?php
use Illuminate\Support\Facades\Storage;
?>

@extends('all_views.viewmaster.index')

@section('title', 'Certificati')
@section('extra_style') 
<!-- x button export -->

<link href="https://cdn.datatables.net/buttons/1.7.0/css/buttons.dataTables.min.css" rel="stylesheet">

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

<script src="{{ URL::asset('/') }}dist/js/edit_provvisorio.js?ver=<?php echo time(); ?>"></script>
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
            <h1 class="m-0">MODIFICA CoA PROVVISORIO</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
			  <li class="breadcrumb-item">Provvisorio</li>
              <li class="breadcrumb-item active">Modifica CoA Provvisorio</li>
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

          <div class="card card-primary card-outline">
            <div class="card-header">
              <h5 class="card-title">Lotto <b>{{$info_provv->lotto}}</b> | Master di riferimento <i>{{$info_provv->codice_associato_master}}</i></h5>
              <div class="card-tools">
                <?php
                  // Definisco il percorso del file di debug
                  $debug_file_path = "debug/debug_provvisorio_{$id_provv}.html";
                ?>
                <a href="{{ url($debug_file_path) }}" target="_blank" class="btn btn-tool" title="Visualizza HTML sorgente (per debug)">
                  <i class="fas fa-code"></i>
                </a>
              </div>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-primary" id="btn_load_clone" onclick="js_clone=1;load_clone_click('{{$id_provv}}')" disabled>
                 <i class="fas fa-spinner fa-spin"></i> Caricamento...
                </button>
                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#infoTagModal">
                  <i class="fas fa-info-circle"></i> Info TAG
                </button>
                
                <a href="{{ route('elenco_provvisori') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Torna all'elenco provvisori</a>
            </div>

          </div>

            <div class="row">
                <div class="col-md-12">
                  <!--
                    <button type="button" class="btn btn-primary" onclick="$('#ifr_doc').width(1200);$('#div_compila').hide(100)";>ZOOM</button>
                    <button type="button" class="btn btn-secondary" onclick="$('#ifr_doc').width(640);$('#div_compila').show(100)";>Normal</button>
                    <a target='blank' href='https://docs.google.com/document/d/{{$id_provv}}/preview?usp=embed_googleplus'>
                            <button type="button" class="btn btn-success">Apri in finestra separata</button>
                    </a>
                    <hr>
                  !-->  
                    <div id='div_frame'>
                      <?php
                        // Carico il contenuto del documento per l'anteprima iniziale
                        $controller = new \App\Http\Controllers\ControllerEditProvvisori();
                        $doc_info = $controller->open_doc($id_provv);
                        $html_content = $doc_info['str_all'];

                        // Correzione per il simbolo di copyright e altri caratteri speciali
                        $html_content = str_replace("Symbol", "Arial", $html_content);

                        // Regex unificata per trovare tutti i tipi di tag: [[tag]], [tag], &lt;tag&gt;, $tag$
                        $pattern = '/(?:\[\[([a-zA-Z0-9_]+)\]\]|\[([a-zA-Z0-9_]+)\]|&lt;([a-zA-Z0-9_]+)&gt;|\$([a-zA-Z0-9_]+)\$)/';

                        // Usiamo preg_replace_callback per avvolgere ogni tag trovato con uno span giallo.
                        $html_content = preg_replace_callback($pattern, function($matches) {
                            $tag_completo = $matches[0];
                            // Pulisco il tag dai delimitatori per verificare se è un tag firma
                            $tag_pulito = preg_replace('/(?:&lt;|\$|\[\[|\]\]|&gt;)/', '', $tag_completo);

                            // Se il tag è 'firma' o 'firma_d', non lo evidenzio.
                            if (in_array($tag_pulito, ['firma', 'firma_d'])) {
                                return $tag_completo; // Restituisco il tag originale
                            }
                            // Altrimenti, lo evidenzio in giallo.
                            return '<span style="background-color: yellow !important; display: inline-block;">' . $tag_completo . '</span>';
                        }, $html_content);

                        // Creazione del file di debug
                        $debug_dir = public_path('debug');
                        if (!is_dir($debug_dir)) {
                            mkdir($debug_dir, 0755, true);
                        }
                        $debug_file_full_path = public_path($debug_file_path);
                        file_put_contents($debug_file_full_path, $html_content);
                      ?>                      
                      <iframe id='ifr_doc' onload="check_load_js_for_clone()" srcdoc="{{ $html_content }}" style="width: 840px; height: 1500px; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0,0,0,0.1);" frameborder="0"></iframe>
                    </div>
                </div>
             </div>

		</form>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>

  <!-- Modal per INFO TAG -->
  <div class="modal fade" id="infoTagModal" tabindex="-1" aria-labelledby="infoTagModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="infoTagModalLabel">Elenco dei TAG disponibili</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Di seguito sono elencati i TAG speciali che possono essere inseriti nei documenti Master. Durante la compilazione, il sistema li sostituirà con i valori corretti o con appositi campi di input.</p>
          
          <div class="row">
            <!-- Tag Compilazione Automatica -->
            <div class="col-md-6">
              <div class="card card-info card-outline">
                <div class="card-header"><h5 class="card-title">Tag a compilazione automatica</h5></div>
                <div class="card-body">
                  <dl class="row">
                    <dt class="col-sm-3"><code>[[lt]]</code>, <code>&lt;lt&gt;</code>, etc.</dt>
                    <dd class="col-sm-9">Sostituito automaticamente con il <strong>numero di lotto</strong> del certificato.</dd>

                    <dt class="col-sm-3"><code>[[pdate]]</code>, <code>&lt;pdate&gt;</code>, etc.</dt>
                    <dd class="col-sm-9">Sostituito automaticamente con la <strong>data di produzione</strong> del lotto.</dd>

                    <dt class="col-sm-3"><code>[[exp]]</code>, <code>&lt;exp&gt;</code>, etc.</dt>
                    <dd class="col-sm-9">Sostituito automaticamente con la <strong>data di scadenza</strong> del lotto.</dd>

                    <dt class="col-sm-3"><code>[[firma]]</code>, <code>$firma$</code></dt>
                    <dd class="col-sm-9">Sostituito con l'<strong>immagine della firma</strong> dell'utente che finalizza il certificato.</dd>

                    <dt class="col-sm-3"><code>[[firma_d]]</code>, <code>$firma_d$</code></dt>
                    <dd class="col-sm-9">Sostituito con la <strong>didascalia della firma</strong> (es. Nome e Cognome dell'utente).</dd>
                  </dl>
                </div>
              </div>
            </div>

            <!-- Tag Compilazione Manuale -->
            <div class="col-md-6">
              <div class="card card-primary card-outline">
                <div class="card-header"><h5 class="card-title">Tag a compilazione manuale</h5></div>
                <div class="card-body">
                  <dl class="row">
                    <dt class="col-sm-3"><code>[[fcont]]</code>, etc.</dt>
                    <dd class="col-sm-9">Genera un campo per inserire la <strong>data di approvazione</strong> del certificato.</dd>

                    <dt class="col-sm-3"><code>[[id]]</code>, <code>[[nid]]</code>, etc.</dt>
                    <dd class="col-sm-9">Genera un menu a tendina per la <strong>spunta di idoneità/non idoneità</strong> (es. ☑ o ☐).</dd>

                    <dt class="col-sm-3">Qualsiasi altro tag</dt>
                    <dd class="col-sm-9">
                        <p>Qualsiasi altro testo racchiuso tra <code>[[...]]</code>, <code>[...]</code>, <code>&lt;...&gt;</code>, o <code>$...$</code> (es. <code>[[aspetto]]</code>, <code>&lt;colore&gt;</code>) verrà trasformato in un <strong>campo di testo semplice</strong>.</p>
                        <p class="mb-0">Questo ti permette di creare nuovi campi personalizzati direttamente nel Master senza modificare il codice.</p>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="alert alert-warning">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Consiglio per la creazione dei TAG</h5>
            <p>
              Per garantire il massimo della compatibilità, <strong>usa sempre il formato con le doppie parentesi quadre (es. <code>[[nome_tag]]</code>)</strong>. È il più affidabile e previene errori di formattazione che possono verificarsi con Google Docs.
            </p>
            <p class="mb-0">
              Dato che Google Docs può aggiungere formattazioni "nascoste" che compromettono il riconoscimento dei tag, si consiglia di <strong>scrivere il tag in un editor di testo semplice</strong> (come Blocco Note/Notepad) e poi <strong>copiarlo e incollarlo</strong> nel documento Master. Questo garantisce che il tag sia "pulito" e venga riconosciuto correttamente dal sistema.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /.content-wrapper -->
  

 @endsection
 
 @section('content_plugin')

@endsection