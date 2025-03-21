// CKEditor Configuration
function initCKEditor(selector = '.ckeditor') {
    const editors = document.querySelectorAll(selector);
    editors.forEach(editor => {
        ClassicEditor
            .create(editor, {
                toolbar: {
                    items: [
                        'heading',
                        '|',
                        'bold',
                        'italic',
                        'link',
                        'bulletedList',
                        'numberedList',
                        '|',
                        'outdent',
                        'indent',
                        '|',
                        'imageUpload',
                        'blockQuote',
                        'insertTable',
                        'mediaEmbed',
                        'undo',
                        'redo',
                        '|',
                        'alignment',
                        'fontColor',
                        'fontBackgroundColor',
                        'highlight',
                        '|',
                        'horizontalLine',
                        'pageBreak',
                        'removeFormat',
                        'specialCharacters'
                    ]
                },
                language: 'en',
                image: {
                    toolbar: [
                        'imageTextAlternative',
                        'imageStyle:full',
                        'imageStyle:side'
                    ]
                },
                table: {
                    contentToolbar: [
                        'tableColumn',
                        'tableRow',
                        'mergeTableCells',
                        'tableCellProperties',
                        'tableProperties'
                    ]
                },
                mediaEmbed: {
                    previewsInData: true
                },
                // File upload configuration
                ckfinder: {
                    uploadUrl: '/admin/api/upload.php'
                }
            })
            .then(editor => {
                // Store editor instance
                editor.sourceElement.ckeditorInstance = editor;

                // Handle form submission
                const form = editor.sourceElement.closest('form');
                if (form) {
                    form.addEventListener('submit', () => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = editor.sourceElement.getAttribute('name');
                        input.value = editor.getData();
                        form.appendChild(input);
                    });
                }
            })
            .catch(error => {
                console.error(error);
            });
    });
}

// File Manager Configuration
const fileManager = {
    init() {
        this.dropzone = new Dropzone('#fileUpload', {
            url: '/admin/api/upload.php',
            paramName: 'file',
            maxFilesize: 10, // MB
            acceptedFiles: 'image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx',
            addRemoveLinks: true,
            dictDefaultMessage: 'Drop files here or click to upload',
            init() {
                this.on('success', function(file, response) {
                    fileManager.refreshFileList();
                });
                this.on('error', function(file, errorMessage) {
                    notifications.error(errorMessage);
                });
            }
        });

        this.refreshFileList();
        this.bindEvents();
    },

    refreshFileList() {
        fetch('/admin/api/files.php')
            .then(response => response.json())
            .then(data => {
                const fileList = document.getElementById('fileList');
                fileList.innerHTML = '';

                data.forEach(file => {
                    const item = this.createFileItem(file);
                    fileList.appendChild(item);
                });
            })
            .catch(error => {
                notifications.error('Failed to load files');
                console.error(error);
            });
    },

    createFileItem(file) {
        const item = document.createElement('div');
        item.className = 'file-item bg-white p-4 rounded-lg shadow flex items-center justify-between';
        item.innerHTML = `
            <div class="flex items-center">
                <div class="file-preview w-12 h-12 mr-4">
                    ${this.getFilePreview(file)}
                </div>
                <div>
                    <h4 class="font-medium">${file.name}</h4>
                    <p class="text-sm text-gray-500">${file.size} - ${file.date}</p>
                </div>
            </div>
            <div class="flex space-x-2">
                <button onclick="fileManager.copyUrl('${file.url}')" 
                        class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-link"></i>
                </button>
                <button onclick="fileManager.deleteFile('${file.id}')"
                        class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        return item;
    },

    getFilePreview(file) {
        if (file.type.startsWith('image/')) {
            return `<img src="${file.thumbnail}" alt="${file.name}" class="w-full h-full object-cover rounded">`;
        }
        
        const icons = {
            'application/pdf': 'fa-file-pdf',
            'application/msword': 'fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'fa-file-word',
            'application/vnd.ms-excel': 'fa-file-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'fa-file-excel',
            'application/vnd.ms-powerpoint': 'fa-file-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'fa-file-powerpoint'
        };

        const iconClass = icons[file.type] || 'fa-file';
        return `<i class="fas ${iconClass} text-4xl text-gray-400"></i>`;
    },

    copyUrl(url) {
        navigator.clipboard.writeText(url)
            .then(() => notifications.success('URL copied to clipboard'))
            .catch(() => notifications.error('Failed to copy URL'));
    },

    deleteFile(id) {
        if (confirm('Are you sure you want to delete this file?')) {
            fetch(`/admin/api/delete-file.php?id=${id}`, { method: 'DELETE' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.refreshFileList();
                        notifications.success('File deleted successfully');
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    notifications.error('Failed to delete file');
                    console.error(error);
                });
        }
    },

    bindEvents() {
        // Drag and drop highlighting
        const dropzone = document.getElementById('fileUpload');
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.classList.add('bg-blue-50', 'border-blue-300');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzone.classList.remove('bg-blue-50', 'border-blue-300');
            });
        });

        // File selection
        document.addEventListener('click', event => {
            const fileItem = event.target.closest('.file-item');
            if (fileItem) {
                document.querySelectorAll('.file-item').forEach(item => {
                    item.classList.remove('ring-2', 'ring-blue-500');
                });
                fileItem.classList.add('ring-2', 'ring-blue-500');
            }
        });
    }
};

// Initialize components when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize CKEditor on all .ckeditor elements
    if (document.querySelector('.ckeditor')) {
        initCKEditor();
    }

    // Initialize file manager if present
    if (document.getElementById('fileUpload')) {
        fileManager.init();
    }

    // Initialize date pickers
    const datePickers = document.querySelectorAll('.datepicker');
    datePickers.forEach(input => {
        flatpickr(input, {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    });

    // Initialize time pickers
    const timePickers = document.querySelectorAll('.timepicker');
    timePickers.forEach(input => {
        flatpickr(input, {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true
        });
    });

    // Initialize select2 dropdowns
    const selects = document.querySelectorAll('.select2');
    selects.forEach(select => {
        new Select2(select, {
            placeholder: select.dataset.placeholder || 'Select an option'
        });
    });

    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        tippy(element, {
            content: element.dataset.tooltip,
            placement: element.dataset.tooltipPlacement || 'top'
        });
    });

    // Initialize sortable lists
    const sortableLists = document.querySelectorAll('.sortable');
    sortableLists.forEach(list => {
        new Sortable(list, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'bg-gray-100'
        });
    });
});

// Utility functions
const utils = {
    formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    },

    formatTime(time) {
        return new Date(`2000-01-01 ${time}`).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    formatNumber(number) {
        return new Intl.NumberFormat('en-US').format(number);
    },

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    },

    slugify(text) {
        return text
            .toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
    },

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};