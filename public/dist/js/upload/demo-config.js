csv_send=false

$(function(){
 //set_class_allegati(0)
 
});


function set_class_allegati(from,id_cand) {
  /*
   * For the sake keeping the code clean and the examples simple this file
   * contains only the plugin configuration & callbacks.
   * 
   * UI functions ui_* can be located in: demo-ui.js
   */
  	
 
  base_path = $("#url").val();
  tipo_doc="";scadenza=""
  extFilter= ["pdf","doc","docx","jpg","png"]
  if (from=="2") {
	  tipo_doc=$("#tipo_doc").val();
	  scadenza=$("#scadenza").val()
  }
  if (from=="sezionali") extFilter= ["jpg"]
  
  
  $('#drag-and-drop-zone').dmUploader({ //
    url: base_path+'/upload.php',
	extraData: {
      
	  "from":from,
	  "id_cand":id_cand
	},
	
	extFilter: extFilter,
	
    maxFileSize: 80000000, // 8 Megs 
    onDragEnter: function(){
      // Happens when dragging something over the DnD area
      this.addClass('active');
    },
    onDragLeave: function(){
      // Happens when dragging something OUT of the DnD area
      this.removeClass('active');
    },
    onInit: function(){
      // Plugin is ready to use
      ui_add_log('Plugin Avviato :)', 'info');
    },
    onComplete: function(){
      // All files in the queue are processed (success or error)
      ui_add_log('Tutti i trasferimenti in sospeso sono terminati');
    },
    onNewFile: function(id, file){
      // When a new file is added using the file selector or the DnD area
      ui_add_log('Nuovo file aggiunto #' + id);
      ui_multi_add_file(id, file);
    },
    onBeforeUpload: function(id){
	  $("#div_img").empty();
      // about tho start uploading a file
      ui_add_log('Inizio upload di #' + id);
      ui_multi_update_file_status(id, 'uploading', 'Uploading...');
      ui_multi_update_file_progress(id, 0, '', true);
    },
    onUploadCanceled: function(id) {
      // Happens when a file is directly canceled by the user.
      ui_multi_update_file_status(id, 'warning', 'Cancellato da utente');
      ui_multi_update_file_progress(id, 0, 'warning', false);
    },
    onUploadProgress: function(id, percent){
      // Updating file progress
      ui_multi_update_file_progress(id, percent);
    },
    onUploadSuccess: function(id, data){
      // A file was successfully uploaded
	  
	  fx=data.path
	  
	  dx=JSON.stringify(data)
	  console.log(dx)
	  if (from=="1") $("#fx_curr").val(data.filename)
	  
      ui_add_log('Server Response for file #' + id + ': ' + JSON.stringify(data));
      ui_add_log('Upload del file #' + id + ' COMPLETATO', 'success');
      ui_multi_update_file_status(id, 'success', 'Upload Completato');
      ui_multi_update_file_progress(id, 100, 'success', false);
	  
	  if (from=="2") {
		  doc_id=data.filename
		  ref_row=doc_id.split(".")[0]
		  //doc upload
		  $("#body_dialog").hide(150);
			$('html, body').animate({
				scrollTop: $("#div_doc").offset().top-150
			}, 1500);		  
			//refresh table	doc
			doc_descr=$("#tipo_doc option:selected").text();
			sotto_tipo_descr=$("#sotto_tipo_doc option:selected").text();
			if (sotto_tipo_descr=="Select...") sotto_tipo_descr="--";
			html="<tr style='background-color:yellow' id='doc"+ref_row+"'>";
				html+="<td>--</td>";
				html+="<td>"+doc_descr+"</td>";
				html+="<td style='max-width:150px'>"+sotto_tipo_descr+"</td>";
				html+="<td>"+scadenza+"</td>";
				
				
				html+="<td>";
					html+="<a href='"+base_path+"/allegati/doc/"+id_cand+"/"+doc_id+"' target='_blank' >";
						html+="<button type='button' class='btn btn-info btn-sm'><i class='far fa-file'></i></button>";
					html+="</a> ";
					
					html+="<a href='javascript:void(0)' onclick=\"remove_doc('"+doc_id+"',"+id_cand+")\">";
						html+="<button type='button' class='btn btn-danger btn-sm' alt='Remove'><i class='fas fa-trash'></i></button>";
					html+="</a>";
				html+="</td>";
			html+="</tr>";
			$('#tb_doc tr:first').after(html);			
			update_doc(doc_id,id_cand)
	  }
	if (from=="ditte") {
		refresh(from,id_cand,data.filename)
	}
	  

    },
    onUploadError: function(id, xhr, status, message){
      ui_multi_update_file_status(id, 'danger', message);
      ui_multi_update_file_progress(id, 0, 'danger', false);  
    },
    onFallbackMode: function(){
      // When the browser doesn't support this plugin :(
      ui_add_log('Il plug-in non può essere utilizzato qui', 'danger');
    },
    onFileSizeError: function(file){
      ui_add_log('Il File \'' + file.name + '\' Non può essere aggiunto: Limite dimensione superato', 'danger');
    }
  });	
}

