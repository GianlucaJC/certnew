@extends('all_views.viewmaster.index')

@section('title', 'Benvenuto')

@section('content_main')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Benvenuto nel Gestionale Certificati</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Missione del Programma</li>
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
                        <h5 class="m-0">Riepilogo del Flusso di Lavoro</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">Questa applicazione è progettata per automatizzare e semplificare il processo di creazione dei Certificati di Analisi (CoA).</h6>

                        <p class="card-text mt-3">Il processo si articola nei seguenti passaggi:</p>
                        <ol>
                            <li><strong>Selezione dei Lotti di Produzione:</strong> Il sistema recupera i lotti prodotti in una data specifica (di default, oggi).</li>
                            <li><strong>Ricerca del Master:</strong> Per ogni lotto, il sistema identifica il documento "Master" corretto basandosi sul codice prodotto e su un insieme di regole predefinite.</li>
                            <li><strong>Creazione del Certificato Provvisorio:</strong> Il documento Master (un file Google Doc) viene clonato per creare un certificato "Provvisorio" unico per quel lotto.</li>
                            <li><strong>Compilazione Dati:</strong> L'utente compila il certificato provvisorio inserendo i dati richiesti nei campi specifici (identificati da tag come <code>&lt;TAG&gt;</code> o <code>[[TAG]]</code>) presenti nel documento.</li>
                            <li><strong>Generazione del PDF Finale:</strong> Una volta completata la compilazione, l'utente trasforma il certificato provvisorio in un documento PDF finale, classificandolo come "Idoneo" o "Non Idoneo".</li>
                        </ol>
                        <div class="alert alert-warning mt-2">
                            <h5 class="alert-heading mb-0"><i class="fas fa-exclamation-triangle"></i> Consiglio per i TAG</h5>
                            <p class="mb-0">Quando si crea o modifica un documento Master, si consiglia di <strong>scrivere i tag (es. <code>[[lt]]</code>) in un editor di testo semplice</strong> (come Notepad) e poi incollarli nel documento. Questo previene errori di formattazione di Google Docs.</p>
                        </div>
                        <hr>
                        
                        <div class="row mt-3 d-flex align-items-stretch">
                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-info">
                                  <div class="inner">
                                    <h3>Lotti</h3>
                                    <p>Crea Provvisori</p>
                                  </div>
                                  <div class="icon">
                                    <i class="fas fa-industry"></i>
                                  </div>
                                  <a href="{{ route('elenco_lotti') }}" class="small-box-footer">Vai <i class="fas fa-arrow-circle-right"></i></a>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-warning">
                                  <div class="inner">
                                    <h3>Provvisori</h3>
                                    <p>Compila Certificati</p>
                                  </div>
                                  <div class="icon">
                                    <i class="fas fa-edit"></i>
                                  </div>
                                  <a href="{{ route('elenco_provvisori') }}" class="small-box-footer">Vai <i class="fas fa-arrow-circle-right"></i></a>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-success">
                                  <div class="inner">
                                    <h3>Definitivi</h3>
                                    <p>Idonei e Non Idonei</p>
                                  </div>
                                  <div class="icon">
                                    <i class="fas fa-check-double"></i>
                                  </div>
                                  <a href="{{ route('elenco_definitivi_idonei') }}" class="small-box-footer">Vai <i class="fas fa-arrow-circle-right"></i></a>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-secondary">
                                  <div class="inner">
                                    <h3>Master</h3>
                                    <p>Gestione Documenti</p>
                                  </div>
                                  <div class="icon">
                                    <i class="fas fa-cogs"></i>
                                  </div>
                                  <a href="{{ route('elenco_master') }}" class="small-box-footer">Vai <i class="fas fa-arrow-circle-right"></i></a>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <!-- small box -->
                                <div class="small-box bg-primary h-100">
                                  <div class="inner">
                                    <h3>Guida</h3>
                                    <p>Manuale Operatore</p>
                                  </div>
                                  <div class="icon">
                                    <i class="fas fa-question-circle"></i>
                                  </div>
                                  <a href="{{ route('guida_operatore') }}" class="small-box-footer">Consulta <i class="fas fa-arrow-circle-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
@endsection