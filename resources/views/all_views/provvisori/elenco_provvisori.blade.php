<!DOCTYPE html>
<html lang="it">
<head>
    @php($title = "Elenco Provvisori")
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
            <h5 class="m-0">Azioni di gruppo</h5>
        </div>
        <div class="card-body">
            <div id='div_progress' class='mb-3'></div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" checked id='sele_all' onchange='seleall()'>
                <label for='sele_all' class="small">Seleziona/Deseleziona tutti i pronti</label>
            </div>
            <div class="mt-2 d-grid gap-2">
                <button type="button" onclick="to_def_all(2)" class="btn btn-success btn-sm btn_def" id='btn_def_id' >
                    <i class="fas fa-check-circle"></i> Idonei
                </button>
                <button type="button" onclick="to_def_all(3)" class="btn btn-danger btn-sm btn_def"  id='btn_def_nid'>
                    <i class="fas fa-times-circle"></i> Non Idonei
                </button>
            </div>
        </div>
    </div>
@endsection

    <main class="container-fluid my-5 flex-grow-1">
        <div class="row">
            {{-- Inclusione della sidebar --}}
            @include('layouts.bootstrap_partials.sidebar')
            <div class="col-md-10">
                <form method='post' action="{{ route('elenco_provvisori') }}" id='frm_articolo' name='frm_articolo' autocomplete="off">
                    <input name="_token" type="hidden" value="{{ csrf_token() }}" id='token_csrf'>
                    <meta name="csrf-token" content="{{{ csrf_token() }}}">
                    <input type="hidden" value="{{url('/')}}" id="url" name="url">

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <table id='tbl_articoli' class="display table table-sm table-striped table-bordered" style="width:100%">
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
                                                <div class="form-check text-center">
                                                    <input class="form-check-input sele_ready" type="checkbox"
                                                           data-info_sele='{{$provvisorio->id}}'
                                                           data-id_provv='{{$provvisorio->id}}'
                                                           data-id_doc='{{$provvisorio->id_doc}}'
                                                           data-codice_master='{{$provvisorio->codice_associato_master}}'
                                                           checked id='sele_{{$provvisorio->id}}'
                                                    />
                                                    <label for='sele_{{$provvisorio->id}}'>Pronto</label>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($provvisorio->stato==0)
                                                <a href="edit_provvisorio/{{$provvisorio->id}}/{{$provvisorio->id_doc}}">
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin\'></i> Attendere...';"><i class="fas fa-edit"></i> Compilazione</button>
                                                </a>
                                            @endif
                                            @if ($provvisorio->stato==1)
                                                <button type="button" onclick="to_def({{$provvisorio->id}},2,'{{$provvisorio->id_doc}}','{{$provvisorio->codice_associato_master}}')" class="btn btn-success btn-sm btn_def btn_def{{$provvisorio->id}}" id='btn_def_id{{$provvisorio->id}}' style='width:250px'>
                                                    <i class="fas fa-check-circle"></i> Trasforma in definitivo idoneo
                                                </button>
                                                <hr class="my-1">
                                                <button type="button" onclick="to_def({{$provvisorio->id}},3,'{{$provvisorio->id_doc}}','{{$provvisorio->codice_associato_master}}')" class="btn btn-danger btn-sm btn_def btn_def{$provvisorio->id}}" style='width:250px'  id='btn_def_nid{{$provvisorio->id}}'>
                                                    <i class="fas fa-times-circle"></i> Trasforma in definitivo NON idoneo
                                                </button>
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
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

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

    <div class="mt-auto">
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

    <script src="{{ URL::asset('/') }}dist/js/elenco_provvisori.js?ver=1.028"></script>

</body>
</html>