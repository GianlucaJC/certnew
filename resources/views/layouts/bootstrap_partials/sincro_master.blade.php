<!DOCTYPE html>
<html lang="it">
<head>
    @php($title = "Sincronizzazione Master Locali")
    @include('layouts.bootstrap_partials.head', ['title' => $title])
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    @include('layouts.bootstrap_partials.navbar')

    <main class="container my-5 flex-shrink-0">
        <div class="row mb-4">
            <div class="col">
                <h1 class="m-0">Sincronizzazione Master Locali</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Sincronizzazione Master</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Sincronizza File Master Locali con Database</h5>
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
                            <i class="fas fa-cloud-upload-alt"></i> Crea/Aggiorna Master in Drive di Google
                        </button>
                        <button type="button" class="btn btn-warning mt-2 mt-md-0 d-none" id="excludeFilesBtn" disabled>
                            <i class="fas fa-ban"></i> Escludi Selezionati
                        </button>
                        <button type="button" class="btn btn-danger d-none" id="cancelSyncBtn">
                            <i class="fas fa-times-circle"></i> Annulla
                        </button>
                        <hr>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" value="" id="overwriteCheckbox">
                            <label class="form-check-label" for="overwriteCheckbox">
                                <strong>Sovrascrivi master esistenti</strong>
                            </label>
                            <small class="form-text text-muted">Se selezionato, i file caricati sovrascriveranno i master con lo stesso nome già presenti su Google Drive. Se non selezionato, i file esistenti verranno ignorati.</small>
                        </div>

                        <div class="mt-3">
                            <a href="javascript:void(0)" id="manageExclusionsLink">Gestisci Esclusioni</a>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-secondary btn-sm" id="helpButton"><i class="fas fa-question-circle"></i> Aiuto</button>
                        </div>

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
    </main>

    <!-- Modal per la gestione delle esclusioni -->
    <div class="modal fade" id="exclusionsModal" tabindex="-1" aria-labelledby="exclusionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exclusionsModalLabel">File Master Esclusi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="button" class="btn btn-primary" id="restoreFilesBtn">Ripristina Selezionati</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal di Aiuto -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="helpModalLabel">Guida alla Sincronizzazione Master</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4>Guida alla Sincronizzazione Master</h4>
                    <p>Questa pagina permette di gestire i file "Master" utilizzati per generare i certificati, sincronizzandoli tra la cartella locale del server e Google Drive.</p>

                    <h5><i class="fas fa-list-alt"></i> La Lista "File Aggiunti"</h5>
                    <p>Questa lista è il cuore della procedura. Mostra tutti i file che sono <strong>in attesa di essere caricati su Google Drive</strong>. Un file appare in questa lista se:</p>
                    <ul>
                        <li>È un file completamente nuovo.</li>
                        <li>È una versione aggiornata di un file già esistente su Drive.</li>
                    </ul>
                    <p class="text-info"><strong><i class="fas fa-info-circle"></i> Importante:</strong> Questa lista mostra lo stato attuale dei file pendenti e persiste anche se esci e rientri nella pagina. Un file rimarrà in questa lista finché non verrà caricato su Drive o escluso manualmente.</p>
                    <hr>

                    <h5><i class="fas fa-upload"></i> Carica Master da PC</h5>
                    <p>Usa questo pulsante per caricare uno o più file dal tuo computer. Il sistema li salverà sul server e li aggiungerà automaticamente alla lista dei "File Aggiunti", pronti per il passo successivo.</p>

                    <h5><i class="fas fa-sync-alt"></i> Avvia Sincronizzazione</h5>
                    <p>Questo processo scansiona la cartella dei master sul server e rileva automaticamente i file nuovi o modificati, aggiungendoli alla lista dei "File Aggiunti". È utile se i file vengono aggiunti al server con altri metodi (es. FTP).</p>

                    <h5><i class="fas fa-cloud-upload-alt"></i> Crea/Aggiorna Master in Drive</h5>
                    <p>Dopo aver selezionato i file desiderati dalla lista, usa questo pulsante per caricarli su Google Drive. Se spunti la casella <strong>"Sovrascrivi master esistenti"</strong>, i file con lo stesso nome sostituiranno le versioni precedenti su Drive.</p>

                    <h5><i class="fas fa-ban"></i> Escludi Selezionati</h5>
                    <p>Se non vuoi caricare un file che appare nella lista, selezionalo e clicca qui. Il file verrà rimosso dalla lista dei pendenti e spostato nell'archivio delle esclusioni.</p>

                    <h5><i class="fas fa-tasks"></i> Gestisci Esclusioni</h5>
                    <p>Questo link apre una finestra dove puoi vedere tutti i file che hai escluso in precedenza e, se necessario, ripristinarli per renderli di nuovo disponibili per l'upload.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.bootstrap_partials.footer')

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            let isSyncing = false;
            let isCancelled = false;
            let filesToUpload = [];
            let isUploadingLocal = false;
            let isLocalUploadCancelled = false;
            const helpModal = new bootstrap.Modal(document.getElementById('helpModal'));
            const exclusionsModal = new bootstrap.Modal(document.getElementById('exclusionsModal'));

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

                // Se non ci sono più file, nascondi i contenitori
                if (files.length === 0) {
                    $('#syncResultsContainer').addClass('d-none');
                    $('#uploadToDriveBtn').addClass('d-none');
                    $('#excludeFilesBtn').addClass('d-none');
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
                        } else {
                            $('#syncResultsContainer').addClass('d-none');
                            $('#addedFilesList').empty();
                            $('#uploadToDriveBtn').addClass('d-none');
                            $('#excludeFilesBtn').addClass('d-none');
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
                    // Non è più necessario inviare dati, la scansione è sempre completa.
                    body: JSON.stringify({})
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
                // CORREZIONE: Leggiamo solo i file selezionati tramite checkbox
                const selectedFiles = $('.file-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedFiles.length === 0) {
                    Swal.fire('Attenzione', 'Nessun file selezionato per il caricamento.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Conferma Caricamento',
                    // CORREZIONE: Mostra il numero di file effettivamente selezionati
                    text: `Stai per trasferire ${selectedFiles.length} nuovi master locali su Google Drive. Vuoi procedere?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sì, carica!',
                    cancelButtonText: 'Annulla'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // CORREZIONE: Passiamo i file selezionati alla funzione di upload
                        startUploadProcess(selectedFiles);
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
                            exclusionsModal.show();
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
                        exclusionsModal.hide();
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

            async function startUploadProcess(filesToProcess) {
                $('#uploadToDriveBtn').prop('disabled', true);
                $('#excludeFilesBtn').prop('disabled', true);
                $('#uploadProgressBarContainer').removeClass('d-none');
                $('#uploadProgressBar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
                $('#uploadStatusText').text('Inizio caricamento su Google Drive...');

                // Leggi lo stato della checkbox UNA SOLA VOLTA all'inizio del processo
                const overwrite = $('#overwriteCheckbox').is(':checked');

                const totalFiles = filesToProcess.length;
                let processedCount = 0;
                const csrfToken = $('meta[name="csrf-token"]').attr('content');

                for (const filename of filesToProcess) {
                    const actionText = overwrite ? 'Sovrascrittura di' : 'Caricamento di';
                    $('#uploadStatusText').text(`${actionText} ${filename}... (${processedCount + 1}/${totalFiles})`);

                    try {
                        const uploadResponse = await fetch("{{ route('sincro_master.upload') }}", {
                            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify({ filename: filename, overwrite: overwrite })
                        });
                        const uploadData = await uploadResponse.json();
                        if (!uploadData.success) {
                            // Se il messaggio indica che il file è stato saltato, non è un errore grave
                            if (uploadData.skipped) {
                                console.log(`File saltato: ${filename}`);
                            } else {
                                throw new Error(uploadData.message || `Errore durante l'operazione su ${filename}`);
                            }
                        }

                        processedCount++;
                        const percentage = Math.round((processedCount / totalFiles) * 100);
                        $('#uploadProgressBar').css('width', `${percentage}%`).attr('aria-valuenow', percentage).text(`${percentage}%`);

                    } catch (error) {
                        $('#uploadStatusText').text(`Errore: ${error.message}. Il processo è stato interrotto.`);
                        $('#uploadProgressBar').addClass('bg-danger');
                        Swal.fire('Errore!', `Si è verificato un errore: ${error.message}. La pagina verrà ricaricata.`, 'error').then(() => window.location.reload());
                        return; // Interrompe il ciclo
                    }
                }

                $('#uploadStatusText').text('Caricamento completato con successo! Tutti i file sono stati trasferiti.');
                Swal.fire('Completato!', 'Tutte le operazioni sono state completate con successo.', 'success').then(() => window.location.reload());
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
                startLocalUpload(files); // La logica di check qui non è più necessaria
            });

            async function startLocalUpload(files) {
                $('#uploadLocalFilesBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Caricamento...');
                $('#localUploadProgressBarContainer').removeClass('d-none');
                $('#localUploadProgressBar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
                $('#localUploadStatusText').text('Inizio caricamento dal PC...');

                const totalFiles = files.length;
                let processedCount = 0;
                const csrfToken = $('meta[name="csrf-token"]').attr('content');

                $('#cancelUploadLocalBtn').removeClass('d-none');

                for (const file of files) {
                    if (isLocalUploadCancelled) { // isLocalUploadCancelled è definita globalmente
                        break; // Esce dal ciclo se il caricamento è stato annullato
                    }

                    const formData = new FormData();
                    formData.append('file', file);

                    $('#localUploadStatusText').text(`Caricamento di ${file.name}... (${processedCount + 1}/${totalFiles})`);

                    try {
                        const response = await fetch("{{ route('sincro_master.upload_local') }}", {
                            method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: formData
                        });

                        const data = await response.json();
                        if (!data.success) {
                            throw new Error(data.message || `Errore durante il caricamento di ${file.name}`);
                        }

                        processedCount++;
                        const percentage = Math.round(((processedCount) / totalFiles) * 100);
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

                // Reset e messaggio finale
                resetLocalUploadUI(processedCount, totalFiles);
            }

            $('#cancelUploadLocalBtn').on('click', function() {
                if (isUploadingLocal) {
                    isLocalUploadCancelled = true;
                    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Annullamento...');
                }
            });

            $('#helpButton').on('click', function() {
                helpModal.show();
            });

            function resetLocalUploadUI(processed, total) {
                isUploadingLocal = false;
                isLocalUploadCancelled = false; // Reset cancel flag
                $('#cancelUploadLocalBtn').addClass('d-none');
                $('#uploadLocalFilesBtn').prop('disabled', false).html('<i class="fas fa-upload"></i> Carica Master da PC');
                $('#localMasterFiles').val('');

                if (isLocalUploadCancelled) {
                    $('#localUploadStatusText').text(`Caricamento annullato. ${processed} su ${total} file processati.`);
                    $('#localUploadProgressBar').addClass('bg-warning');
                } else {
                    $('#localUploadStatusText').text('Caricamento dal PC completato. Avvia la sincronizzazione per vedere i nuovi file.');

                    Swal.fire({
                        title: 'Completato!',
                        text: `Operazione completata. ${processed} su ${total} file sono stati caricati sul server. Avvia la sincronizzazione per aggiornare la lista.`,
                        icon: 'success'
                    }).then(() => checkPendingUploads());
                }
            }
        });
    </script>
</body>
</html>