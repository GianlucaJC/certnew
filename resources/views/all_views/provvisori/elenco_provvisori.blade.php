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
              <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
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



        <div class="row">
          <div class="col-md-12">
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
                    <tr>
                        <td style='width:70px'>
                            @if ($provvisorio->stato==0)
                              <div class="alert alert-warning p-1" role="alert" align='center'>
                                  Incompleto
                              </div>
                            @endif  
                            @if ($provvisorio->stato==1)
                              <div class="alert alert-success p-1" role="alert" align='center'>
                                  Pronto
                              </div>
                            @endif  

                        </td>
                        <td>
                          @if ($provvisorio->stato==0)  
                            <a href="edit_provvisorio/{{$provvisorio->id}}/{{$provvisorio->id_doc}}">
                              <button type="button" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Compilazione</button>
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
	
	

	<script src="{{ URL::asset('/') }}dist/js/elenco_provvisori.js?ver=1.001"></script>

@endsection