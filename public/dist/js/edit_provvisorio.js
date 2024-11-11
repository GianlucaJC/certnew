var js_clone=0
$(document).ready( function () {
    $( ".enable_tag" ).on( "click", function(event) {
      id_ref=$(this).attr('data-id');
      if ($(this).prop('checked'))
        tg=$("#tg"+id_ref).attr('disabled', false);
      else
        tg=$("#tg"+id_ref).attr('disabled', true);
    })
} );


function setframe(url) {
  $("#div_frame").empty()
  html="<iframe id='ifr_doc' src='"+url+"' style='width:600px; height:1500px;' frameborder='0'></iframe>";
  $("#div_frame").html(html)
}

function save_tag_edit(doc_id) {
  check_save=false
  formData = new FormData();
  
  str="doc_id="+doc_id
  $( ".enable_tag" ).each(function(){
    if ($(this).prop('checked')) {
      check_save=true
      id= "tg"+$(this).attr("data-id");
      tag=$("#"+id).attr('data-tag');
      value=$("#"+id).val();
      console.log("id",id,"tag",tag,"value",value)
      if (str.length!=0) str+="&"
      str+=tag+"="+value  
    }


  })

  if (check_save===false) {
    alert("Selezionare almeno un tag da compilare!")
    return false;
  }

  $("#btn_save_provv").attr('disabled', true);
  $("#btn_save_provv").text("Salvataggio in corso. Attendere....");

  url=$("#url").val()
  const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
  const csrf = metaElements.length > 0 ? metaElements[0].content : "";

  fetch(url+"/save_tag_edit", {
      method: 'post',
      headers: {
        "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "X-CSRF-Token": csrf
      },
      body: str,
  })
  .then(response => {
      if (response.ok) {
         return response.json();
      }
  })
  .then(resp=>{
      $("#btn_save_provv").text("Salva");
      $("#btn_save_provv").attr('disabled', false);
      $( ".dati_compilazione" ).each(function(){
          $(this).attr('disabled', true);
      })

      num_elem=0
      $( ".enable_tag" ).each(function(){
        if ($(this).prop('checked')) {
          $(this).prop('checked',false)
          $(this).hide()
          id= "tg"+$(this).attr("data-id");
          tag=$("#"+id).attr('disabled', true);
        } else num_elem++
      })      
      if (num_elem==0) $("#btn_save_provv").hide();
  
      url="https://docs.google.com/document/d/"+doc_id+"/preview?usp=embed_googleplus"
      setframe(url)
  })
  .catch(status, err => {
      return console.log(status, err);
  })    
}


function view_tag(doc_id,tag) {

  url=$("#url").val()
  const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
  const csrf = metaElements.length > 0 ? metaElements[0].content : "";

  fetch(url+"/view_tag", {
      method: 'post',
      headers: {
        "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "X-CSRF-Token": csrf
      },
      body: "doc_id="+doc_id+"&tag="+tag,
  })
  .then(response => {
      if (response.ok) {
         return response.json();
      }
  })
  .then(resp=>{
      $('#ifr_doc').attr('srcdoc', resp.content);
  })
  .catch(status, err => {
      return console.log(status, err);
  })    

}

function load_clone(doc_id) {
  
  url=$("#url").val()
  const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
  const csrf = metaElements.length > 0 ? metaElements[0].content : "";

  fetch(url+"/load_clone", {
      method: 'post',
      headers: {
        "Content-type": "application/x-www-form-urlencoded; charset=UTF-8",
        "X-CSRF-Token": csrf
      },
      body: "doc_id="+doc_id,
  })
  .then(response => {
      if (response.ok) {
         return response.json();
      }
  })
  .then(resp=>{
      $('#ifr_doc').attr('srcdoc', resp.content);
      
  })
  .catch(status, err => {
      return console.log(status, err);
  })   

  
}

function check_load_js_for_clone() {
  if (js_clone==1) load_js()
  js_clone=0
}

function load_js() {
  content=$("#ifr_doc").contents().find("html").html();
  var n = content.indexOf("jquery");
  
	if (n!=-1) return true;
  const frames = window.frames; // or const frames = window.parent.frames;
  
  var iFrameHead =frames[0].document.getElementsByTagName("head")[0];

  
	//Inietto il codice javascript per la manipolazione successiva del documento (inject nell'head)
	//carico prima libreria jquery
	var myscript = document.createElement('script');
	myscript.type = 'text/javascript';
  url=$("#url").val()

  
	myscript.src = url+"/plugins/jquery/jquery.min.js";
	iFrameHead.appendChild(myscript);

	myscript.onload = myscript.onreadystatechange = function(){
		var d = new Date();
		t = d.getTime();
    //N.B.: CARICO LO SCRIPT utile alla gestione degli eventi nel documento provvisorio
		//carico il mio script quando sono certo che la libreria jquery è caricata, altrimenti lo script è inutilizzabile!!!!
		var myscript = document.createElement('script');
		myscript.type = 'text/javascript';
		myscript.src =  url+'/dist/js/add_script.js?ver='+t;
		iFrameHead.appendChild(myscript);
	}
}