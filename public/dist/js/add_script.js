$(document).ready(function() {
	//load_init()
})


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
		$( ".dati" ).each(function(){
			if (this.value.length!=0)  $(this).attr('disabled', true);
		})
		$("#btn_save_cont").text("Salva dati");
		$("#btn_save_cont").attr('disabled', false);
	
		//$('#ifr_doc').attr('srcdoc', resp.content);
		
	})
	.catch(status, err => {
		return console.log(status, err);
	})	
}


function load_init() {

	$('.user_content').change(function(){
		if (ruoli.indexOf("MANCQ")!=-1) {
			$(".user_content").attr('disabled','disabled');
		}	
		
		id=this.id
		var value=$("#"+id).val()
		$("#H"+id).html(value)
		//$("#H"+id).show()
		
		$("#div_note").hide();
		fl_e=0
		flag_enable_date=true;
		
		$(".user_content").each(function() {
			if (this.value=="NC"){
				//$(this).css("background-color","yellow");
				fl_e=1
			}
			else {
				hasc1=$( '#'+this.id ).hasClass( "esclusione_for_date" )
				hasc2=$( '#'+this.id ).hasClass( "extra_esclusione" )
				if (this.value=="" && hasc1==false && hasc2==false && $("#"+this.id).is(":visible")) {
					flag_enable_date=false
				}	
				//$(this).css("background-color","white");
			}	
		})

		if (fl_e==1) $("#div_note").show();
		if (this.value=="NC") {
			alert("Attenzione!\nIn caso di risultati non conformi compilare il campo note in fondo al certificato")
		}
		
		if (ruoli.indexOf("MANCQ")==-1) {
			$(".DATE_EN_CQ").attr('disabled','disabled'); 
		}

		$(".DATE_EN_FIRMA").attr('disabled','disabled');
		if (flag_enable_date==true && ruoli.indexOf("MANCQ")==-1) $(".DATE_EN_FIRMA").removeAttr('disabled');
		if (flag_enable_date==true && ruoli.indexOf("MANCQ")!=-1) $(".DATE_EN_CQ").removeAttr('disabled');
		
		
	})

	
	$('.esclusione_for_date').change(function(){		
		ready=true
		$(".esclusione_for_date").each(function() {
			if (this.value.length==0) ready=false
		})
		ready_firma=true
		$(".DATE_EN_FIRMA").each(function() {
			if (this.value.length==0) ready_firma=false
		})
		$("#provv_ready").val("0")	
		if (ready_firma==true) $("#provv_ready").val("1")
		if (ready==true) {
			if (!confirm("Tutti i dati editabili sono stati compilati.\nVERRANNO INIBITI TUTTI CAMPI.\nSaranno comunque ancora disponibili fino a quando non sarà salvato il documento.\n\nContinuare?")) {
				this.value='';
				return false;
			}	
			$("#provv_ready").val("2")
			
			//Inibizione campi user ---> visione campi real
			$(".user_content").remove()
			$(".real_content").show()
		}
	})	
	
		
	$( ".user_content" ).trigger( "change" );
	$( ".esclusione_for_date" ).trigger( "change" );
}


