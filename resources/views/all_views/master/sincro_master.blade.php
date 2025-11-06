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
                        <button type="button" class="btn btn-danger d-none" id="cancelUploadLocalBtn">
                            <i class="fas fa-times-circle"></i> Annulla Caricamento
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
                        <button type="button" class="btn btn-warning mt-2 mt-md-0 d-none" id="excludeFilesBtn" disabled>
                            <i class="fas fa-ban"></i> Escludi Selezionati
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
                            <div class="mb-2">
                                <a href="javascript:void(0)" id="manageExclusionsLink">Gestisci Esclusioni</a>
                            </div>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAllCheckbox"> Nome File</th>
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

    <!-- Modal per la gestione delle esclusioni -->
    <div class="modal fade" id="exclusionsModal" tabindex="-1" role="dialog" aria-labelledby="exclusionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exclusionsModalLabel">File Master Esclusi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Questi file sono stati esclusi e non verranno caricati su Google Drive. Selezionali e clicca su "Ripristina" per renderli nuovamente disponibili.</p>
                    <table class="table table-bordered">
                        <thead>
                            <tr><th><input type="checkbox" id="selectAllExcludedCheckbox"> Nome File</th></tr>
                        </thead>
                        <tbody id="excludedFilesList"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
                    <button type="button" class="btn btn-primary" id="restoreFilesBtn">Ripristina Selezionati</button>
                </div>
            </div>
        </div>
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
            let isLocalUploadCancelled = false;

            // Funzione per popolare la UI con i file
            function populateUiWithFiles(files) {
                filesToUpload = files;
                $('#addedFilesList').empty();

                if (files.length > 0) {
                    $('#syncResultsContainer').removeClass('d-none');
                    $('#selectAllCheckbox').prop('checked', false);
                    files.forEach(filename => {
                        $('#addedFilesList').append(`<tr><td><input type="checkbox" class="file-checkbox" value="${filename}"> ${filename}</td></tr>`);
                    });
                    $('#uploadToDriveBtn').removeClass('d-none').prop('disabled', false);
                    $('#excludeFilesBtn').removeClass('d-none').prop('disabled', false);
                    $('#syncStatusText').text(`Trovati ${files.length} nuovi file locali pronti per essere caricati su Google Drive.`);
                } else {
                    $('#syncStatusText').text("Nessun nuovo file locale da caricare. Tutti i master sono sincronizzati con il database.");
                }
            }

            // Controlla i file pendenti al caricamento della pagina
            function checkPendingUploads() {
                const checkUrl = "{{ route('sincro_master.check_pending') }}";
                fetch(checkUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.pending_files.length > 0) {
                            $('#syncProgressBarContainer').removeClass('d-none');
                            populateUiWithFiles(data.pending_files);
                        }
                    })
                    .catch(error => console.error('Errore nel controllo dei file pendenti:', error));
            }

            checkPendingUploads(); // Esegui al caricamento

            $('#startSyncBtn').on('click', function() {
                if (isSyncing) return;

                isSyncing = true;
                isCancelled = false;
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sincronizzazione in corso...');
                $('#cancelSyncBtn').removeClass('d-none');
                $('#excludeFilesBtn').addClass('d-none').prop('disabled', true);
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
                        populateUiWithFiles(data.added_files);
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

            $('#excludeFilesBtn').on('click', function() {
                const selectedFiles = $('.file-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedFiles.length === 0) {
                    Swal.fire('Attenzione', 'Nessun file selezionato da escludere.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Conferma Esclusione',
                    text: `Sei sicuro di voler escludere ${selectedFiles.length} file dal caricamento su Drive?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sì, escludi',
                    cancelButtonText: 'Annulla'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const excludeUrl = "{{ route('sincro_master.exclude') }}";
                        const csrfToken = $('meta[name="csrf-token"]').attr('content');

                        fetch(excludeUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify({ filenames: selectedFiles })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Esclusi!', 'I file selezionati sono stati esclusi.', 'success');
                                checkPendingUploads(); // Ricarica la lista dei file pendenti
                                $('#syncResultsContainer').addClass('d-none');
                                $('#uploadToDriveBtn').addClass('d-none');
                                $('#excludeFilesBtn').addClass('d-none');
                            } else {
                                Swal.fire('Errore', data.message || 'Si è verificato un errore.', 'error');
                            }
                        }).catch(error => Swal.fire('Errore', `Errore di rete: ${error.message}`, 'error'));
                    }
                });
            });

            $('#manageExclusionsLink').on('click', function() {
                const getExcludedUrl = "{{ route('sincro_master.get_excluded') }}";
                fetch(getExcludedUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const list = $('#excludedFilesList');
                            list.empty();
                            $('#selectAllExcludedCheckbox').prop('checked', false);
                            if (data.excluded_files.length > 0) {
                                data.excluded_files.forEach(filename => {
                                    list.append(`<tr><td><input type="checkbox" class="excluded-file-checkbox" value="${filename}"> ${filename}</td></tr>`);
                                });
                            } else {
                                list.append('<tr><td>Nessun file escluso.</td></tr>');
                            }
                            $('#exclusionsModal').modal('show');
                        } else {
                            Swal.fire('Errore', data.message || 'Impossibile caricare i file esclusi.', 'error');
                        }
                    }).catch(error => Swal.fire('Errore', `Errore di rete: ${error.message}`, 'error'));
            });

            $('#restoreFilesBtn').on('click', function() {
                const selectedToRestore = $('.excluded-file-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedToRestore.length === 0) {
                    Swal.fire('Attenzione', 'Nessun file selezionato da ripristinare.', 'warning');
                    return;
                }

                const restoreUrl = "{{ route('sincro_master.restore') }}";
                const csrfToken = $('meta[name="csrf-token"]').attr('content');

                fetch(restoreUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ filenames: selectedToRestore })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Ripristinati!', 'I file selezionati sono di nuovo disponibili per il caricamento.', 'success');
                        $('#exclusionsModal').modal('hide');
                        checkPendingUploads(); // Ricarica la lista dei file pendenti
                    } else {
                        Swal.fire('Errore', data.message || 'Si è verificato un errore.', 'error');
                    }
                }).catch(error => Swal.fire('Errore', `Errore di rete: ${error.message}`, 'error'));
            });

            $('#selectAllCheckbox').on('click', function() {
                $('.file-checkbox').prop('checked', $(this).prop('checked'));
            });
            $('#selectAllExcludedCheckbox').on('click', function() {
                $('.excluded-file-checkbox').prop('checked', $(this).prop('checked'));
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
                isLocalUploadCancelled = false;
                $('#uploadLocalFilesBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Caricamento...');
                $('#localUploadProgressBarContainer').removeClass('d-none');
                $('#localUploadProgressBar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
                $('#localUploadStatusText').text('Inizio caricamento dal PC...');

                const totalFiles = files.length;
                let processedCount = 0;
                const uploadUrl = "{{ route('sincro_master.upload_local') }}";
                const csrfToken = $('meta[name="csrf-token"]').attr('content');

                $('#cancelUploadLocalBtn').removeClass('d-none');

                for (const file of files) {
                    if (isLocalUploadCancelled) {
                        break; // Esce dal ciclo se il caricamento è stato annullato
                    }

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
                        $('#cancelUploadLocalBtn').addClass('d-none');
                        // Pulisce l'input file per permettere di riselezionare gli stessi file
                        $('#localMasterFiles').val('');
                        return; // Interrompe il ciclo
                    }
                }

                isUploadingLocal = false;
                $('#cancelUploadLocalBtn').addClass('d-none');
                $('#uploadLocalFilesBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Carica Master da PC');
                $('#localMasterFiles').val(''); // Pulisce l'input

                if (isLocalUploadCancelled) {
                    $('#localUploadStatusText').text(`Caricamento annullato dall'utente. ${processedCount} su ${totalFiles} file sono stati caricati.`);
                    $('#localUploadProgressBar').addClass('bg-warning');
                } else {
                    $('#localUploadStatusText').text('Caricamento dal PC completato con successo! Ora puoi avviare la sincronizzazione.');
                    Swal.fire({
                        title: 'Completato!',
                        text: `Tutti i ${totalFiles} file sono stati caricati sul server. Ora puoi procedere con la sincronizzazione per registrarli nel database.`,
                        icon: 'success'
                    });
                }
            }

            $('#cancelUploadLocalBtn').on('click', function() {
                if (isUploadingLocal) {
                    isLocalUploadCancelled = true;
                    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Annullamento...');
                }
            });

        });
    </script>
@endsection