var js_clone=0
var hasClickedCorreggi = false; // Flag per tracciare il click
var originalModalBody;
var originalModalFooter;

$(document).ready( function () {
    $( ".enable_tag" ).on( "click", function(event) {
      id_ref=$(this).attr('data-id');
      if ($(this).prop('checked'))
        tg=$("#tg"+id_ref).attr('disabled', false);
      else
        tg=$("#tg"+id_ref).attr('disabled', true);
    })

    // Salvo il contenuto originale della modale all'avvio
    originalModalBody = $('#tagProblemModalBody').html();
    originalModalFooter = $('#tagProblemModalFooter').html();

    // Aggiungo un ID al link per identificarlo facilmente
    $('#tagProblemModal a.btn-primary').attr('id', 'btnCorreggiProvvisorio');

    // Quando l'utente clicca per correggere, imposto il flag
    $(document).on('click', '#btnCorreggiProvvisorio', function() {
        hasClickedCorreggi = true;
    });

    // Quando la finestra del browser torna ad essere attiva (focus)
    $(window).on('focus', function() {
        // Se l'utente aveva cliccato per correggere, aggiorno la modale
        if (hasClickedCorreggi) {
            $('#tagProblemModalBody').html('<p>Per visualizzare le modifiche apportate al documento, è necessario aggiornare la pagina.</p><p>Clicca sul pulsante qui sotto per ricaricare l\'anteprima.</p>');
            $('#tagProblemModalFooter').html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button><button type="button" class="btn btn-success" onclick="location.reload(true);"><i class="fas fa-sync-alt"></i> Aggiorna Pagina</button>');
            // Resetto il flag per evitare che si attivi di nuovo
            hasClickedCorreggi = false;
        }
    });

    // Ripristino il contenuto originale quando la modale viene chiusa
    $('#tagProblemModal').on('hidden.bs.modal', function () {
        $('#tagProblemModalBody').html(originalModalBody);
        $('#tagProblemModalFooter').html(originalModalFooter);
    });
});


function setframe(url) {
  $("#div_frame").empty()
  html="<iframe id='ifr_doc' src='"+url+"' style='width:600px; height:1500px;' frameborder='0'></iframe>";
  $("#div_frame").html(html)
}

