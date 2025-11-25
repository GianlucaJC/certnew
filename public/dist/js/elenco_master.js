let customFilter = '', custom_filter_sistemato = null, custom_filter_archivio = 'attivo';

$(document).ready( function () {
    $('#tbl_articoli tfoot th').each(function () {
        var title = $(this).text();
        // Escludi la prima colonna (azioni) dall'input di ricerca
        if ($(this).index() === 0) return;

        var placeholderText = (title.length !== 0) ? title : '';
		
        if (title.length !== 0 || $(this).index() === 4) // Aggiunge l'input anche alla colonna Tag
			$(this).html('<input type="text" placeholder="Cerca ' + placeholderText + '" style="width:100%" />');
    });

    var table = $('#tbl_articoli').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "elenco_master", // Questa dovrebbe puntare alla route che restituisce i dati JSON
            "type": "POST",
            "headers": {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            "data": function (d) {
                // Aggiunge il parametro del filtro custom alla richiesta inviata al server
                if (customFilter) {
                    d.custom_filter = customFilter;
                }
                d.custom_filter_sistemato = custom_filter_sistemato;
                d.custom_filter_archivio = custom_filter_archivio;
            },
            "error": function (jqXHR, textStatus, errorThrown) {
                // Intercetta l'errore AJAX di DataTables per evitare l'alert di default.
                // In questo modo, l'utente non vedrà l'avviso. L'errore viene comunque loggato in console per il debug.
                console.error("DataTables AJAX error: ", textStatus, errorThrown);
            }
        },
        "columns": [
            { "data": "operazioni", "name": null, "orderable": false, "searchable": false },
            { "data": "real_name", "name": "m.real_name" }, // Assumendo che il controller lo gestisca
            { "data": "rev", "name": "m.rev" }, // Assumendo che il controller lo gestisca
            { "data": "created_at", "name": "m.created_at", "searchable": false },
            { "data": "tags_found", "name": "m.tags_found", "orderable": false, "searchable": true },
            { "data": "archivio_descr", "name": null, "orderable": false, "searchable": false }, // Colonna virtuale
            { 
                "data": "sistemato",
                "name": "m.sistemato", 
                "orderable": true, 
                "searchable": false,
                "render": function(data, type, row) {
                    const baseUrl = $('meta[name="base-url"]').attr('content');
                    if (data == 1) {
                        return `<button class="btn btn-outline-secondary btn-sm" onclick="toggleSistemato('${row.id_doc}', this, '${baseUrl}')">Segna come non sistemato</button>`;
                    } else {
                        return `<button class="btn btn-outline-success btn-sm" onclick="toggleSistemato('${row.id_doc}', this, '${baseUrl}')">Segna come sistemato</button>`;
                    }
                }
            }
        ],
        "columnDefs": [
            {
                "targets": 0,
                "render": function (data, type, row, meta) {
                    let buttons = `<div id='div_oper${row.id}' class="btn-group" role="group" aria-label="Azioni Master">`;
                    
                    if (row.dele == 1) {
                        // Se il master è eliminato, mostra solo il pulsante di ripristino
                        buttons += `<button type="button" class="btn btn-success btn-sm" onclick='restore_master(${row.id})' title="Ripristina Master"><i class="fas fa-undo"></i></button>`;
                    } else {
                        // Pulsanti standard per master non eliminati
                        if (row.id_clone_from == null) {
                            buttons += `<button type="button" class="btn btn-secondary btn-sm btnall" id='btn_dup${row.id}' onclick="duplica_master('${row.id_doc}',${row.id})" title="Duplica Master"><i class="fas fa-clone"></i></button>`;
                        } else {
                            buttons += `<button type="button" class="btn btn-primary btn-sm btnall" id='btn_change${row.id}' onclick="change_master('${row.id_doc}','${row.id_clone_from}',${row.id})" title="Rendi questo clone il master ufficiale"><i class="fas fa-exchange-alt"></i></button>`;
                        }
                        buttons += `<button type="button" class="btn btn-danger btn-sm btnall" onclick='dele_master(${row.id})' title="Elimina Master"><i class="fas fa-trash-alt"></i></button>`;

                        // Menu a tendina "Sposta in" dinamico
                        let dropdownItems = '';
                        if (custom_filter_archivio !== 'attivo') {
                            dropdownItems += `<a class="dropdown-item move-btn" href="#" data-id="${row.id_doc}" data-archivio="attivo">Attivi</a>`;
                        }
                        if (custom_filter_archivio !== 'confermato') {
                            dropdownItems += `<a class="dropdown-item move-btn" href="#" data-id="${row.id_doc}" data-archivio="confermato">Confermati</a>`;
                        }
                        if (custom_filter_archivio !== 'obsoleto') {
                            dropdownItems += `<a class="dropdown-item move-btn" href="#" data-id="${row.id_doc}" data-archivio="obsoleto">Obsoleti</a>`;
                        }

                        buttons += `<div class="btn-group" role="group">
                                      <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Sposta in...">
                                        <i class="fas fa-folder-open"></i>
                                      </button>
                                      <div class="dropdown-menu">${dropdownItems}</div>
                                    </div>`;
                    }

                    buttons += `</div>`;
                    return buttons;
                }
            },
            {
                "targets": 1,
                "render": function (data, type, row, meta) {
                    let link = (row.dele == 1) ? 
                        `<span id='name_mod${row.id}'>${row.real_name}</span>` :
                        `<a target='blank' href='https://docs.google.com/document/d/${row.id_doc}/edit?usp=embed_googleplus'>
                           <span id='name_mod${row.id}'>${row.real_name}</span>
                         </a>`;
                    return `<div id='name_m${row.id}'>${link}</div>`;
                }
            },
            {
                "targets": 2,
                "render": function (data, type, row, meta) {
                    let dx = "";
                    if (row.data_rev != null) {
                        let d = new Date(row.data_rev);
                        dx = ('0' + d.getDate()).slice(-2) + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + d.getFullYear();
                    }
                    let rev_text = (row.rev != null) ? `${row.rev} del ${dx}` : 'Info';
                    return `<div id='div_edit${row.id}' class='div_edit'></div>
                            <span id='info_master${row.id}'
                                data-name_master='${row.real_name}'
                                data-rev='${row.rev || ''}'
                                data-data_rev='${row.data_rev || ''}'>
                            </span>
                            ${row.dele == 1 ? rev_text : `<a href='#' onclick="edit_rev(${row.id})">${rev_text}</a>`}
                            `;
                }
            },
            {
                "targets": 3,
                "render": function (data, type, row, meta) {
                    let d = new Date(data);
                    return ('0' + d.getDate()).slice(-2) + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + d.getFullYear();
                }
            },
            {
                "targets": 4, // Indice della nuova colonna "Tag Rilevati"
                "render": function (data, type, row) {
                    // Se last_scan è null, il master non è mai stato scansionato.
                    if (row.last_scan === null) {
                        return '<span class="text-muted"><i>Mai scansionato</i></span>';
                    }

                    // Se tags_found è null o vuoto dopo una scansione, significa che non ha trovato nulla.
                    if (!data) {
                        return '<span class="text-danger"><i>Nessun tag trovato</i></span>';
                    }

                    // I dati ora sono una stringa separata da virgole, es: "lt,exp,pdate"
                    const tags_array = data.split(',').filter(tag => tag.trim() !== '');

                    if (tags_array.length === 0) {
                        return '<span class="text-danger"><i>Nessun tag trovato</i></span>';
                    }

                    const tagCount = tags_array.length;
                    const countBadge = `<span class="badge badge-secondary badge-pill mr-2" title="Numero di tag trovati">${tagCount}</span>`;

                    const essential_tags = ['lt', 'exp', 'pdate'];
                    let tagsBadgesHtml = '';
                    tags_array.forEach(tag => {
                        // I tag essenziali (lt, exp, pdate) sono in verde, gli altri in blu.
                        const badgeClass = essential_tags.includes(tag) ? 'bg-success' : 'bg-primary';
                        tagsBadgesHtml += `<span class="badge ${badgeClass} m-1">${tag.toUpperCase()}</span>`;
                    });

                    // Combina il contatore e i badge dei tag
                    const tagsHtml = `<div class="d-flex align-items-center">${countBadge}<div class="d-flex flex-wrap">${tagsBadgesHtml}</div></div>`;
                    return tagsHtml;
                }
            }
        ],
        "rowCallback": function(row, data, index) {
            // Aggiunge una classe per evidenziare le righe eliminate
            if (data.dele == 1) {
                $(row).addClass('bg-danger-light');
            }
        },
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Italian.json"
        },
        initComplete: function () {
            const api = this.api();

            // Applica la ricerca solo alle colonne che hanno un input nel footer
            api.columns().every(function() {
                let searchTimer; // Dichiarato qui, ogni colonna ha il suo timer.
                const column = this;
                const input = $('input', this.footer());
                input.on('keyup change clear', function() {
                        clearTimeout(searchTimer);
                        searchTimer = setTimeout(() => {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        }, 500); // Ritardo di 500ms
                });
            });
        }
    });
    // Memorizza l'istanza di DataTable globalmente se necessaria per altre funzioni
    window.masterDataTable = table;

    // Logica per il menu a tendina "Sposta in..."
    $('#tbl_articoli').on('click', '.move-btn', function(e) {
        e.preventDefault();
        const id_doc = $(this).data('id');
        let target_archivio = $(this).data('archivio');
        const target_text = $(this).text();

        Swal.fire({
            title: 'Sei sicuro?',
            text: `Vuoi spostare questo master in "${target_text}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sì, sposta!',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
                const baseUrl = $('meta[name="base-url"]').attr('content');
                const csrf = metaElements.length > 0 ? metaElements[0].content : "";

                $.ajax({
                    url: `${baseUrl}/move-master`, // Nuovo endpoint
                    type: 'POST',
                    data: {
                        _token: csrf,
                        id_doc: id_doc,
                        target_archivio: target_archivio // Nuovo parametro
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Spostato!', response.message, 'success');
                            // Ricarica la tabella per rimuovere la riga
                            window.masterDataTable.ajax.reload(null, false);
                        } else {
                            Swal.fire('Errore!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Si è verificato un errore durante la richiesta al server.';
                        console.error(xhr);
                        Swal.fire('Errore!', errorMsg, 'error');
                    }
                });
            }
        });
    });

    // Funzione per resettare tutti i filtri
    function resetAllFilters() {
        // Resetta i filtri per colonna
        table.columns().search('').draw();
        // Resetta il filtro globale
        table.search('').draw();
        // Pulisce gli input nel footer
        $('#tbl_articoli tfoot input').val('');
        // Resetta il filtro custom e ridisegna la tabella
        customFilter = '';
        custom_filter_sistemato = null;
        // Non resetto il filtro archivio, quello si resetta solo scegliendo 'attivo'
    }

    // Evento per il pulsante "Solo mai scansionati"
    $('#filtra_mai_scansionati').on('click', function() {
        resetAllFilters();
        // Applica il filtro sulla colonna 'Tag Rilevati' per cercare 'mai'
        // L'indice 4 corrisponde alla colonna "Tag Rilevati"
        table.column(4).search('mai').draw();
    });

    // Evento per il pulsante "Filtra tag essenziali non rilevati"
    $('#filtra_tag_mancanti').on('click', function() {
        // Pulisce tutti i filtri esistenti
        resetAllFilters();
        // Imposta il flag per il filtro custom
        customFilter = 'tag_essenziali_mancanti';
        // Ricarica i dati dalla tabella. La funzione 'ajax.data' aggiungerà il parametro.
        table.draw();
    });

    function updateSistematoButtons() {
        if (custom_filter_sistemato === 'sistemati') {
            $('#filtra_sistemati').removeClass('btn-outline-success').addClass('btn-success');
            $('#filtra_non_sistemati').removeClass('btn-danger').addClass('btn-outline-danger');
        } else if (custom_filter_sistemato === 'non_sistemati') {
            $('#filtra_non_sistemati').removeClass('btn-outline-danger').addClass('btn-danger');
            $('#filtra_sistemati').removeClass('btn-success').addClass('btn-outline-success');
        } else {
            $('#filtra_sistemati').removeClass('btn-success').addClass('btn-outline-success');
            $('#filtra_non_sistemati').removeClass('btn-danger').addClass('btn-outline-danger');
        }
    }

    $('#filtra_sistemati').on('click', function() {
        if (custom_filter_sistemato === 'sistemati') {
            custom_filter_sistemato = null; // Deseleziona se già attivo
        } else {
            resetAllFilters();
            custom_filter_sistemato = 'sistemati';
        }
        updateSistematoButtons();
        table.draw();
    });

    $('#filtra_non_sistemati').on('click', function() {
        if (custom_filter_sistemato === 'non_sistemati') {
            custom_filter_sistemato = null; // Deseleziona se già attivo
        } else {
            resetAllFilters();
            custom_filter_sistemato = 'non_sistemati';
        }
        updateSistematoButtons();
        table.draw();
    });

    // Evento per il pulsante "Reset Filtri"
    $('#reset_filtri').on('click', function() {
        resetAllFilters();
        updateSistematoButtons();
        table.draw(); // Ricarica la tabella senza filtri custom
    });

    // Logica per il nuovo filtro "Archivio"
    $('#archivio-filter-menu').on('click', '.archivio-filter', function(e) {
        e.preventDefault();
        const newFilter = $(this).data('archivio');
        const newFilterText = $(this).text();

        custom_filter_archivio = newFilter;
        $('#current-archivio-filter').text(newFilterText);
        table.draw();
    });



} );


function dele_master(id_ref) {
    Swal.fire({
        title: 'Sei sicuro?',
        text: "Il master verrà spostato nel cestino!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sì, elimina!',
        cancelButtonText: 'Annulla'
    }).then((result) => {
        if (result.isConfirmed) {
            const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
            const csrf = metaElements.length > 0 ? metaElements[0].content : "";
            const baseUrl = $('meta[name="base-url"]').attr('content');

            fetch(`${baseUrl}/dele_master`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-CSRF-TOKEN': csrf
                },
                body: `id_ref=${id_ref}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Errore di rete o del server.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire('Eliminato!', data.message, 'success');
                    window.masterDataTable.ajax.reload(null, false); // Ricarica la tabella
                } else {
                    Swal.fire('Errore!', data.message || 'Impossibile eliminare il master.', 'error');
                }
            })
            .catch(error => {
                console.error('Errore nella chiamata fetch:', error);
                Swal.fire('Errore!', 'Si è verificato un problema di comunicazione con il server.', 'error');
            });
        }
    });
}

