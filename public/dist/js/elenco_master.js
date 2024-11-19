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
		
		dom: 'Bfrtip',
		buttons: [
			'excel', 'pdf'
		],		
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
            zeroRecords: 'Nessun master trovato',
            info: 'Pagina _PAGE_ di _PAGES_',
            infoEmpty: 'Non sono presenti master',
            infoFiltered: '(Filtrati da _MAX_ master totali)',
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

function save_master(id_ref) {
	name_master_edit=$("#name_master_edit").val();
	rev_edit=$("#rev_edit").val();
	data_rev_edit=$("#data_rev_edit").val();
	if (name_master_edit.length==0) {
		alert("Controllare il nome assegnato!")
		return false
	}
	if (id_ref!=0) {
		$("#info_master"+id_ref).attr('data-name_master',name_master_edit);
		$("#info_master"+id_ref).attr('data-rev',rev_edit);
		$("#info_master"+id_ref).attr('data-data_rev',data_rev_edit);
	}
	btn_save=`<button type="button" class="btn btn-success" id="btn_save" disabled>Attendere...</button>`
	$("#altri_btn").html(btn_save)


    const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
    const csrf = metaElements.length > 0 ? metaElements[0].content : "";

    fetch("save_master", {
        method: 'post',
        headers: {
          "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
          "X-CSRF-Token": csrf
        },
        body: "id_ref="+id_ref+"&name_master_edit="+name_master_edit+"&rev_edit="+rev_edit+"&data_rev_edit="+data_rev_edit,
    })
    .then(response => {
        if (response.ok) {
           return response.json();
        }
    })
    .then(resp=>{
		btn_save=`<button type="button" class="btn btn-success" id="btn_save" onclick='save_master(`+id_ref+`)'>Salva</button>`
		$("#altri_btn").html(btn_save)


		if (resp.header=="OK") {
			$('#modal_story').modal('toggle')
			
			$("#name_mod"+id_ref).html(name_master_edit)	
			html=""
			if (rev_edit.length>0 && data_rev_edit.length>0) {
				datex = data_rev_edit.split('-');
				data_rev_edit = datex[2] + '-' + datex[1] + '-' + datex[0];
				html=rev_edit+" del "+data_rev_edit
			}

			if (id_ref!=0)	
				$("#mod_rev"+id_ref).html(html)
			else
				window.location.reload();
		}	
		else
			alert("Problema riscontrato durante la modifica")
    })
    .catch(status, err => {
        return console.log(status, err);
    }) 
}

function load_rev(id_ref) {
    const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
    const csrf = metaElements.length > 0 ? metaElements[0].content : "";

    fetch("load_rev", {
        method: 'post',
        headers: {
          "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
          "X-CSRF-Token": csrf
        },
        body: "id_ref="+id_ref,
    })
    .then(response => {
        if (response.ok) {
           return response.json();
        }
    })
    .then(resp=>{
		console.log("revisioni",resp)
    })
    .catch(status, err => {
        return console.log(status, err);
    }) 
}


function dele_master(id_ref) {
	if (!confirm("Sicuri di eliminare il Master?")) return false
		
    const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
    const csrf = metaElements.length > 0 ? metaElements[0].content : "";

    fetch("dele_master", {
        method: 'post',
        headers: {
          "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
          "X-CSRF-Token": csrf
        },
        body: "id_ref="+id_ref,
    })
    .then(response => {
        if (response.ok) {
           return response.json();
        }
    })
    .then(resp=>{
		if (resp.header=="OK") {
			html="<del>"+name_master+"</del>"
			$("#name_m"+id_ref).html(html)
			$("#div_oper"+id_ref).empty()
			alert("Master eliminato")
		}	
		else
			alert("Problema riscontrato durante la cancellazione")
    })
    .catch(status, err => {
        return console.log(status, err);
    })   
}

function duplica_master(id_doc,id_ref) {
	if (!confirm("Sicuri di duplicare il master?")) return false
	name_master=$("#info_master"+id_ref).attr('data-name_master');	
	name_clone=name_master+"-copia";

	const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
    const csrf = metaElements.length > 0 ? metaElements[0].content : "";

    fetch("duplica_master", {
        method: 'post',
        headers: {
          "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
          "X-CSRF-Token": csrf
        },
        body: "id_doc="+id_doc+"&name_clone="+name_clone,
    })
    .then(response => {
        if (response.ok) {
           return response.json();
        }
    })
    .then(resp=>{
		if (resp.header=="OK") {
			alert("Master duplicato. Per trovarlo cercalo di nuovo con il nome originale")
		}	
		else
			alert("Problema riscontrato durante la cancellazione")
    })
    .catch(status, err => {
        return console.log(status, err);
    })  	
}
function edit_rev(id_ref) {
    name_master=$("#info_master"+id_ref).attr('data-name_master');
	rev=$("#info_master"+id_ref).attr('data-rev');
    data_rev=$("#info_master"+id_ref).attr('data-data_rev');
	if (id_ref==0) {
		name_master=""
		rev=""
		data_rev=""
	}
	disp="display:none"
	if (id_ref==0) disp="";
	//N.B.:modifica nome master non abilitato: dovrei far riflettere anche su drive...eventualmente faccio io
	html=`
			<div class="input-group mb-3" style='`+disp+`'>
				<div class="input-group-prepend">
				<span class="input-group-text" >Nome File</span>
				</div>
				<input type="text" class="form-control" id="name_master_edit" placeholder="Filename" aria-label="Filename" aria-describedby="Filename" value='`+name_master+`'>
			</div>	
	        <div class="input-group mb-3">
                <span class="input-group-text">Revisione</span>
                <input type="text" class="form-control" placeholder="Rev" id="rev_edit" aria-label="Numero revisione" aria-describedby="Numero revisione" name='rev' value="`+rev+`">
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text">Data revisione</span>
                <input type="date" class="form-control" id="data_rev_edit" aria-label="Data revisione" aria-describedby="Data revisione" name='data_rev' value="`+data_rev+`">
            </div>
        `     
    //$(".div_edit").empty() 
    //$("#div_edit"+id_ref).html(html)

	btn_save=`<button type="button" class="btn btn-success" id="btn_save" onclick='save_master(`+id_ref+`)'>Salva</button>`
	if (id_ref==0)
		$("#title_modal").html("Definizione informazioni nuovo MASTER")
	else
		$("#title_modal").html("Modifica informazioni MASTER")
	$("#altri_btn").html(btn_save)
	$('#modal_story').modal('toggle')
	$("#body_modal").html(html)	
    
}


function dele_master(id_ref) {
	if (!confirm("Sicuri di eliminare il Master?")) return false
	name_master=$("#info_master"+id_ref).attr('data-name_master');
    const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
    const csrf = metaElements.length > 0 ? metaElements[0].content : "";

    fetch("dele_master", {
        method: 'post',
        headers: {
          "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
          "X-CSRF-Token": csrf
        },
        body: "id_ref="+id_ref,
    })
    .then(response => {
        if (response.ok) {
           return response.json();
        }
    })
    .then(resp=>{
		if (resp.header=="OK") {
			html="<del>"+name_master+"</del>"
			$("#name_m"+id_ref).html(html)
			$("#div_oper"+id_ref).empty()
			alert("Master eliminato")
		}	
		else
			alert("Problema riscontrato durante la cancellazione")
    })
    .catch(status, err => {
        return console.log(status, err);
    })    

}