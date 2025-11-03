@extends('all_views.viewmaster.index')

@section('title', 'Guida Operatore')

@section('content_main')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Guida per l'Operatore</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
              <li class="breadcrumb-item active">Guida Operatore</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="m-0">Come Funziona il Gestionale Certificati</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Questa guida ti spiega in modo semplice come usare il programma per creare i Certificati di Analisi. Il sistema è stato progettato per rendere il tuo lavoro più veloce e ridurre la possibilità di errori, guidandoti passo dopo passo.</p>
                        <p>Il processo si divide in 4 semplici fasi:</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- FASE 1 -->
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title">1. Creare un Certificato Provvisorio (la "Bozza")</h3>
            </div>
            <div class="card-body">
                <p>Questa è la prima cosa da fare: creare una bozza del certificato partendo da un lotto di produzione.</p>
                <ol>
                    <li><strong>Vai alla Sezione "Lotti"</strong>: Clicca sul riquadro azzurro <a href="{{ route('elenco_lotti') }}"><strong>Lotti</strong></a>. Qui troverai l'elenco dei lotti prodotti oggi (o in un'altra data che puoi selezionare).</li>
                    <li><strong>Scegli il Lotto</strong>: Cerca il lotto per cui devi creare il certificato. Se accanto al lotto c'è già una spunta verde, significa che la bozza è già stata creata.</li>
                    <li><strong>Crea la Bozza</strong>: Clicca sul pulsante "Crea Certificato Provvisorio".</li>
                </ol>
                <div class="alert alert-light mt-3" role="alert">
                    <strong>Cosa succede in automatico?</strong> Il programma, in base al codice del prodotto, <strong>trova da solo il modello giusto</strong> ("Master") e ne crea una copia su Google Drive. Questa copia è la tua "bozza" ("Provvisorio") e viene pre-compilata con le informazioni base come il numero di lotto e la data di scadenza.
                </div>
            </div>
        </div>

        <!-- FASE 2 -->
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title">2. Compilare la Bozza del Certificato</h3>
            </div>
            <div class="card-body">
                <p>Ora che la bozza esiste, devi inserire i dati delle analisi.</p>
                <ol>
                    <li><strong>Vai alla Sezione "Provvisori"</strong>: Clicca sul riquadro giallo <a href="{{ route('elenco_provvisori') }}"><strong>Provvisori</strong></a>. Qui vedrai l'elenco di tutte le bozze in attesa. Una <strong>barra di avanzamento</strong> ti mostra a che punto sei con la compilazione.</li>
                    <li><strong>Apri la Bozza da Modificare</strong>: Clicca sull'icona della matita per aprire la pagina di modifica.</li>
                    <li><strong>Carica i Campi</strong>: Clicca sul pulsante <strong>"Clicca quì per compilare i tag"</strong>. Il sistema trasformerà tutti i campi da riempire (chiamati "tag") in caselle di testo, menu a tendina o calendari.</li>
                    <li><strong>Inserisci i Dati e Salva</strong>: Riempi le caselle e clicca su <strong>"Salva dati"</strong>. Il programma aggiornerà il documento su Google Drive.</li>
                </ol>
                <div class="alert alert-info mt-3">
                    <h4>Quali sono i TAG?</h4>
                    <p>I tag sono dei segnaposto speciali che il sistema riconosce. Esistono due formati:</p>
                    <ul>
                        <li>Il formato "classico": <code>&lt;nome_tag&gt;</code> (es. <code>&lt;fcont&gt;</code>)</li>
                        <li>Il formato "robusto": <code>$nome_tag$</code> (es. <code>$fcont$</code>)</li>
                    </ul>
                    <p><strong>Consiglio:</strong> Quando crei o modifichi un documento Master, <strong>usa sempre il formato con il dollaro (<code>$nome_tag$</code>)</strong>. È molto più affidabile e previene errori di formattazione che possono verificarsi con Google Docs.</p>
                    <p class="mb-0">Se un tag nel vecchio formato non viene riconosciuto, la soluzione migliore è modificare il Master originale e sostituirlo con il nuovo formato.</p>
                </div>
                <div class="alert alert-warning mt-3">
                    <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Consiglio per la creazione dei TAG</h4>
                    <p>Dato che Google Docs può aggiungere formattazioni "nascoste" che compromettono il riconoscimento dei tag, si consiglia di <strong>scrivere il tag in un editor di testo semplice</strong> (come Blocco Note/Notepad) e poi <strong>copiarlo e incollarlo</strong> nel documento Master. Questo garantisce che il tag sia "pulito" e venga riconosciuto correttamente dal sistema.</p>
                </div>
            </div>
        </div>

        <!-- FASE 3 -->
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title">3. Finalizzare il Certificato e Creare il PDF</h3>
            </div>
            <div class="card-body">
                <p>Quando la compilazione è al 100%, il certificato è pronto per diventare un documento ufficiale in PDF.</p>
                <ol>
                    <li><strong>Segna come "Pronto"</strong>: Dall'elenco dei "Provvisori", puoi cambiare lo stato del certificato.</li>
                    <li><strong>Genera il PDF Finale</strong>: Sempre dall'elenco, troverai i pulsanti per finalizzare il certificato scegliendo se il risultato è <strong>"Idoneo"</strong> o <strong>"Non Idoneo"</strong>. Il programma convertirà la bozza in PDF e la archivierà automaticamente.</li>
                </ol>
            </div>
        </div>

        <!-- FASE 4 -->
        <div class="card">
            <div class="card-header bg-secondary">
                <h3 class="card-title">4. Consultare i Certificati Finali</h3>
            </div>
            <div class="card-body">
                <p><strong>Vai alla Sezione "Definitivi"</strong>: Clicca sul riquadro verde <a href="{{ route('elenco_definitivi_idonei') }}"><strong>Definitivi</strong></a> per trovare l'elenco di tutti i certificati PDF creati e archiviati, pronti per essere consultati o inviati.</p>
            </div>
        </div>

        <!-- INFO TAG -->
        <div class="card">
            <div class="card-header bg-dark">
                <h3 class="card-title">Elenco dei TAG disponibili</h3>
            </div>
            <div class="card-body">
                <p>Questi sono i segnaposto che puoi usare nei tuoi documenti Master.</p>
                <dl class="row">
                    <dt class="col-sm-3"><code>$lt$</code> o <code>&lt;lt&gt;</code></dt>
                    <dd class="col-sm-9">Sostituito con il <strong>numero di lotto</strong>.</dd>
                    <dt class="col-sm-3"><code>$pdate$</code> o <code>&lt;pdate&gt;</code></dt>
                    <dd class="col-sm-9">Sostituito con la <strong>data di produzione</strong>.</dd>
                    <dt class="col-sm-3"><code>$exp$</code> o <code>&lt;exp&gt;</code></dt>
                    <dd class="col-sm-9">Sostituito con la <strong>data di scadenza</strong>.</dd>
                    <dt class="col-sm-3"><code>$fcont$</code> o <code>&lt;fcont&gt;</code></dt>
                    <dd class="col-sm-9">Genera un campo data per l'<strong>approvazione</strong>.</dd>
                    <dt class="col-sm-3"><code>$id$</code> / <code>$nid$</code> o <code>&lt;id&gt;</code> / <code>&lt;nid&gt;</code></dt>
                    <dd class="col-sm-9">Generano campi per le spunte di <strong>idoneità/non idoneità</strong>.</dd>
                    <dt class="col-sm-3"><code>$firma$</code> e <code>$firma_d$</code></dt>
                    <dd class="col-sm-9">Sostituiti con l'<strong>immagine e la didascalia della firma</strong> dell'utente che finalizza.</dd>
                </dl>
            </div>
        </div>
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
@endsection