function redirect(from,id_cand) {
	if (from=="ditte")  {
		$("#refr").val(id_cand)
		$("#frm_ditte1").submit()
	}	
}

function refresh(from,id_cand,filename) {
	if (from=="ditte") {
		base_path = $("#url").val();
		let CSRF_TOKEN = $("#token_csrf").val();		
		descr_file=$("#descr_file").val()
		html="";
		html+="<font color='red'>Attendere. Refresh in corso...</font><hr>"
		$("#div_allegati").html(html)
		
		fetch(base_path+'/update_doc_ditte', {
			method: 'post',
			//cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached		
			headers: {
			  "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
			},
			body: '_token='+ CSRF_TOKEN+'&filename='+filename+'&id_ditta='+id_cand+'&descr_file='+descr_file
		})
		.then(response => {
			if (response.ok) {
			   return response.json();
			}
		})
		.then(resp=>{			
			if (resp.status=="KO") {
				$("#div_allegati").html('')
				alert("Problemi occorsi durante il salvataggio.\n\nDettagli:\n"+resp.message);
				return false;
			}
			timeout = setTimeout(function() {redirect(from,id_cand)}, 1500);
		})
		.catch(status, err => {
			return console.log(status, err);
		})			
				
	}
}
function update_doc(filename,id_cand) {
	tipo_doc=$("#tipo_doc").val()
	sotto_tipo_doc=$("#sotto_tipo_doc").val()
	scadenza=$("#scadenza").val()

	base_path = $("#url").val();
	let CSRF_TOKEN = $("#token_csrf").val();
	
	html="<span role='status' aria-hidden='true' class='spinner-border spinner-border-sm'></span> Attendere...";


	//$("#doc"+ref_row).html(html);
	setTimeout(function(){
	
		fetch(base_path+'/update_doc', {
			method: 'post',
			//cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached		
			headers: {
			  "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
			},
			body: '_token='+ CSRF_TOKEN+'&filename='+filename+'&tipo_doc='+tipo_doc+'&sotto_tipo_doc='+sotto_tipo_doc+'&scadenza='+scadenza+'&id_cand='+id_cand
		})
		.then(response => {
			if (response.ok) {
			   return response.json();
			}
		})
		.then(resp=>{
			if (resp.status=="KO") {
				alert("Problemi occorsi durante il salvataggio.\n\nDettagli:\n"+resp.message);
				return false;
			}
			//$("#doc"+ref_row).remove()
		})
		.catch(status, err => {
			return console.log(status, err);
		})	
	
	
	},1000);
	
	
}

function remove_doc(doc_id,id_cand) {
	ref_row=doc_id.split(".")[0]
	base_path = $("#url").val();
	let CSRF_TOKEN = $("#token_csrf").val();
	if (!confirm("Sicuri di cancellare l'allegato?")) return false;
	html="<span role='status' aria-hidden='true' class='spinner-border spinner-border-sm'></span> Attendere...";

	$("#doc"+ref_row).html(html);
	setTimeout(function(){
	
		fetch(base_path+'/remove_doc', {
			method: 'post',
			//cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached		
			headers: {
			  "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
			},
			body: '_token='+ CSRF_TOKEN+'&doc_id='+doc_id+'&id_cand='+id_cand
		})
		.then(response => {
			if (response.ok) {
			   return response.json();
			}
		})
		.then(resp=>{
			if (resp.status=="KO") {
				alert("Problemi occorsi durante il salvataggio.\n\nDettagli:\n"+resp.message);
				return false;
			}
			$("#doc"+ref_row).remove()
		})
		.catch(status, err => {
			return console.log(status, err);
		})	
	
	
	},1000);	
}