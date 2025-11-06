@extends('all_views.viewmaster.index')

@section('title', 'Sincronizzazione Master Locali')

@section('extra_style')
    <style>
        .progress-container {
            margin-top: 20px;
        }
        .results-table {
            margin-top: 20px;
        }
    </style>
@endsection

@section('content_main')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Sincronizzazione Master Locali</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Sincronizzazione Master</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title">Sincronizza File Master Locali con Database</h5>
                    </div>
                    <div class="card-body">                        
                        <p><b>Passo 1:</b> Carica i file <code>.doc</code> dal tuo PC alla cartella <code>public/doc/master</code> del server.</p>
                        <button type="button" class="btn btn-info" id="uploadLocalFilesBtn">
                            <i class="fas fa-upload"></i> Carica Master da PC
                        </button>
                        <input type="file" id="localMasterFiles" multiple style="display: none;" accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                        <hr>
                        <p><b>Passo 2:</b> Scansiona la cartella <code>public/doc/master</code> per aggiungere i nuovi file al database.</p>
                        <button type="button" class="btn btn-primary" id="startSyncBtn">
                            <i class="fas fa-sync-alt"></i> Avvia Sincronizzazione
                        </button>
                        <button type="button" class="btn btn-success mt-2 mt-md-0 d-none" id="uploadToDriveBtn" disabled>
                            <i class="fas fa-cloud-upload-alt"></i> Crea Master in Drive di Google
                        </button>
                        <button type="button" class="btn btn-danger d-none" id="cancelSyncBtn">
                            <i class="fas fa-times-circle"></i> Annulla
                        </button>

                        <div class="progress-container mt-3 d-none" id="syncProgressBarContainer">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="syncProgressBar">0%</div>
                            </div>
                            <p class="mt-2" id="syncStatusText">In attesa di iniziare...</p>
                        </div>

                        <div class="progress-container mt-3 d-none" id="localUploadProgressBarContainer">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="localUploadProgressBar">0%</div>
                            </div>
                            <p class="mt-2" id="localUploadStatusText">In attesa di caricare i file dal PC...</p>
                        </div>

                        <div class="progress-container mt-3 d-none" id="uploadProgressBarContainer">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="uploadProgressBar">0%</div>
                            </div>
                            <p class="mt-2" id="uploadStatusText">In attesa di iniziare il caricamento...</p>
                        </div>


                        <div class="results-table mt-4 d-none" id="syncResultsContainer">
                            <h5>File Aggiunti:</h5>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome File</th>
                                    </tr>
                                </thead>
                                <tbody id="addedFilesList">
                                    <!-- Results will be appended here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
@endsection

