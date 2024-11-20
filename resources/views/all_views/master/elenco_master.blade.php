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

        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <button class="btn btn-outline-secondary" type="submit" name='btn_cerca'>Cerca CoA</button>
          </div>
          <input type="text" class="form-control" placeholder="Certificato da cercare" id='cerca_coa' name='cerca_coa'>
        </div>      

        <div class="row">
          <div class="col-md-12">
            <table id='tbl_articoli' class="display">
                    <thead>
                      <tr>
                      <th style='min-width:210px;max-width:140px'>Operazioni</th>
                      <th>MASTER</th>
                      <th>Revisione</th>
                      <th>Creato il</th>
                    </tr>
                    </thead>
                    <tbody>
                      @foreach($elenco_master as $master)
                          <tr id='tr{{$master->id}}'>

                            
                              <td style='min-width:210px'>
                                <div id='div_oper{{$master->id}}'>
                                  @if(1==2)
                                    <button type="button" class="btn btn-primary btn-sm btnall" onclick='edit_rev({{$master->id}})'>Storicizza</button>
                                  @endif
                                  
                                  @if($master->id_clone_from==null) 
                                    <button type="button" class="btn btn-secondary btn-sm btnall" id='btn_dup{{$master->id}}' onclick="duplica_master('{{$master->id_doc}}',{{$master->id}})">Duplica</button>
                                  @else
                                    <button type="button" class="btn btn-primary btn-sm btnall" id='btn_change{{$master->id}}'' onclick="change_master('{{$master->id_doc}}','{{$master->id_clone_from}}',{{$master->id}})">Change Master</button>
                                  @endif  
                                  @if($master->obsoleti=="2") 
                                    <button type="button" class="btn btn-warning btn-sm btnall" >Vedi Obsoleti</button>
                                  @endif  


                                  <button type="button" class="btn btn-danger btn-sm btnall" onclick='dele_master({{$master->id}})' >Elimina</button>
                                </div>
                              </td>
                            

                            <td>
                              <div id='name_m{{$master->id}}'>
                                <a target='blank' href='https://docs.google.com/document/d/{{$master->id_doc}}/edit?usp=embed_googleplus'>
                                  <span id='name_mod{{$master->id}}'>{{$master->real_name}}</span>
                                </a>
                              </div>
                            </td>
                            <td>
                                  <?php
                                      $dx="";
                                      if ($master->data_rev!=null)
                                          $dx=date('d-m-Y', strtotime($master->data_rev));
                                  ?>
                                  <div id='div_edit{{$master->id}}' class='div_edit'></div>
                                  
                                  <span id='info_master{{$master->id}}'
                                      data-name_master='{{$master->real_name}}'
                                      data-rev='{{$master->rev}}'
                                      data-data_rev='{{$master->data_rev}}' 
                                  ></span>
                                  
                                  <a href='#' onclick="load_rev('{{$master->id_doc}}')">
                                    Info
                                    @if(1==2)
                                      <span id='mod_rev{{$master->id}}'>
                                        @if ($master->rev!=null)
                                          {{$master->rev}} del {{$dx}}
                                        @endif
                                      </span>
                                    @endif
                                  </a>
                                
                              </td>
                              <td>{{$master->created_at}}</td>
                          </tr>  
                      @endforeach
                    </tbody>
                    <tfoot>
                      <tr>
                        <th style='min-width:210px'></th>
                        <th>MASTER</th>
                        <th>Revisione</th>
                        <th>Creato il</th>
                      </tr>
                    </tfoot>					
            </table>
			    	<input type='hidden' id='dele_contr' name='dele_contr'>
				    <input type='hidden' id='restore_contr' name='restore_contr'>
			
          </div>

        </div>
        <div id=''>
          <button type="button" class="btn btn-primary btn-sm" onclick='edit_rev(0)'>Nuovo Master</button>
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
	
	

	<script src="{{ URL::asset('/') }}dist/js/elenco_master.js?ver=1.052"></script>

@endsection