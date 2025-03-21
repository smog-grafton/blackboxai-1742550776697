<?php
/**
 * File Manager Template
 * Standalone file manager with drag-and-drop upload
 */

// Required scripts
$fileManagerScripts = [
    'https://unpkg.com/dropzone@5/dist/min/dropzone.min.js',
    '/admin/assets/js/admin.js'
];

// Required styles
$fileManagerStyles = [
    'https://unpkg.com/dropzone@5/dist/min/dropzone.min.css',
    '/admin/assets/css/admin.css'
];

function renderFileManager($options = []) {
    $options = array_merge([
        'maxFileSize' => 10, // MB
        'acceptedFiles' => 'image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx',
        'multiple' => true,
        'preview' => true,
        'sortable' => true
    ], $options);
    ?>
    <div class="file-manager">
        <!-- Upload Zone -->
        <div class="mb-6">
            <div id="uploadZone" class="upload-zone" data-max-size="<?= $options['maxFileSize'] ?>">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                <p>Drop files here or click to upload</p>
                <p class="text-sm text-gray-500 mt-1">
                    Maximum file size: <?= $options['maxFileSize'] ?>MB
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <select id="filterType" class="form-select">
                    <option value="">All Types</option>
                    <option value="image">Images</option>
                    <option value="document">Documents</option>
                    <option value="video">Videos</option>
                    <option value="audio">Audio</option>
                </select>
                <input type="text" 
                       id="searchFiles" 
                       placeholder="Search files..." 
                       class="form-input">
            </div>
            <div class="flex items-center space-x-2">
                <button type="button" 
                        onclick="toggleView('grid')"
                        class="btn btn-outline">
                    <i class="fas fa-th-large"></i>
                </button>
                <button type="button" 
                        onclick="toggleView('list')"
                        class="btn btn-outline">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>

        <!-- File List -->
        <div id="fileList" class="<?= $options['preview'] ? 'media-grid' : 'space-y-2' ?>">
            <!-- Items will be loaded dynamically -->
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden">
            <div class="flex items-center justify-center py-12">
                <div class="spinner"></div>
                <span class="ml-3">Loading files...</span>
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden">
            <div class="text-center py-12">
                <i class="fas fa-folder-open text-4xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">No files found</p>
            </div>
        </div>

        <!-- Error State -->
        <div id="errorState" class="hidden">
            <div class="text-center py-12 text-red-600">
                <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                <p>Failed to load files</p>
                <button onclick="refreshFiles()" 
                        class="btn btn-outline mt-4">
                    Try Again
                </button>
            </div>
        </div>

        <!-- File Details Sidebar -->
        <div id="fileDetails" class="hidden fixed inset-y-0 right-0 w-80 bg-white shadow-lg border-l">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold">File Details</h3>
                    <button onclick="closeFileDetails()" 
                            class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="fileDetailsContent">
                    <!-- Details will be loaded dynamically -->
                </div>
            </div>
        </div>

        <!-- File Actions Menu -->
        <div id="fileActionsMenu" class="hidden absolute bg-white rounded-lg shadow-lg py-2 w-48">
            <button onclick="previewFile()" class="w-full px-4 py-2 text-left hover:bg-gray-100">
                <i class="fas fa-eye mr-2"></i> Preview
            </button>
            <button onclick="downloadFile()" class="w-full px-4 py-2 text-left hover:bg-gray-100">
                <i class="fas fa-download mr-2"></i> Download
            </button>
            <button onclick="copyFileUrl()" class="w-full px-4 py-2 text-left hover:bg-gray-100">
                <i class="fas fa-link mr-2"></i> Copy URL
            </button>
            <button onclick="renameFile()" class="w-full px-4 py-2 text-left hover:bg-gray-100">
                <i class="fas fa-edit mr-2"></i> Rename
            </button>
            <div class="border-t border-gray-200 my-1"></div>
            <button onclick="deleteFile()" class="w-full px-4 py-2 text-left text-red-600 hover:bg-red-50">
                <i class="fas fa-trash mr-2"></i> Delete
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize file manager
            const fileManager = new FileManager('#uploadZone', {
                maxFileSize: <?= $options['maxFileSize'] ?>,
                acceptedFiles: <?= json_encode($options['acceptedFiles']) ?>,
                multiple: <?= $options['multiple'] ? 'true' : 'false' ?>,
                preview: <?= $options['preview'] ? 'true' : 'false' ?>,
                sortable: <?= $options['sortable'] ? 'true' : 'false' ?>,
                onUploadComplete: function(file) {
                    notifications.success('File uploaded successfully');
                    refreshFiles();
                },
                onUploadError: function(file, message) {
                    notifications.error(message || 'Failed to upload file');
                },
                onFileSelect: function(file) {
                    showFileDetails(file);
                },
                onFileDeselect: function() {
                    hideFileDetails();
                }
            });

            // Initialize search
            const searchInput = document.getElementById('searchFiles');
            searchInput.addEventListener('input', utils.debounce(function() {
                fileManager.search(this.value);
            }, 300));

            // Initialize type filter
            const filterType = document.getElementById('filterType');
            filterType.addEventListener('change', function() {
                fileManager.filter(this.value);
            });

            // Initialize context menu
            document.addEventListener('click', function(e) {
                const menu = document.getElementById('fileActionsMenu');
                if (!e.target.closest('#fileActionsMenu')) {
                    menu.classList.add('hidden');
                }
            });

            // Load initial files
            fileManager.loadFiles();
        });

        // File manager helper functions
        function showFileDetails(file) {
            const sidebar = document.getElementById('fileDetails');
            const content = document.getElementById('fileDetailsContent');
            
            content.innerHTML = `
                <div class="space-y-4">
                    ${file.type.startsWith('image/') ? `
                        <img src="${file.url}" alt="${file.name}" class="w-full rounded">
                    ` : `
                        <div class="bg-gray-100 rounded p-4 text-center">
                            <i class="fas ${getFileIcon(file.type)} text-4xl text-gray-400"></i>
                        </div>
                    `}
                    <div class="space-y-2">
                        <p class="font-medium">${file.name}</p>
                        <p class="text-sm text-gray-500">${file.type}</p>
                        <p class="text-sm text-gray-500">${formatFileSize(file.size)}</p>
                        <p class="text-sm text-gray-500">Uploaded ${formatDate(file.uploaded)}</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="downloadFile('${file.id}')" 
                                class="btn btn-outline flex-1">
                            <i class="fas fa-download mr-1"></i> Download
                        </button>
                        <button onclick="deleteFile('${file.id}')" 
                                class="btn btn-danger flex-1">
                            <i class="fas fa-trash mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            `;
            
            sidebar.classList.remove('hidden');
        }

        function hideFileDetails() {
            document.getElementById('fileDetails').classList.add('hidden');
        }

        function getFileIcon(type) {
            const icons = {
                'image': 'fa-image',
                'video': 'fa-video',
                'audio': 'fa-music',
                'application/pdf': 'fa-file-pdf',
                'application/msword': 'fa-file-word',
                'application/vnd.ms-excel': 'fa-file-excel',
                'application/vnd.ms-powerpoint': 'fa-file-powerpoint'
            };

            for (const [key, icon] of Object.entries(icons)) {
                if (type.startsWith(key)) {
                    return icon;
                }
            }

            return 'fa-file';
        }

        function formatFileSize(bytes) {
            const units = ['B', 'KB', 'MB', 'GB'];
            let size = bytes;
            let unitIndex = 0;

            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }

            return `${size.toFixed(1)} ${units[unitIndex]}`;
        }

        function formatDate(date) {
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    </script>
    <?php
}
?>

<!-- Usage Example -->
<?php if (!isset($hideExample)): ?>
    <!--
    <?php
    // Basic usage
    renderFileManager();

    // With options
    renderFileManager([
        'maxFileSize' => 5,
        'acceptedFiles' => 'image/*',
        'multiple' => false,
        'preview' => true,
        'sortable' => false
    ]);
    ?>
    -->
<?php endif; ?>