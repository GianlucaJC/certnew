$(document).ready(function() {
	
})
// NOTA: Le funzioni save_to_ready e execute_save_to_ready potrebbero diventare obsolete in futuro,
// dato che la funzionalità di passaggio a "Pronto" è stata spostata principalmente nell'elenco provvisori.
function save_to_ready(doc_id) {
	// Mostra la modale di Bootstrap
	$('#confirmStateChangeModal').modal('show');

	// Gestisce il click sul pulsante di conferma della modale
	$("#confirmStateChangeBtn").off('click').on('click', function() {
		$('#confirmStateChangeModal').modal('hide');
		execute_save_to_ready(doc_id);
	});
}

function execute_save_to_ready(doc_id) {
	$("#btn_ready").text("Attendere...");
	$("#btn_ready").attr('disabled', true);
	
	url=$("#url").val()
	const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
	const csrf = metaElements.length > 0 ? metaElements[0].content : "";
  
	fetch(url+"/save_to_ready", {
		method: 'post',
		headers: {
		  "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
		  "X-CSRF-Token": csrf
		},
		body:"doc_id="+doc_id,
	})
	.then(response => {
		if (response.ok) {
		   return response.json();
		}
	})
	.then(resp=>{
		$("#btn_ready").hide();
		alert("Stato assegnato!")
	
		//$('#ifr_doc').attr('srcdoc', resp.content);
		
	})
	.catch(status, err => {
		return console.log(status, err);
	})	
}

function save_all(doc_id) {
	let str = "doc_id=" + doc_id;
	let hasValues = false;

	// Itera su tutti gli elementi con classe 'dati' (input di testo, data, select)
	$(".dati").each(function() {
		const tag = $(this).data('tag');
		// Prende semplicemente il valore dell'input, che sia testo, data o il valore di una select
		let value = $(this).val();

		if (value !== null && value !== '') {
			hasValues = true;
			str += "&" + tag + "=" + encodeURIComponent(value);
		}
	});
	$("#btn_save_cont").text("Attendere...");

	// Rimuovo eventuali messaggi di salvataggio precedenti
	$("#save-feedback").remove();

	// Creo un alert di Bootstrap per il feedback
	const feedbackAlert = `
		<div id="save-feedback" class="alert alert-success mt-3" role="alert" style="display: none;">
			Dati salvati con successo!
		</div>
	`;
	$("#btn_save_cont").parent().append(feedbackAlert);

	$("#btn_save_cont").attr('disabled', true);

	
	const url = window.parent.$("#url").val(); // Usa il valore dalla finestra padre
	const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
	const csrf = metaElements.length > 0 ? metaElements[0].content : "";
  
	fetch(url+"/save_dati", {
		method: 'post',
		headers: {
		  "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
		  "X-CSRF-Token": csrf
		},
		body:str,
	})
	.then(response => {
		if (response.ok) {
		   return response.json();
		}
	})
	.then(resp=>{
		entr=0
		// Disabilita gli input che sono stati compilati
		$(".dati").each(function() {
			// Un campo è considerato compilato se il suo valore non è una stringa vuota.
			let filled = $(this).val() !== '';
			
			if (filled) {
				$(this).attr('disabled', true);
			} else {
				entr++;
			}
		});
		if (entr==0) {
			$("#btn_save_cont").hide();
			// Tutti i tag sono stati compilati, quindi mostro il bottone per passare allo stato successivo.
			var readyCardHtml = `
				<div class="alert alert-success mt-3" role="alert">
					<h4 class="alert-heading">Compilazione Completata!</h4>
					<p>Tutti i tag del documento sono stati compilati. Ora puoi passare questo certificato allo stato "Pronto" dalla pagina "Elenco Provvisori".</p>
				</div>
			`;
			$("#btn_save_cont").parent().append(readyCardHtml);
		}
		else {	
			$("#btn_save_cont").text("Salva dati");
			$("#btn_save_cont").attr('disabled', false);
			// Mostro il messaggio di successo e lo nascondo dopo qualche secondo
			$("#save-feedback").fadeIn().delay(3000).fadeOut();
		}	
	
		//$('#ifr_doc').attr('srcdoc', resp.content);
		
	})
	.catch(status, err => {
		return console.log(status, err);
	})	
}
