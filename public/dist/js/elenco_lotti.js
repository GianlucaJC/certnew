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

    
   
	

	
} );


function make_call(indice) {
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

        html=`
        <div class="progress mt-2" role="progressbar" aria-label="Avanzamento creazione provvisori" aria-valuenow="`+perc+`" aria-valuemin="`+perc+`" aria-valuemax="100">
            <div class="progress-bar bg-warning" style="width: `+perc+`%">`+perc+`%</div>
        </div><hr>`
        $("#div_progress").html(html);
        console.log("indice",indice,"arr_info.length",arr_info.length)
        if (indice<make_call.arr_info.length) make_call(indice)
        else {
            html=`<div class="alert alert-success mt-2" role="alert">
                Scansione lotti eseguita!<hr>

                <button type="button" onclick="$('#frm_lotti').submit()" class="btn btn-primary">Esegui il refresh della tabella per vedere l'esito dell'associazione master</button>
            </div>`
            $("#div_progress").html(html)
        }

    })
    .catch(status, err => {
        return console.log(status, err);
    })    

}


function crea_provv() {
    if (!confirm("Sicuri di creare i certificati provvisori?")) return false;
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
            <div class="progress mt-2" role="progressbar" aria-label="Avanzamento creazione provvisori" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-warning" style="width: 0%">0%</div>
            </div><hr>`
    
        $("#div_progress").html(html)
        $("#div_progress").show();
        make_call(0)
    }
}