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

            <div class="row">
                <div class="col-md-7">
                    <button type="button" class="btn btn-primary" onclick="$('#ifr_doc').width(1200);$('#div_compila').hide(100)";>ZOOM</button>
                    <button type="button" class="btn btn-secondary" onclick="$('#ifr_doc').width(640);$('#div_compila').show(100)";>Normal</button>
                    <a target='blank' href='https://docs.google.com/document/d/{{$id_provv}}/preview?usp=embed_googleplus'>
                            <button type="button" class="btn btn-success">Apri in finestra separata</button>
                    </a>
                    <hr>
                    <div id='div_frame'>
                      <iframe id='ifr_doc' onload='check_load_js_for_clone()' src="https://docs.google.com/document/d/{{$id_provv}}/preview?embedded=true" style="width:640px; height:1500px;" frameborder="0"></iframe>
                    </div>
                </div>
                
                <div class="col-md-5" id='div_compila'>
                  <div class="alert alert-info" role="alert">
                    <a href='#' onclick="js_clone=1;load_clone('{{$id_provv}}')">
                        Compilazione guidata dei tag rilevati nel provvisorio
                    </a>  
                   </div>
                    <?php
                    		$tag_O="&lt;";
                        $tag_C="&gt;";
                        $tags=$all_tag['tags'];
                        
                        for ($sca=0;$sca<count($tags);$sca++) {
                            $tag=trim($tags[$sca]);
                            $ref_tag=str_replace($tag_O,"",$tag);$ref_tag=str_replace($tag_C,"",$ref_tag);
                            $html="<div class='input-group mb-3'>";
                                  $html.="<span class='input-group-text'>";
                                    $html.="       <input class='form-check-input mt-0 enable_tag ml-1' type='checkbox' data-id='$sca'>
                                    ";
                                  $html.="</span>";
                                  $html.="<a href='#' onclick=\"view_tag('$id_provv','$ref_tag')\"";
                                    $html.="<span class='input-group-text'><font color='royalblue'>$tag</font></span>";      
                                  $html.="</a>";
                                  
                                  $html.=render_input($tag,$ref_tag,$sca);
                            $html.="</div>";

                            echo $html;  
                        }
                    ?>
                  <?php if (count($tags)>0) {?>
                      <button type="button" class="btn btn-primary" id='btn_save_provv' onclick="save_tag_edit('{{$id_provv}}')">Salva</button>
                  <?php }
                    else  {
                      echo "<button type='button' class='btn btn-success' id='btn_save_idoneo'>Trasforma in definitivo IDONEO";
                      echo "<button type='button' class='ml-2 btn btn-danger' id='btn_save_non_idoneo'>Trasforma in definitivo NON IDONEO";

                    }
                  ?>  

                    
                </div>
            </div>
		</form>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  
<?php
function render_input($tag,$ref_tag,$sca) {
    $place=$tag;
    if ($ref_tag=="lt") $place="Lotto";
    if ($ref_tag=="pdate") $place="Data produzione";
    if ($ref_tag=="exp") $place="Data scadenza";  
    if ($ref_tag=="fcont") $place="Data approvazione";  
    $html="";
    if ($ref_tag=="fcont----")
        $html="<div 'style=width:100px'><input style='width:210px' type='date' disabled data-id='tg$sca'  id='tg$sca' class='form-control dati_compilazione' data-tag='$ref_tag'></div>";
    elseif ($ref_tag=="id" || $ref_tag=="nid") {
       $lbl="Non idoneo";  
       if ($ref_tag=="id") $lbl="Idoneo";
        $html="<div class='form-check'>
                $lbl <select class='form-select dati_compilazione' disabled  id='tg$sca'  data-id='tg$sca' data-tag='$ref_tag'>
                  <option value='N'>NO Check ☐</option>
                  <option value='S'>Check ☑</option>
                </select>
          </div>";
    }
    else
        $html="<input type='text' class='form-control dati_compilazione' disabled   data-id='tg$sca' data-tag='$ref_tag'  id='tg$sca' placeholder='$place'>";
    return $html;  
  }

?>

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
	
	

	<script src="{{ URL::asset('/') }}dist/js/edit_provvisorio.js?ver=<?php echo time(); ?>"></script>

@endsection