@section('content_plugin')
    <!-- jQuery -->
	<script src="{{ URL::asset('/') }}plugins/jquery/jquery.min.js"></script>
	<!-- Bootstrap 4 -->
	<script src="{{ URL::asset('/') }}plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- AdminLTE App -->
	<script src="{{ URL::asset('/') }}dist/js/adminlte.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            let isSyncing = false;
            let isCancelled = false;
            let filesToUpload = [];
            let isUploadingLocal = false;

            $('#startSyncBtn').on('click', function() {
                if (isSyncing) return;

                isSyncing = true;
                isCancelled = false;
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sincronizzazione in corso...');
                $('#cancelSyncBtn').removeClass('d-none');
                $('#uploadToDriveBtn').addClass('d-none').prop('disabled', true);
                $('#syncProgressBarContainer').removeClass('d-none');
                $('#syncResultsContainer').addClass('d-none');
                $('#addedFilesList').empty();
                $('#syncProgressBar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
                $('#syncStatusText').text('Avvio scansione...');

                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                const syncUrl = "{{ route('sincro_master.sync') }}";

                fetch(syncUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({}) // No specific data needed for this sync
                })
                .then(response => response.json())
                .then(data => {
                    isSyncing = false;
                    $('#startSyncBtn').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Avvia Sincronizzazione');
                    $('#cancelSyncBtn').addClass('d-none');

                    if (data.success) {
                        $('#syncProgressBar').css('width', '100%').attr('aria-valuenow', 100).text('100%');
                        $('#syncStatusText').text(data.message + ` Aggiunti ${data.added_files.length} file.`);
                        filesToUpload = data.added_files;

                        if (data.added_files.length > 0) {
                            $('#syncResultsContainer').removeClass('d-none');
                            data.added_files.forEach(filename => {
                                $('#addedFilesList').append(`<tr><td>${filename}</td></tr>`);
                            });
                            // Abilita il bottone per l'upload solo se ci sono file
                            $('#uploadToDriveBtn').removeClass('d-none').prop('disabled', false);
                        }
                    } else {
                        $('#syncProgressBar').addClass('bg-danger');
                        $('#syncStatusText').text(`Errore: ${data.message}`);
                        alert(`Errore di sincronizzazione: ${data.message}`);
                    }
                })
                .catch(error => {
                    isSyncing = false;
                    $('#startSyncBtn').prop('disabled', false).html('<i class="fas fa-sync-alt></i> Avvia Sincronizzazione');
                    $('#cancelSyncBtn').addClass('d-none');
                    $('#syncProgressBar').addClass('bg-danger');
                    $('#syncStatusText').text(`Errore di rete: ${error.message}`);
                    alert(`Errore di rete durante la sincronizzazione: ${error.message}`);
                });
            });

            $('#cancelSyncBtn').on('click', function() {
                isCancelled = true;
                // In this simple implementation, cancellation just prevents further UI updates
                // and resets the button. The server-side process will complete.
                // For true server-side cancellation, a more complex mechanism (e.g., websockets) would be needed.
                $('#syncStatusText').text('Sincronizzazione annullata (il processo server-side potrebbe continuare).');
                $('#startSyncBtn').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Avvia Sincronizzazione');
                $('#cancelSyncBtn').addClass('d-none');
                isSyncing = false;
            });

            $('#uploadToDriveBtn').on('click', function() {
                if (filesToUpload.length === 0) {
                    alert("Nessun nuovo file da caricare.");
                    return;
                }

                Swal.fire({
                    title: 'Conferma Caricamento',
                    text: `Stai per trasferire ${filesToUpload.length} nuovi master locali su Google Drive. Vuoi procedere?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sì, carica!',
                    cancelButtonText: 'Annulla'
                }).then((result) => {
                    if (result.isConfirmed) {
                        startUploadProcess();
                    }
                });
            });

            async function startUploadProcess() {
                $('#uploadToDriveBtn').prop('disabled', true);
                $('#uploadProgressBarContainer').removeClass('d-none');
                $('#uploadProgressBar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
                $('#uploadStatusText').text('Inizio caricamento su Google Drive...');

                const totalFiles = filesToUpload.length;
                let processedCount = 0;
                const uploadUrl = "{{ route('sincro_master.upload') }}";
                const csrfToken = $('meta[name="csrf-token"]').attr('content');

                for (const filename of filesToUpload) {
                    $('#uploadStatusText').text(`Caricamento di ${filename}... (${processedCount + 1}/${totalFiles})`);
                    try {
                        const response = await fetch(uploadUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ filename: filename })
                        });

                        const data = await response.json();
                        if (!data.success) {
                            throw new Error(data.message || `Errore durante il caricamento di ${filename}`);
                        }

                        processedCount++;
                        const percentage = Math.round((processedCount / totalFiles) * 100);
                        $('#uploadProgressBar').css('width', `${percentage}%`).attr('aria-valuenow', percentage).text(`${percentage}%`);

                    } catch (error) {
                        $('#uploadStatusText').text(`Errore: ${error.message}. Il processo è stato interrotto.`);
                        $('#uploadProgressBar').addClass('bg-danger');
                        // Ricarica la pagina per evitare stati inconsistenti
                        setTimeout(() => window.location.reload(), 3000);
                        return; // Interrompe il ciclo
                    }
                }

                $('#uploadStatusText').text('Caricamento completato con successo! Tutti i file sono stati trasferiti.');
                Swal.fire('Completato!', 'Tutti i nuovi master sono stati caricati su Google Drive.', 'success');
            }

            // Logica per il nuovo pulsante di upload da locale
            $('#uploadLocalFilesBtn').on('click', function() {
                if (isUploadingLocal) return;
                $('#localMasterFiles').click();
            });

            $('#localMasterFiles').on('change', function(event) {
                const files = event.target.files;
                if (files.length === 0) {
                    return;
                }
                startLocalUpload(files);
            });

            async function startLocalUpload(files) {
                isUploadingLocal = true;
                $('#uploadLocalFilesBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Caricamento...');
                $('#localUploadProgressBarContainer').removeClass('d-none');
                $('#localUploadProgressBar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
                $('#localUploadStatusText').text('Inizio caricamento dal PC...');

                const totalFiles = files.length;
                let processedCount = 0;
                const uploadUrl = "{{ route('sincro_master.upload_local') }}";
                const csrfToken = $('meta[name="csrf-token"]').attr('content');

                for (const file of files) {
                    const formData = new FormData();
                    formData.append('file', file);

                    $('#localUploadStatusText').text(`Caricamento di ${file.name}... (${processedCount + 1}/${totalFiles})`);

                    try {
                        const response = await fetch(uploadUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: formData
                        });

                        const data = await response.json();
                        if (!data.success) {
                            throw new Error(data.message || `Errore durante il caricamento di ${file.name}`);
                        }

                        processedCount++;
                        const percentage = Math.round((processedCount / totalFiles) * 100);
                        $('#localUploadProgressBar').css('width', `${percentage}%`).attr('aria-valuenow', percentage).text(`${percentage}%`);

                    } catch (error) {
                        $('#localUploadStatusText').text(`Errore: ${error.message}. Il processo è stato interrotto.`);
                        $('#localUploadProgressBar').addClass('bg-danger');
                        isUploadingLocal = false;
                        $('#uploadLocalFilesBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Carica Master da PC');
                        // Pulisce l'input file per permettere di riselezionare gli stessi file
                        $('#localMasterFiles').val('');
                        return; // Interrompe il ciclo
                    }
                }

                isUploadingLocal = false;
                $('#uploadLocalFilesBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Carica Master da PC');
                $('#localUploadStatusText').text('Caricamento dal PC completato con successo! Ora puoi avviare la sincronizzazione.');
                $('#localMasterFiles').val(''); // Pulisce l'input

                Swal.fire({
                    title: 'Completato!',
                    text: `Tutti i ${totalFiles} file sono stati caricati sul server. Ora puoi procedere con la sincronizzazione per registrarli nel database.`,
                    icon: 'success'
                });
            }
        });
    </script>
@endsection