function restore_master(id_ref) {
    Swal.fire({
        title: 'Sei sicuro?',
        text: "Il master verrà ripristinato.",
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sì, ripristina!',
        cancelButtonText: 'Annulla'
    }).then((result) => {
        if (result.isConfirmed) {
            const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
            const csrf = metaElements.length > 0 ? metaElements[0].content : "";
            const baseUrl = $('meta[name="base-url"]').attr('content');

            fetch(`${baseUrl}/restore_master`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-CSRF-TOKEN': csrf
                },
                body: `id_ref=${id_ref}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Errore di rete o del server.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire('Ripristinato!', data.message, 'success');
                    // Ricarica la tabella per rimuovere la riga ripristinata dalla vista corrente
                    window.masterDataTable.ajax.reload(null, false);
                } else {
                    Swal.fire('Errore!', data.message || 'Impossibile ripristinare il master.', 'error');
                }
            })
            .catch(error => {
                console.error('Errore nella chiamata fetch:', error);
                Swal.fire('Errore!', 'Si è verificato un problema di comunicazione con il server.', 'error');
            });
        }
    });
}

function save_master(id_ref) {
	name_master_edit=$("#name_master_edit").val();
	rev_edit=$("#rev_edit").val();
	data_rev_edit=$("#data_rev_edit").val();
	if (name_master_edit.length==0) {
		Swal.fire("Attenzione", "Controllare il nome assegnato!", "warning");
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
			Swal.fire("Errore", "Problema riscontrato durante la modifica", "error");
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


function change_master(id_doc, id_clone_from, id_ref) {
    Swal.fire({
        title: 'Sei sicuro?',
        text: "Vuoi rendere il clone master ufficiale?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sì, procedi!',
        cancelButtonText: 'Annulla'
    }).then((result) => {
        if (result.isConfirmed) {
            $("#btn_change" + id_ref).text('Attendere...');
            $(".btnall").attr('disabled', true);

            const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
            const csrf = metaElements.length > 0 ? metaElements[0].content : "";

            fetch("change_master", {
                    method: 'post',
                    headers: {
                        "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
                        "X-CSRF-Token": csrf
                    },
                    body: "id_doc=" + id_doc + "&id_clone_from=" + id_clone_from,
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    }
                })
                .then(resp => {
                    if (resp.header == "OK") {
                        Swal.fire('Successo!', 'Master associato!', 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Errore!', 'Problema riscontrato durante l\'operazione.', 'error');
                        $(".btnall").attr('disabled', false);
                        $("#btn_change" + id_ref).text('Change Master');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Errore!', 'Si è verificato un errore di comunicazione.', 'error');
                    $(".btnall").attr('disabled', false);
                    $("#btn_change" + id_ref).text('Change Master');
                });
        }
    });
}

function duplica_master(id_doc, id_ref) {
    Swal.fire({
        title: 'Sei sicuro?',
        text: "Vuoi duplicare il master?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sì, duplica!',
        cancelButtonText: 'Annulla'
    }).then((result) => {
        if (result.isConfirmed) {
            const name_master = $("#info_master" + id_ref).attr('data-name_master');
            const name_clone = name_master + "-copia";
            $("#btn_dup" + id_ref).text('Attendere...');
            $(".btnall").attr('disabled', true);

            const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
            const csrf = metaElements.length > 0 ? metaElements[0].content : "";

            fetch("duplica_master", {
                    method: 'post',
                    headers: {
                        "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
                        "X-CSRF-Token": csrf
                    },
                    body: "id_doc=" + id_doc + "&name_clone=" + name_clone,
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    }
                })
                .then(resp => {
                    if (resp.header == "OK") {
                        Swal.fire('Duplicato!', 'Master duplicato con successo.', 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Errore!', 'Problema riscontrato durante la duplicazione.', 'error');
                        $("#btn_dup" + id_ref).text('Duplica');
                        $(".btnall").attr('disabled', false);
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Errore!', 'Si è verificato un errore di comunicazione.', 'error');
                    $("#btn_dup" + id_ref).text('Duplica');
                    $(".btnall").attr('disabled', false);
                });
        }
    });
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


function toggleSistemato(id_doc, button, baseUrl) {
    const url = `${baseUrl}/toggle_sistemato`;
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    $(button).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ id_doc: id_doc })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Ricarica la riga per aggiornare lo stato senza ridisegnare tutta la tabella
            window.masterDataTable.row($(button).closest('tr')).ajax.reload(null, false);
        } else {
            Swal.fire('Errore!', data.message || 'Impossibile aggiornare lo stato.', 'error');
            $(button).prop('disabled', false).html($(button).data('original-text'));
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        Swal.fire('Errore!', 'Si è verificato un errore di comunicazione.', 'error');
        $(button).prop('disabled', false).html($(button).data('original-text'));
    });
}

// Variabile globale per controllare l'interruzione del processo
let isVerificationCancelled = false;
let tagsToVerify = []; // Variabile globale per i tag da verificare

// 1. Mostra la modale di selezione dei tag
function showTagSelectionModal() {
    $('#tagSelectionModal').modal('show');
}

// 2. Raccoglie i tag e mostra la modale di scelta dell'ambito
function startVerificationProcess() {
    tagsToVerify = [];
    // Raccoglie i tag standard selezionati
    $('#tagSelectionModal .form-check-input:checked').each(function() {
        tagsToVerify.push($(this).val());
    });

    // Raccoglie e pulisce i tag custom
    const customTags = $('#custom_tags').val().split(',')
        .map(tag => tag.trim())
        .filter(tag => tag.length > 0);

    tagsToVerify = [...new Set([...tagsToVerify, ...customTags])]; // Unisce e rimuove duplicati

    $('#tagSelectionModal').modal('hide');
    $('#verificationChoiceModal').modal('show');
}

// Funzione per aggiornare il token CSRF prima di una richiesta POST critica
async function refreshCsrfToken() {
    const baseUrl = $('meta[name="base-url"]').attr('content');
    const requestUrl = `${baseUrl}/refresh-csrf`;

    try {
        const response = await fetch(requestUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
        });
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        // Aggiorna il meta tag con il nuovo token
        $('meta[name="csrf-token"]').attr('content', data.token);
        return data.token;
    } catch (error) {
        console.error('Impossibile aggiornare il token CSRF:', error);
        return null; // Restituisce null se fallisce
    }
}

// Funzione principale per la verifica dei tag, ora accetta una modalità ('page' o 'all')
async function verificaTagMaster(mode) {
    $('#verificationChoiceModal').modal('hide');
    const btn = $('#btn_verifica_tag_master');
    btn.attr('disabled', true);
    isVerificationCancelled = false;

    // Aggiorna il token CSRF prima di procedere
    const newCsrfToken = await refreshCsrfToken();
    if (!newCsrfToken) {
        Swal.fire("Errore di sessione", "Impossibile avviare la verifica. Prova a ricaricare la pagina.", "error");
        btn.attr('disabled', false);
        return;
    }
    // Ottieni gli ID in base alla modalità scelta
    let idsToVerify;
    const url = `${$('meta[name="base-url"]').attr('content')}/elenco_master`;

    if (mode === 'page') {
        // Solo le righe nella pagina corrente che corrispondono al filtro
        idsToVerify = window.masterDataTable.rows({ page: 'current', search: 'applied' }).data().map(row => row.id_doc).toArray();
        if (idsToVerify.length === 0) {
            Swal.fire("Attenzione", "Nessun master trovato nella pagina corrente per la verifica.", "warning");
            btn.attr('disabled', false);
            return;
        }
        await processIds(idsToVerify, newCsrfToken);
    } else {
        // NUOVA LOGICA: Chiedi al server tutti gli ID che corrispondono ai filtri correnti
        const table = window.masterDataTable;
        const columnFilters = [];
        table.columns().every(function () {
            const col = this;
            columnFilters.push({
                search: { value: col.search() }
            });
        });

        const requestBody = new URLSearchParams();
        requestBody.append('action', 'get_all_filtered_ids');
        requestBody.append('search[value]', table.search());
        requestBody.append('custom_filter', customFilter);
        columnFilters.forEach((filter, index) => {
            if (filter.search.value) {
                requestBody.append(`columns[${index}][search][value]`, filter.search.value);
            }
        });

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
                    "X-CSRF-Token": newCsrfToken
                },
                body: requestBody
            });
            const data = await response.json();
            if (data.success) {
                if (data.ids.length === 0) {
                    Swal.fire("Attenzione", "Nessun master trovato con i filtri applicati per la verifica.", "warning");
                    btn.attr('disabled', false);
                    return;
                }
                await processIds(data.ids, newCsrfToken);
            } else {
                throw new Error(data.message || 'Impossibile recuperare gli ID dal server.');
            }
        } catch (error) {
            Swal.fire('Errore', error.message, 'error');
            btn.attr('disabled', false);
        }
    }

    if (idsToVerify.length === 0) {
        Swal.fire("Attenzione", "Nessun master trovato per la verifica.", "warning");
        btn.attr('disabled', false);
        return;
    }
}

// Funzione di supporto per eseguire il processo di verifica
async function processIds(idsToVerify, csrf) {

    // Setup e mostra la modale di progresso
    $('#progress-bar').css('width', '0%').attr('aria-valuenow', 0);
    $('#progress-status').text(`In attesa di iniziare... 0 / ${idsToVerify.length}`);
    $('#progressModal').modal('show');

    // Resetta lo stato del pulsante Annulla per la nuova operazione
    const cancelBtn = $('#cancel-verification-btn');
    cancelBtn.attr('disabled', false).html('Annulla');

    // Gestione del pulsante Annulla
    $('#cancel-verification-btn').off('click').on('click', function() {
        const btn = $(this);
        btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Annullamento...');
        isVerificationCancelled = true;
    });

    const totalIds = idsToVerify.length;
    let processedCount = 0;
    const chunkSize = 5; // Processa 5 ID alla volta per non sovraccaricare il server
    // Anche qui, assumiamo la sottodirectory '/certnew' per coerenza.
    const url = `${$('meta[name="base-url"]').attr('content')}/elenco_master`;
    // Processa gli ID in "chunks" (pacchetti)
    for (let i = 0; i < totalIds; i += chunkSize) {
        if (isVerificationCancelled) {
            $('#progress-status').text(`Verifica annullata dall'utente. Processati: ${processedCount} / ${totalIds}`);
            $('#progress-bar').addClass('bg-danger');
            break;
        }

        const chunk = idsToVerify.slice(i, i + chunkSize);

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
                    "X-CSRF-Token": csrf
                },
                body: "action=verifica_tag&ids=" + JSON.stringify(chunk) + "&tags=" + JSON.stringify(tagsToVerify),
            });

            if (!response.ok) {
                throw new Error(`Errore di rete: ${response.statusText}`);
            }

            const result = await response.json();
            if (result.success) {
                processedCount += result.processed;
                const percentage = Math.round((processedCount / totalIds) * 100);
                $('#progress-bar').css('width', `${percentage}%`).attr('aria-valuenow', percentage);
                $('#progress-status').text(`Processati ${processedCount} / ${totalIds}`);
            } else {
                throw new Error(result.message || "Errore sconosciuto dal server.");
            }

        } catch (error) {
            console.error("Errore durante la verifica di un chunk:", error);
            $('#progress-status').text(`Errore: ${error.message}. Processati: ${processedCount} / ${totalIds}`);
            $('#progress-bar').addClass('bg-danger');
            isVerificationCancelled = true; // Ferma il processo in caso di errore
            break;
        }
    }

    // Azioni finali dopo il completamento o l'annullamento
    setTimeout(() => {
        $('#progressModal').modal('hide'); // Nascondi la modale
        const btn = $('#btn_verifica_tag_master');
        btn.attr('disabled', false);
        
        if (isVerificationCancelled) {
            Swal.fire('Interrotto', `Verifica interrotta. Sono stati processati ${processedCount} documenti su ${totalIds}.`, 'info');
        } else {
            Swal.fire('Completato!', `Verifica completata! Processati ${processedCount} documenti.`, 'success');
        }

        // Ricarica la tabella per mostrare i risultati
        window.masterDataTable.ajax.reload(null, false);

        // Resetta la progress bar per la prossima esecuzione
        setTimeout(() => {
            $('#progress-bar').removeClass('bg-danger');
        }, 500);

    }, isVerificationCancelled ? 2000 : 1000); // Attendi un po' di più se annullato per far leggere il messaggio
}