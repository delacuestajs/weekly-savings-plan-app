<div id="toast" class="fixed bottom-4 right-4 z-50 hidden max-w-sm">
    <div id="toastContent" class="flex items-center p-4 rounded-lg shadow-lg text-white">
        <span id="toastIcon" class="mr-3 text-xl"></span>
        <span id="toastMessage" class="flex-1 text-sm font-medium"></span>
        <button onclick="hideToast()" class="ml-3 text-white opacity-70 hover:opacity-100 text-lg leading-none">&times;</button>
    </div>
</div>

<div id="attachmentModal" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center z-40 p-4" onclick="closeModal(event)">
    <div class="bg-white rounded-lg max-w-4xl max-h-[90vh] w-full overflow-hidden relative">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-800">Attachment</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
        </div>
        <div id="modalContent" class="p-4 overflow-auto max-h-[70vh] flex items-center justify-center">
        </div>
    </div>
</div>

<div id="zoomModal" class="fixed inset-0 bg-black bg-opacity-90 hidden items-center justify-center z-50 p-4" onclick="closeZoomModal(event)">
    <div class="relative max-w-5xl w-full">
        <div class="flex justify-between items-center mb-4">
            <h3 id="zoomTitle" class="text-lg font-semibold text-white">Zoom</h3>
            <div class="flex items-center gap-2">
                <button onclick="zoomOut()" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-lg transition" title="Zoom Out">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </button>
                <span id="zoomLevel" class="text-white text-sm min-w-[50px] text-center">100%</span>
                <button onclick="zoomIn()" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-lg transition" title="Zoom In">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
                <button onclick="resetZoom()" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-lg transition" title="Reset">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
                <button onclick="closeZoomModal()" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-lg transition ml-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
        <div class="overflow-auto max-h-[80vh] flex items-center justify-center">
            <img id="zoomImage" src="" alt="" class="max-w-none transition-transform duration-200">
        </div>
    </div>
</div>

<script>
let currentZoom = 1;
const zoomStep = 0.25;
const minZoom = 0.25;
const maxZoom = 4;

function showToast(type, message) {
    const toast = document.getElementById('toast');
    const toastContent = document.getElementById('toastContent');
    const toastIcon = document.getElementById('toastIcon');
    const toastMessage = document.getElementById('toastMessage');
    
    toastContent.className = 'flex items-center p-4 rounded-lg shadow-lg text-white';
    
    if (type === 'success') {
        toastContent.classList.add('bg-green-500');
        toastIcon.textContent = '✓';
    } else if (type === 'error') {
        toastContent.classList.add('bg-red-500');
        toastIcon.textContent = '✕';
    } else if (type === 'warning') {
        toastContent.classList.add('bg-amber-500');
        toastIcon.textContent = '⚠';
    } else {
        toastContent.classList.add('bg-blue-500');
        toastIcon.textContent = 'ℹ';
    }
    
    toastMessage.textContent = message;
    toast.classList.remove('hidden');
    
    setTimeout(function() {
        hideToast();
    }, 4000);
}

function hideToast() {
    document.getElementById('toast').classList.add('hidden');
}

