$(document).ready( function () {
    $('#tbl_articoli tfoot th').each(function () {
        var title = $(this).text();
		if (title.length!=0)
			$(this).html('<input type="text" placeholder="Search ' + title + '" />');
    });	
	
	
    var table=$('#tbl_articoli').DataTable({
		"fnDrawCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
			ind_sum=0

			this.api()
			.columns({ search: 'applied' })
            .every(function () {
				
				let ourSum = 0
				if ($(this.header()).hasClass('sum')) {
					ind_sum++
					ourSum = this.data().reduce(function(a, b) {
						var x = parseFloat(a) || 0;
						var y = parseFloat(b) || 0;
						return x + y;
					}, 0);

					$('#sum_res'+ind_sum).html("<b>"+ourSum+"</b>");
				}
			})
		},			
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
            lengthMenu: 'Visualizza _MENU_ records per pagina',
            zeroRecords: 'Nessun provvisorio trovato',
            info: 'Pagina _PAGE_ di _PAGES_',
            infoEmpty: 'Non sono presenti provvisori',
            infoFiltered: '(Filtrati da _MAX_ provvisori totali)',
        },

		
    });	
	
	/*
	table.columns().every(function(index, tableCounter, counter) {
		let ourSum = 0
		let column = this;
		
		if ($(column.header()).hasClass('sum')) {
		  ourSum = column.data().reduce(function(a, b) {
			var x = parseFloat(a) || 0;
			var y = parseFloat(b) || 0;
			return x + y;
		  }, 0);
		}
		else {
		  ourSum = ""
		}
		console.log("column " + index + ": ourSum " + ourSum);	
	})
	*/
    
   
	

	
} );


function seleall(value) {
	var check = document.getElementById("sele_all").checked;
	if (check==true) $(".sele_ready").prop("checked",true)
	else $(".sele_ready").prop("checked",false)
}


function make_call(indice) {
    const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
    const csrf = metaElements.length > 0 ? metaElements[0].content : "";
    id_provv=make_call.arr_info[indice]['id_provv']
	from=make_call.arr_info[indice]['from']
    id_doc=make_call.arr_info[indice]['id_doc']
	codice_master=make_call.arr_info[indice]['codice_master']

    url=$("#url").val()

	fetch(url+"/to_def", {
        method: 'post',
        headers: {
          "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
          "X-CSRF-Token": csrf
        },
        body: "id_provv="+id_provv+"&from="+from+"&id_doc="+id_doc+"&codice_master="+codice_master,
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
		$("#tr"+id_provv).remove();
        if (indice<make_call.arr_info.length) make_call(indice)
        else {
			if (from=="2") $("#btn_def_id").text('Trasforma i selezionati in definitivi idonei');
			if (from=="3") $("#btn_def_nid").text('Trasforma i selezionati in definitivi non idonei');
			$(".btn_def").attr('disabled', false);
			txt=""
			if (from=="2") txt="Idonei"
			if (from=="3") txt="NON Idonei"
            html=`<div class="alert alert-success mt-2" role="alert">
                Certificati CoA Definitivi `+txt+` creati con successo!<hr>
            </div>`
            $("#div_progress").html(html)
        }

    })
    .catch(status, err => {
        return console.log(status, err);
    })    

}

function to_def_all(from) {
	txt=""
	if (from=="2") 
		txt="Idonei"	
	if (from=="3")
		txt="Non Idonei"
	if (!confirm("Sicuri di trasformare tutti i documenti selezionati in Definitivi "+txt+"?")) return false;

    arr_info=new Array()
    indice=0;
	$('.sele_ready').each(function () {
        if ($(this).is(":checked")) {
            arr_info[indice]=new Array()

            id_provv=$(this).attr('data-id_provv');
            id_doc=$(this).attr('data-id_doc');
            codice_master=$(this).attr('data-codice_master');
			
			arr_info[indice]['from']=from
            arr_info[indice]['id_provv']=id_provv
            arr_info[indice]['id_doc']=id_doc
			arr_info[indice]['codice_master']=codice_master
			

            indice++
        }
	})	  
    make_call.arr_info=arr_info
    if (indice>0) {
		if (from==2) $("#btn_def_id").text('Attendere...');
		if (from==3) $("#btn_def_nid").text('Attendere...');
		$(".btn_def").attr('disabled', true);		
        html=`
            <div class="progress mt-2" role="progressbar" aria-label="Avanzamento creazione definitivi" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-warning" style="width: 0%">0%</div>
            </div><hr>`
    
        $("#div_progress").html(html)
        $("#div_progress").show();
        make_call(0)
    }

}

function to_def(id_provv,from,id_doc,codice_master) {
	txt=""
	if (from=="2") 
		txt="Idoneo"	
	if (from=="3")
		txt="Non Idoneo"
	if (!confirm("Sicuri di trasformare in Definitivo "+txt+"?")) return false;
	$(".btn_def"+id_provv).attr('disabled', true);
	if (from=="2")
		$("#btn_def_id"+id_provv).text('Attendere...');
	if (from=="3")
		$("#btn_def_nid"+id_provv).text('Attendere...');
	


	url=$("#url").val()
    const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
    const csrf = metaElements.length > 0 ? metaElements[0].content : "";

    fetch(url+"/to_def", {
        method: 'post',
        headers: {
          "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
          "X-CSRF-Token": csrf
        },
        body: "id_provv="+id_provv+"&from="+from+"&id_doc="+id_doc+"&codice_master="+codice_master,
    })
    .then(response => {
        if (response.ok) {
           return response.json();
        }
    })
    .then(resp=>{
		if (resp.header=="OK") {
			$("#tr"+id_provv).remove();
			alert("OK")
		}	
		else
			alert("Problema riscontrato durante l'operazione")
    })
    .catch(status, err => {
        return console.log(status, err);
    }) 	
}



function dele_element(value) {
	if(!confirm('Sicuri di eliminare l\'elemento?')) 
		event.preventDefault() 
	else 
		$('#dele_contr').val(value)	
}

function restore_element(value) {
	if(!confirm('Sicuri di ripristinare l\'elemento?')) 
		event.preventDefault() 
	else 
		$('#restore_contr').val(value)	
}