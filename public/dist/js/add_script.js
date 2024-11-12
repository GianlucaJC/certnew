$(document).ready(function() {
	
})

function save_to_ready(doc_id) {
	if (!confirm('Sicuri di cambiare lo stato del documento?')) return false;
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
	str="doc_id="+doc_id
	
	$( ".dati" ).each(function(){
		if (this.value.length!=0) {
			tag= $(this).attr("data-id_ref");
			value=this.value;
			console.log("tag",tag,"value",value)
			if (str.length!=0) str+="&"
			str+=tag+"="+value  
		}
	})	
	$("#btn_save_cont").text("Attendere...");
	$("#btn_save_cont").attr('disabled', true);

	


	url=$("#url").val()
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
		$( ".dati" ).each(function(){
			if (this.value.length!=0)  $(this).attr('disabled', true);
			else entr++
		})
		if (entr==0) 
			$("#btn_save_cont").hide();
		else {	
			$("#btn_save_cont").text("Salva dati");
			$("#btn_save_cont").attr('disabled', false);
		}	
	
		//$('#ifr_doc').attr('srcdoc', resp.content);
		
	})
	.catch(status, err => {
		return console.log(status, err);
	})	
}

