<!DOCTYPE html>
<html lang="it">
<head>
    @php($title = "HomePage")
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><path d='M6 2h15l5 5v21H6V2zm14 1v4h4' fill='%23fff' stroke='%23000' stroke-width='2' stroke-linejoin='round'/><path d='m12 18 4 4 8-8' fill='none' stroke='%2304a24c' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/></svg>" type="image/svg+xml">    
    @include('layouts.bootstrap_partials.head', ['title' => $title])
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        /* Stili specifici della pagina */
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .hero-section {
            background: linear-gradient(45deg, #007bff, #6610f2);
            color: white;
            padding: 3rem 0;
            text-align: center;
        }
        .hero-section h1 {
            font-weight: 700;
            font-size: 3.5rem;
        }
        .hero-section p {
            font-size: 1.25rem;
            font-weight: 300;
        }
        .workflow-card {
            transition: transform .2s ease-in-out, box-shadow .2s ease-in-out;
            cursor: pointer;
            border: 0;
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        }
        .workflow-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
        }
        .workflow-card .card-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .workflow-card .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .workflow-card .card-title {
            font-weight: 600;
        }
        .workflow-card.disabled {
            opacity: 0.65;
            pointer-events: none;
            cursor: not-allowed;
        }
        .workflow-card.disabled:hover {
            transform: none;
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        }
        .footer {
            background-color: #343a40;
            color: #f8f9fa;
            padding: 2rem 0;
            width: 100%;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100" style="padding-bottom: 100px;">

    @include('layouts.bootstrap_partials.navbar')
    <!--
    <header class="hero-section">
        <div class="container">
            <h1 class="display-4">Benvenuto nel Gestionale CoA</h1>
            <p class="lead">La soluzione per automatizzare e semplificare la creazione dei Certificati di Analisi.</p>
        </div>
    </header>
    !-->

    <main class="container my-5 flex-shrink-0">
        <div class="text-center mb-5">
            <h2>Flusso di Lavoro Principale</h2>
            <p class="text-muted">Segui questi passaggi per generare i tuoi certificati in modo efficiente.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <a @if(config('menu.full_menu_enabled', true)) href="{{ route('elenco_lotti') }}" @endif class="text-decoration-none">
                    <div class="card h-100 workflow-card @if(!config('menu.full_menu_enabled', true)) disabled @endif">
                        <div class="card-body">
                            <div class="icon text-primary"><i class="fas fa-industry"></i></div>
                            <h5 class="card-title">1. Lotti</h5>
                            <p class="card-text">Crea i certificati provvisori partendo dai lotti di produzione.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a @if(config('menu.full_menu_enabled', true)) href="{{ route('elenco_provvisori') }}" @endif class="text-decoration-none">
                    <div class="card h-100 workflow-card @if(!config('menu.full_menu_enabled', true)) disabled @endif">
                        <div class="card-body">
                            <div class="icon text-warning"><i class="fas fa-edit"></i></div>
                            <h5 class="card-title">2. Provvisori</h5>
                            <p class="card-text">Compila i dati di analisi nei certificati in lavorazione.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <div class="card h-100 workflow-card @if(!config('menu.full_menu_enabled', true)) disabled @endif">
                    <div class="card-body d-flex flex-column">
                        <div class="icon text-success"><i class="fas fa-check-double"></i></div>
                        <h5 class="card-title">3. Definitivi</h5>
                        <p class="card-text">Consulta l'archivio dei certificati finali.</p>
                        <div class="mt-auto">
                            <a @if(config('menu.full_menu_enabled', true)) href="{{ route('elenco_definitivi_idonei', ['stato' => 'idoneo']) }}" @endif class="btn btn-success btn-sm me-2">
                                <i class="fas fa-check-circle"></i> Idonei
                            </a>
                            <a @if(config('menu.full_menu_enabled', true)) href="{{ route('elenco_definitivi_non_idonei', ['stato' => 'non idoneo']) }}" @endif class="btn btn-danger btn-sm">
                                <i class="fas fa-times-circle"></i> Non Idonei
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mt-1">
            <div class="col-md-4">
                <a href="{{ route('elenco_master') }}" class="text-decoration-none ">
                    <div class="card h-100 workflow-card">
                        <div class="card-body">
                            <div class="icon text-secondary"><i class="fas fa-cogs"></i></div>
                            <h5 class="card-title">Archivio Master</h5>
                            <p class="card-text">Gestisci i modelli base per la creazione dei certificati.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('sincro_master') }}" class="text-decoration-none">
                    <div class="card h-100 workflow-card">
                        <div class="card-body">
                            <div class="icon text-info"><i class="fas fa-sync-alt"></i></div>
                            <h5 class="card-title">Check Master</h5>
                            <p class="card-text">Sincronizza e aggiorna i dati anagrafici delle materie prime.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('guida_operatore') }}" class="text-decoration-none">
                    <div class="card h-100 workflow-card">
                        <div class="card-body">
                            <div class="icon text-dark"><i class="fas fa-book-open"></i></div>
                            <h5 class="card-title">Guida Operatore</h5>
                            <p class="card-text">Consulta la guida per l'utilizzo del gestionale.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading"><i class="fas fa-lightbulb"></i> Consiglio Utile</h4>
                    <p>Quando crei o modifichi un documento Master, si consiglia di <strong>scrivere i tag (es. <code>[[lt]]</code>) in un editor di testo semplice</strong> (come Blocco Note) e poi incollarli nel documento. Questo previene errori di formattazione nascosti di Google Docs.</p>
                </div>
            </div>
        </div>

    </main>

    <div class="mt-auto fixed-bottom">
        @include('layouts.bootstrap_partials.footer')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>