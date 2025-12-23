<!DOCTYPE html>
<html lang="it">
<head>
    @php($title = "Elenco Lotti")
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
        #tbl_articoli {
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    @include('layouts.bootstrap_partials.navbar')

    @section('sidebar_extra_content')
        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <h5 class="m-0">Gestione Lotti</h5>
            </div>
            <div class="card-body">
                <div id='div_crea_provv'>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="" id="new_provv_if_exist" name="new_provv_if_exist">
                        <label class="form-check-label small" for="new_provv_if_exist">
                            Crea nuovo se già esistente
                        </label>
                    </div>
                    <div class="d-grid">
                        <button type="button" class="btn btn-primary" onclick="crea_provv()" id='btn_crea_provv'><i class="fas fa-plus-circle"></i> Crea Provvisori</button>
                    </div>
                </div>
                <div id='div_progress' class='mt-3'></div>
            </div>
        </div>
    @endsection

    <main class="container-fluid my-5 flex-grow-1">
        <div class="row">
            <div class="col-md-2">
                @include('all_views.master.sidebar')
            </div>

            <div class="col-md-10">
                <form method='post' action="{{ route('elenco_lotti') }}" id='frm_lotti' name='frm_lotti' autocomplete="off">
                    <input name="_token" type="hidden" value="{{ csrf_token() }}" id='token_csrf'>
                    <meta name="csrf-token" content="{{{ csrf_token() }}}">

                    <div class="mb-3" style="max-width: 250px;">
                        <label for='data_lotti' class="form-label">Carica lotti del</label>
                        <input type='date' onkeydown="return false" name='data_lotti' id='data_lotti' class="form-control" value="{{$data_lotti}}" onchange="$('#frm_lotti').submit();">
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header"><h1 class="m-0 h4">Elenco Lotti</h1></div>
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="" id="sele_all" checked>
                                <label class="form-check-label" for="sele_all">
                                    Seleziona/Deseleziona tutti
                                </label>
                            </div>
                            <table id='tbl_articoli' class="display table table-sm table-striped table-bordered" style="width:100%">
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
                                                    <input class="form-check-input sele_lotti" type="checkbox" data-lotto='{{$info_lotto->DBlotto}}' data-codice='{{$info_lotto->DBcodice}}' checked>
                                                </div>
                                            </td>
                                            <td style='text-align:center;max-width:30px'>
                                                @if($info_lotto->check_master===0)
                                                    <i class="fas fa-times-circle fa-lg" style="color: #ff0000;" title="Master non trovato"></i>
                                                @endif
                                                @if($info_lotto->check_master==1)
                                                    <i class="fas fa-clipboard-check fa-lg" style="color: #008442;" title="Master associato"></i>
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
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
  
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
    
    <script src="{{ URL::asset('/') }}dist/js/elenco_lotti.js?ver=<?php echo time();?>"></script>

</body>
</html>