function save_tag_edit(doc_id) { // Questa funzione non sembra essere usata, ma la aggiorno per coerenza
  let check_save = false;
  let str = "doc_id=" + doc_id;

  // Itera su tutti gli input che devono essere salvati
  $(".dati, .dati_checkbox").each(function() {
    const isEnabledCheckbox = $(this).closest('div').find('.enable_tag').prop('checked');
    
    // Controlla se l'elemento è un checkbox per l'abilitazione e se è spuntato
    if ($(this).hasClass('enable_tag') && $(this).prop('checked')) {
      check_save = true;
      const id_ref = $(this).data("id");
      const target_input = $("#tg" + id_ref);
      const tag = target_input.data('tag');
      let value = '';

      if (target_input.is(':checkbox')) {
        value = target_input.is(':checked') ? '☑' : '';
      } else {
        value = target_input.val();
      }
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

function load_clone_click(doc_id) {
  $("#btn_load_clone, #btn_load_clone_2").html("<i class='fas fa-spinner fa-spin'></i> Caricamento...");
  $("#btn_load_clone, #btn_load_clone_2").attr('disabled', true);
  load_clone(doc_id);
}

function load_clone(doc_id) {
  
  url=$("#url").val()
  const metaElements = document.querySelectorAll('meta[name="csrf-token"]');
  const csrf = metaElements.length > 0 ? metaElements[0].content : "";

  // Mostra un messaggio di caricamento nell'iframe
  var loadingHtml = `
    <style>
      .loader {
        border: 16px solid #f3f3f3;
        border-top: 16px solid #3498db;
        border-radius: 50%;
        width: 120px;
        height: 120px;
        animation: spin 2s linear infinite;
      }
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    </style>
    <div style="display: flex; justify-content: center; align-items: center; height: 100%; flex-direction: column; margin-top: 15px;">
      <div class="loader"></div>
      <p style="margin-top: 20px; font-family: sans-serif; font-size: 16px;">Rilevazione dei TAG presenti nel
      documento in corso...</p>
    </div>
  `;
  $('#ifr_doc').attr('srcdoc', loadingHtml);

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
      // Aggiungo un piccolo ritardo per assicurarmi che il DOM dell'iframe sia pronto.
      if (js_clone === 1) setTimeout(load_js, 100);
      check_load_js_for_clone(); // Riabilita il pulsante dopo il caricamento del contenuto

  })
  .catch(status, err => {
      return console.log(status, err);
  })   

  
}

function check_load_js_for_clone() {
  // La logica di caricamento JS è stata spostata in load_clone().
  // Questa funzione ora serve solo a riabilitare il pulsante.
  $("#btn_load_clone, #btn_load_clone_2").html("<i class='fas fa-cogs'></i> Clicca quì per compilare i tag rilevati nel provvisorio");
  $("#btn_load_clone, #btn_load_clone_2").attr('disabled', false);
}

function load_js() {
  content=$("#ifr_doc").contents().find("html").html();
  var n = content.indexOf("jquery");
  
  // Rimuovo il controllo perché ora lo script viene iniettato sempre quando necessario.
  // if (n!=-1) return true;
  js_clone = 0; // Resetto il flag qui

  const frames = window.frames; // or const frames = window.parent.frames;
  
  var iFrameHead =frames[0].document.getElementsByTagName("head")[0];

  
	//Inietto il codice javascript per la manipolazione successiva del documento (inject nell'head)
	//carico prima libreria jquery
	var myscript = document.createElement('script');
	myscript.type = 'text/javascript';
  url=$("#url").val()

  
  var iFrameHead =frames[0].document.getElementsByTagName("head")[0];

  
  // Inject Bootstrap CSS
  var bootstrapCss = document.createElement('link');
  bootstrapCss.rel = 'stylesheet';
  bootstrapCss.href = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css';
  iFrameHead.appendChild(bootstrapCss);

  // Inject Font Awesome CSS
  var fontAwesomeCss = document.createElement('link');
  fontAwesomeCss.rel = 'stylesheet';
  fontAwesomeCss.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';
  iFrameHead.appendChild(fontAwesomeCss);

  
	//Inietto il codice javascript per la manipolazione successiva del documento (inject nell'head)
	//carico prima libreria jquery
	var jqueryScript = document.createElement('script');
	jqueryScript.type = 'text/javascript';
  url=$("#url").val()

	jqueryScript.src = url+"/plugins/jquery/jquery.min.js";
	iFrameHead.appendChild(jqueryScript);

	jqueryScript.onload = jqueryScript.onreadystatechange = function(){
    // Una volta caricato jQuery, carico Bootstrap JS
    var bootstrapJs = document.createElement('script');
    bootstrapJs.type = 'text/javascript';
    bootstrapJs.src = url + "/plugins/bootstrap/js/bootstrap.bundle.min.js";
    iFrameHead.appendChild(bootstrapJs);

    bootstrapJs.onload = bootstrapJs.onreadystatechange = function() {
      // Una volta caricato Bootstrap JS, carico lo script personalizzato
      var d = new Date();
      var t = d.getTime();
      //N.B.: CARICO LO SCRIPT utile alla gestione degli eventi nel documento provvisorio
      //carico il mio script quando sono certo che la libreria jquery e bootstrap sono caricate!!!!
      var customScript = document.createElement('script');
      customScript.type = 'text/javascript';
      customScript.src =  url+'/dist/js/add_script.js?ver='+t;
      iFrameHead.appendChild(customScript);
    };
	}

    // Dopo il caricamento del contenuto e l'iniezione degli script,
    // imposto la larghezza del body a 'auto' per un layout flessibile.
    setTimeout(function() { 
      const iframeBody = $('#ifr_doc').contents().find('body');
      if (iframeBody.length > 0) {
        // Sovrascrivo gli stili di Google Docs usando !important.
        // Rimuovo il max-width per evitare che il contenuto sia limitato in larghezza.
        iframeBody[0].style.setProperty('max-width', 'none', 'important');
        // Imposto una larghezza fissa (es. 900px) e uso i margini automatici
        // per centrare il blocco del corpo orizzontalmente.
        iframeBody[0].style.setProperty('width', '900px', 'important');
      }
    }, 200); // Un leggero ritardo per garantire che il DOM sia pronto.
}