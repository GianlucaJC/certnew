<?php
use Illuminate\Support\Facades\Storage;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    @php($title = "Elenco CoA Definitivi Non Idonei")
    @include('layouts.bootstrap_partials.head', ['title' => $title])
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><path d='M6 2h15l5 5v21H6V2zm14 1v4h4' fill='%23fff' stroke='%23000' stroke-width='2' stroke-linejoin='round'/><path d='m12 18 4 4 8-8' fill='none' stroke='%2304a24c' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/></svg>" type="image/svg+xml">    
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.12.1/b-2.2.3/b-colvis-2.2.3/b-html5-2.2.3/b-print-2.2.3/datatables.min.css"/>
    <style>
        tfoot input {
            width: 100%;
            padding: 3px;
            box-sizing: border-box;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    @include('layouts.bootstrap_partials.navbar')

    <main class="container-fluid mt-3 flex-grow-1">
        <div class="row">
            <div class="col-md-2">
                @include('all_views.master.sidebar')
            </div>

            <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('elenco_lotti') }}">Home</a></li>
                            <li class="breadcrumb-item">CoA</li>
                            <li class="breadcrumb-item active" aria-current="page">Elenco CoA non idonei</li>
                        </ol>
                    </nav>
                </div>

                <form method='post' action="{{ route('elenco_definitivi_idonei') }}" id='frm_articolo' name='frm_articolo' autocomplete="off">
                    <input name="_token" type="hidden" value="{{ csrf_token() }}" id='token_csrf'>

                    <table id='tbl_articoli' class="display table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>LOTTO</th>
                                <th>Codice</th>
                                <th>Documento MASTER</th>
                                <th>Creato il</th>
                                <th>Aggiornato il</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($elenco_definitivi_non_idonei as $definitivo)
                            <tr>
                                <td>
                                    <a target='_blank' href="doc/definitivi_non_idonei/{{$definitivo->codice_associato_master}}.pdf">
                                        <button type="button" class="btn btn-danger"><i class="fas fa-file-pdf"></i></button>
                                    </a>
                                </td>
                                <td>
                                    <a target='_blank' href='https://docs.google.com/document/d/{{$definitivo->id_doc}}/preview?usp=embed_googleplus'>
                                        {{$definitivo->lotto}}
                                    </a>
                                </td>
                                <td>{{$definitivo->codice}}</td>
                                <td>{{$definitivo->codice_associato_master}}</td>
                                <td>{{$definitivo->created_at}}</td>
                                <td>{{$definitivo->updated_at}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>File</th>
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
                </form>
            </div>
        </div>
    </main>

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

    <script src="{{ URL::asset('/') }}dist/js/elenco_definitivi_ni.js?ver=1.001"></script>

</body>
</html>