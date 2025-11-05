$(document).ready( function () {
    $('#tbl_articoli tfoot th').each(function () {
        var title = $(this).text();
		if (title.length!=0)
			$(this).html('<input type="text" placeholder="Search ' + title + '" />');
    });	
	
	
    var table=$('#tbl_articoli').DataTable({
		dom: 'lBfrtip',
		buttons: [
			'excel'
		],		
        pagingType: 'full_numbers',
		pageLength: 500,
		lengthMenu: [8, 10, 15, 20, 50, 100, 200, 500],
        initComplete: function () {
            // Apply the search
            this.api()
                .columns()
                .every(function () {
                    var that = this;
 
                    $('input', this.footer()).on('keyup change clear', function () {
                        if (that.search() !== this.value) {
                            that.search(this.value).draw();
                        }
                    });
                });
        },
        language: {
            lengthMenu: "<div class='mr-3'>Visualizza _MENU_ lotti per pagina<br><small>Attenzione! Le spunte saranno relative solo alla pagina in corso</small></div>",
            zeroRecords: 'Nessun lotto trovato',
            info: 'Pagina _PAGE_ di _PAGES_',
            infoEmpty: 'Non sono presenti lotti',
            infoFiltered: '(Filtrati da _MAX_ lotti totali)',
        },

		
    });	

    
   
	$('#sele_all').on('click', function(){
		// Get all rows with search applied
		var rows = table.rows({ 'search': 'applied' }).nodes();
		// Check/uncheck checkboxes for all rows in the table
		$('input[type="checkbox"]', rows).prop('checked', this.checked);
	 });

	// Handle click on checkbox to set state of "Select all" control
	$('#tbl_articoli tbody').on('change', 'input[type="checkbox"]', function(){
		// If checkbox is unchecked
		if(!this.checked){
			var el = $('#sele_all').get(0);
			// If "Select all" control is checked and has 'indeterminate' property
			if(el && el.checked && ('indeterminate' in el)){
				// Set visual state of "Select all" control 
				// as 'indeterminate'
				el.indeterminate = true;
			}
		}
	});
	

	
} );

var isCancelled = false;

function cancel_operation() {
    if (!confirm("Sei sicuro di voler annullare l'operazione?")) return;
    
    const cancelBtn = $("#btn_cancel_op");
    isCancelled = true;
    cancelBtn.html('<i class="fas fa-spinner fa-spin"></i> Annullamento in corso...');
    cancelBtn.attr('disabled', true);
}

// Funzione per ricaricare la tabella
function refreshTable() {
    $('#tbl_articoli').DataTable().ajax.reload(null, false); // false per mantenere la paginazione
    $("#div_progress").hide(); // Nasconde il box dei messaggi
}


// Funzione ricorsiva per la creazione dei provvisori
function make_call(indice) {
    // Se l'utente ha annullato, interrompi il ciclo
    if (isCancelled) {
        const html = `<div class="alert alert-warning mt-2" role="alert">
                        Operazione annullata dall'utente.
                        <hr>
                        <button class="btn btn-primary" onclick="refreshTable()">Aggiorna Tabella</button>
                      </div>`;
        $("#div_progress").html(html);
        $("#div_crea_provv").show(200); // Mostra di nuovo il pulsante per creare
        // Resetta il pulsante di annullamento per operazioni future
        $("#btn_cancel_op").html('Annulla Operazione').attr('disabled', false);
        return;
    }

    new_provv = $("#new_provv_if_exist").is(":checked")
    n_p=0;
    if (new_provv==true) n_p=1
    const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
    const csrf = metaElements.length > 0 ? metaElements[0].content : "";
    codice=make_call.arr_info[indice]['codice']
    lotto=make_call.arr_info[indice]['lotto']
    fetch("crea_provv", {
        method: 'post',
        headers: {
          "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
          "X-CSRF-Token": csrf
        },
        body: "codice="+codice+"&lotto="+lotto+"&n_p="+n_p,
    })
    .then(response => {
        if (response.ok) {
           return response.json();
        }
    })
    .then(resp=>{
        console.log(resp)
        indice++ 
        perc=parseInt((100/make_call.arr_info.length)*indice)
        
        // Aggiorna solo la barra di progresso, non tutto il div
        $("#progress-container .progress-bar").css("width", perc + "%").text(perc + "%");
        $("#progress-container .progress-bar").attr("aria-valuenow", perc);
        console.log("indice",indice,"arr_info.length",arr_info.length)
        
        // Prosegui con la prossima chiamata solo se non è stato annullato
        if (indice < make_call.arr_info.length) {
            // Aggiungo un piccolo timeout per rendere l'UI più reattiva all'annullamento
            setTimeout(() => make_call(indice), 100);
        } else {
            // Operazione completata
            const html = `<div class="alert alert-success mt-2" role="alert">
                            Scansione lotti eseguita!
                            <hr>
                            <p>Puoi aggiornare la tabella per vedere l'esito dell'associazione master.</p>
                            <button class="btn btn-success" onclick="refreshTable()">Aggiorna Tabella</button>
                          </div>`;
            // Mostra di nuovo il pulsante di creazione e il messaggio di successo
            $("#div_crea_provv").show(200);
            $("#div_progress").html(html);
            $("#div_progress").show();
        }

    })
    .catch(status, err => {
        return console.log(status, err);
    })    

}


function crea_provv() {
    $('#confirmModal').modal('show');
}

function crea_provv_confirm() {
    // Resetta lo stato di annullamento prima di iniziare
    isCancelled = false;
    // Resetta il pulsante di annullamento
    const cancelBtn = $("#btn_cancel_op");
    cancelBtn.html('Annulla Operazione').attr('disabled', false);

    $('#confirmModal').modal('hide');
    arr_info=new Array()
    indice=0;
	$('.sele_lotti').each(function () {
        if ($(this).is(":checked")) {
            arr_info[indice]=new Array()
            codice=$(this).attr('data-codice');
            lotto=$(this).attr('data-lotto');
            console.log("codice",codice,"lotto",lotto)
            arr_info[indice]['codice']=codice
            arr_info[indice]['lotto']=lotto
            indice++
        }
	})	  
    make_call.arr_info=arr_info
    if (indice>0) {
        $("#div_crea_provv").hide(100)
        html=`
            <div id="progress-container">
                <div class="progress mt-2" role="progressbar" aria-label="Avanzamento creazione provvisori" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar bg-warning" style="width: 0%">0%</div>
                </div>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-danger" id="btn_cancel_op" onclick="cancel_operation()">Annulla Operazione</button>
            </div>
            <hr>`;
    
        $("#div_progress").html(html)
        $("#div_progress").show();
        make_call(0)
    }
}