function openModal(url, title, type) {
    const modal = document.getElementById('attachmentModal');
    const content = document.getElementById('modalContent');
    const modalTitle = document.getElementById('modalTitle');
    
    modalTitle.textContent = title || 'Attachment';
    content.innerHTML = '';
    
    if (type === 'image') {
        const img = document.createElement('img');
        img.src = url;
        img.alt = title;
        img.className = 'max-w-full h-auto max-h-[70vh] rounded-lg';
        content.appendChild(img);
    } else if (type === 'pdf') {
        const iframe = document.createElement('iframe');
        iframe.src = url;
        iframe.className = 'w-full h-[70vh] rounded-lg border-0';
        content.appendChild(iframe);
    } else {
        content.innerHTML = '<div class="text-center py-8"><p class="text-gray-600 mb-4">This file type cannot be previewed.</p><a href="' + url + '" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">Download File</a></div>';
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    const modal = document.getElementById('attachmentModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
    document.getElementById('modalContent').innerHTML = '';
}

function openZoomModal(url, title) {
    const modal = document.getElementById('zoomModal');
    const zoomImage = document.getElementById('zoomImage');
    const zoomTitle = document.getElementById('zoomTitle');
    
    zoomTitle.textContent = title || 'Zoom';
    zoomImage.src = url;
    zoomImage.alt = title;
    currentZoom = 1;
    updateZoom();
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeZoomModal(event) {
    if (event && event.target !== event.currentTarget) return;
    const modal = document.getElementById('zoomModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

function zoomIn() {
    if (currentZoom < maxZoom) {
        currentZoom = Math.min(currentZoom + zoomStep, maxZoom);
        updateZoom();
    }
}

function zoomOut() {
    if (currentZoom > minZoom) {
        currentZoom = Math.max(currentZoom - zoomStep, minZoom);
        updateZoom();
    }
}

function resetZoom() {
    currentZoom = 1;
    updateZoom();
}

function updateZoom() {
    const zoomImage = document.getElementById('zoomImage');
    const zoomLevel = document.getElementById('zoomLevel');
    zoomImage.style.transform = 'scale(' + currentZoom + ')';
    zoomLevel.textContent = Math.round(currentZoom * 100) + '%';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeZoomModal();
    }
    if (document.getElementById('zoomModal').classList.contains('hidden') === false) {
        if (e.key === '+' || e.key === '=') zoomIn();
        if (e.key === '-') zoomOut();
        if (e.key === '0') resetZoom();
    }
});

(function() {
    const params = new URLSearchParams(window.location.search);
    const toastType = params.get('toast');
    const toastMessage = params.get('message');
    
    if (toastType && toastMessage) {
        showToast(toastType, decodeURIComponent(toastMessage));
        params.delete('toast');
        params.delete('message');
        const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.replaceState({}, '', newUrl);
    }
})();

function initUploadProgress(inputId, progressId) {
    const input = document.getElementById(inputId);
    const progressContainer = document.getElementById(progressId);
    
    if (!input || !progressContainer) return;
    
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            showToast('error', 'File too large. Maximum size is 5MB.');
            input.value = '';
            return;
        }
        
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            showToast('error', 'Invalid file type. Allowed: JPG, PNG, GIF, WebP');
            input.value = '';
            return;
        }
        
        progressContainer.classList.remove('hidden');
        const progressBar = progressContainer.querySelector('.progress-bar');
        const progressText = progressContainer.querySelector('.progress-text');
        
        progressBar.style.width = '0%';
        progressText.textContent = '0%';
        
        let progress = 0;
        const interval = setInterval(function() {
            progress += Math.random() * 15;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                setTimeout(function() {
                    progressContainer.classList.add('hidden');
                    showToast('success', 'File selected: ' + file.name);
                }, 300);
            }
            progressBar.style.width = progress + '%';
            progressText.textContent = Math.round(progress) + '%';
        }, 100);
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(inputId + '_preview');
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
        };
        reader.readAsDataURL(file);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initUploadProgress('picture', 'picture_progress');
    initUploadProgress('attachment', 'attachment_progress');
    
    var forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        if (!form.hasAttribute('novalidate')) {
            form.setAttribute('novalidate', '');
        }
        
        form.addEventListener('submit', function(e) {
            var requiredFields = form.querySelectorAll('[required]');
            var firstInvalid = null;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    e.preventDefault();
                    field.style.borderColor = '#ef4444';
                    if (!firstInvalid) {
                        firstInvalid = field;
                    }
                    field.setCustomValidity('<?= Locale::get('field_required') ?>');
                    field.reportValidity();
                } else {
                    field.style.borderColor = '';
                    field.setCustomValidity('');
                }
            });
            
            if (firstInvalid) {
                firstInvalid.focus();
                return false;
            }
        });
    });
});
</script>

    </div>
</div>

</body>
</html>
