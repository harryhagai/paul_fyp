$(document).ready(function () {
    const pageRoot = document.querySelector('.product-media-page');
    if (!pageRoot) return;

    const productId = pageRoot.dataset.productId;
    const uploadUrl = pageRoot.dataset.uploadUrl;
    const primaryUrlTemplate = pageRoot.dataset.primaryUrlTemplate;
    const deleteUrlTemplate = pageRoot.dataset.deleteUrlTemplate;
    const uploadMaxBytes = parseInt(pageRoot.dataset.uploadMaxBytes || '0', 10);

    const rootStyle = getComputedStyle(document.documentElement);
    const chunkSize = 1024 * 1024; // 1MB chunks

    let selectedFiles = [];

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function showToast(type, message, title = '') {
        const container = document.getElementById('mediaToastContainer');
        if (!container) return;

        const tone = type === 'success'
            ? 'text-bg-success'
            : (type === 'warning' ? 'text-bg-warning' : 'text-bg-danger');
        const heading = title || (type === 'success' ? 'Success' : (type === 'warning' ? 'Notice' : 'Error'));
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center ${tone} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong class="me-2">${heading}:</strong>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        container.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 2800 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }

    $('#browseBtn').on('click', function () {
        $('#mediaInput').trigger('click');
    });

    $('#mediaInput').on('change', function (e) {
        const files = Array.from(e.target.files || []);
        handleFiles(files);
        updateSelectedCount();
    });

    const uploadZone = document.getElementById('mediaUploadZone');
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName) => {
        uploadZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach((eventName) => {
        uploadZone.addEventListener(eventName, () => uploadZone.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        uploadZone.addEventListener(eventName, () => uploadZone.classList.remove('dragover'), false);
    });

    uploadZone.addEventListener('drop', function (e) {
        const files = Array.from((e.dataTransfer && e.dataTransfer.files) || []);
        handleFiles(files);
        updateSelectedCount();
    }, false);

    function handleFiles(files) {
        selectedFiles = files.filter((file) => {
            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'video/mp4',
                'video/avi',
                'video/x-msvideo',
                'video/quicktime',
                'video/webm',
            ];
            const maxSize = uploadMaxBytes > 0 ? uploadMaxBytes : (10 * 1024 * 1024);

            if (!allowedTypes.includes(file.type)) {
                showToast('error', `File type not supported: ${file.name}`);
                return false;
            }

            if (file.size > maxSize) {
                const maxMb = Math.floor(maxSize / (1024 * 1024));
                showToast('error', `File too large: ${file.name} (max ${maxMb}MB)`);
                return false;
            }

            return true;
        });
    }

    function updateSelectedCount() {
        if (selectedFiles.length > 0) {
            $('#uploadSelectedBtn').show();
            $('#selectedCount').text(selectedFiles.length);
        } else {
            $('#uploadSelectedBtn').hide();
        }
    }

    function setProgress(percent, label) {
        const safePercent = Math.max(0, Math.min(100, Math.round(percent)));
        $('#uploadProgressWrap').removeClass('d-none');
        $('#uploadProgressBar').css('width', `${safePercent}%`);
        $('#uploadProgressPercent').text(`${safePercent}%`);
        if (label) $('#uploadProgressLabel').text(label);
    }

    function resetProgress() {
        $('#uploadProgressWrap').addClass('d-none');
        $('#uploadProgressBar').css('width', '0%');
        $('#uploadProgressPercent').text('0%');
        $('#uploadProgressLabel').text('Uploading...');
    }

    function confirmPrimaryMedia() {
        return Swal.fire({
            icon: 'question',
            title: 'Set as primary?',
            text: 'This media will be shown as the main media for this product.',
            showCancelButton: true,
            showCloseButton: true,
            buttonsStyling: false,
            confirmButtonText: '<i class="bi bi-star-fill me-1"></i>Set Primary',
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
            customClass: {
                popup: 'media-confirm-popup',
                icon: 'media-confirm-icon',
                title: 'media-confirm-title',
                htmlContainer: 'media-confirm-text',
                actions: 'media-confirm-actions',
                confirmButton: 'media-confirm-button btn',
                cancelButton: 'media-cancel-button btn'
            }
        });
    }

    $('#uploadSelectedBtn').on('click', function () {
        uploadFiles();
    });

    function uploadFiles() {
        if (selectedFiles.length === 0) {
            showToast('warning', 'No files selected');
            return;
        }

        (async () => {
            try {
                let uploadedBytesTotal = 0;
                const grandTotalBytes = selectedFiles.reduce((sum, file) => sum + file.size, 0);

                for (let fileIndex = 0; fileIndex < selectedFiles.length; fileIndex++) {
                    const file = selectedFiles[fileIndex];
                    await uploadSingleFile(file, (fileUploadedBytes) => {
                        const bytesBeforeCurrentFile = selectedFiles
                            .slice(0, fileIndex)
                            .reduce((sum, f) => sum + f.size, 0);
                        uploadedBytesTotal = bytesBeforeCurrentFile + fileUploadedBytes;
                        const overallPct = grandTotalBytes > 0 ? (uploadedBytesTotal / grandTotalBytes) * 100 : 0;
                        setProgress(overallPct, `Uploading ${file.name} (${fileIndex + 1}/${selectedFiles.length})`);
                    });
                }

                setProgress(100, 'Upload complete');
                showToast('success', 'All files uploaded successfully!');
                selectedFiles = [];
                updateSelectedCount();
                setTimeout(() => location.reload(), 500);
            } catch (error) {
                showToast('error', `Upload failed: ${error}`);
                resetProgress();
            }
        })();
    }

    function uploadSingleFile(file, onProgress) {
        return new Promise((resolve, reject) => {
            const totalChunks = Math.max(1, Math.ceil(file.size / chunkSize));
            const uploadId = `${Date.now()}_${Math.random().toString(36).slice(2, 10)}_${file.name.replace(/\W+/g, '_')}`;
            let currentChunk = 0;
            let uploadedBytes = 0;

            const sendNextChunk = () => {
                const start = currentChunk * chunkSize;
                const end = Math.min(file.size, start + chunkSize);
                const chunkBlob = file.slice(start, end);

                const formData = new FormData();
                formData.append('file', chunkBlob, file.name);
                formData.append('type', file.type.startsWith('image/') ? 'image' : 'video');
                formData.append('upload_id', uploadId);
                formData.append('chunk_index', String(currentChunk));
                formData.append('total_chunks', String(totalChunks));
                formData.append('original_name', file.name);
                formData.append('mime_type', file.type || '');

                $.ajax({
                    url: uploadUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (!response.success) {
                            reject(response.message || 'Upload failed');
                            return;
                        }

                        uploadedBytes = end;
                        if (typeof onProgress === 'function') onProgress(uploadedBytes);
                        currentChunk += 1;

                        if (currentChunk < totalChunks) {
                            sendNextChunk();
                            return;
                        }

                        resolve(response);
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON && xhr.responseJSON.errors
                            ? Object.values(xhr.responseJSON.errors).flat().join(' ')
                            : null;
                        const message = errors || (xhr.responseJSON && xhr.responseJSON.message) || `Upload error (${xhr.status})`;
                        console.error('Upload failed response:', xhr.responseJSON || xhr.responseText || xhr.statusText);
                        reject(message);
                    }
                });
            };

            sendNextChunk();
        });
    }

    window.setAsPrimary = function (mediaId) {
        confirmPrimaryMedia().then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: primaryUrlTemplate.replace('__MEDIA_ID__', mediaId),
                type: 'PATCH',
                success: function (response) {
                    if (response.success) {
                        showToast('success', 'Media set as primary!');
                        setTimeout(() => location.reload(), 700);
                        return;
                    }
                    showToast('error', response.message);
                },
                error: function (xhr) {
                    showToast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to set as primary');
                }
            });
        });
    };

    window.deleteMedia = function (mediaId) {
        $.ajax({
            url: deleteUrlTemplate.replace('__MEDIA_ID__', mediaId),
            type: 'DELETE',
            success: function (response) {
                if (response.success) {
                    showToast('success', 'Media deleted successfully!');
                    setTimeout(() => location.reload(), 500);
                    return;
                }
                showToast('error', response.message);
            },
            error: function (xhr) {
                showToast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed');
            }
        });
    };

    window.openMediaModal = function (src, type) {
        let content = '';
        if (type === 'image') {
            content = `<img src="${src}" class="img-fluid" style="max-height: 80vh;">`;
        } else {
            content = `<video controls class="w-100" style="max-height: 80vh;">
                <source src="${src}">
                Your browser does not support the video tag.
            </video>`;
        }
        $('#mediaModalContent').html(content);
        $('#mediaModal').modal('show');
    };

    $('#gridView').on('click', function () {
        $('#gridView').addClass('active');
        $('#listView').removeClass('active');
        $('.media-grid').removeClass('media-list').addClass('row');
    });

    $('#listView').on('click', function () {
        $('#listView').addClass('active');
        $('#gridView').removeClass('active');
        $('.media-grid').removeClass('row').addClass('media-list');
    });
});
