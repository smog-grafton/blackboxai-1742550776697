<?php
/**
 * Rich Text Editor Template
 * Includes CKEditor with file manager integration
 */

// Required scripts
$editorScripts = [
    'https://cdn.ckeditor.com/ckeditor5/35.0.1/classic/ckeditor.js',
    '/admin/assets/js/admin.js'
];

// Required styles
$editorStyles = [
    '/admin/assets/css/admin.css'
];

function renderEditor($name, $content = '', $options = []) {
    $options = array_merge([
        'height' => '400px',
        'toolbar' => 'full', // full, basic
        'mediaUpload' => true,
        'placeholder' => 'Start writing...'
    ], $options);

    $editorId = preg_replace('/[^a-z0-9]/i', '_', $name);
    ?>
    <div class="editor-wrapper">
        <textarea
            id="<?= $editorId ?>"
            name="<?= htmlspecialchars($name) ?>"
            class="ckeditor"
            data-toolbar="<?= $options['toolbar'] ?>"
            data-media-upload="<?= $options['mediaUpload'] ? 'true' : 'false' ?>"
            style="height: <?= $options['height'] ?>"
            placeholder="<?= htmlspecialchars($options['placeholder']) ?>"
        ><?= htmlspecialchars($content) ?></textarea>
    </div>

    <?php if ($options['mediaUpload']): ?>
        <!-- File Manager Modal -->
        <div id="fileManager_<?= $editorId ?>" class="modal hidden">
            <div class="modal-backdrop"></div>
            <div class="modal-content max-w-4xl w-full mx-4">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold">Media Library</h2>
                        <button type="button" class="modal-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Upload Zone -->
                    <div id="uploadZone_<?= $editorId ?>" class="upload-zone mb-6">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                        <p>Drop files here or click to upload</p>
                    </div>

                    <!-- Media Grid -->
                    <div id="mediaGrid_<?= $editorId ?>" class="media-grid">
                        <!-- Items will be loaded dynamically -->
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end mt-6 space-x-3">
                        <button type="button" 
                                class="btn btn-outline modal-close">
                            Cancel
                        </button>
                        <button type="button" 
                                class="btn btn-primary" 
                                onclick="insertMedia('<?= $editorId ?>')">
                            Insert Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initCKEditor('#<?= $editorId ?>', {
                toolbar: '<?= $options['toolbar'] ?>',
                mediaUpload: <?= $options['mediaUpload'] ? 'true' : 'false' ?>,
                fileManagerId: 'fileManager_<?= $editorId ?>'
            });
        });
    </script>
    <?php
}
?>

<!-- Usage Example -->
<?php if (!isset($hideExample)): ?>
    <!--
    <?php
    // Basic usage
    renderEditor('content');

    // With options
    renderEditor('description', '', [
        'height' => '200px',
        'toolbar' => 'basic',
        'mediaUpload' => false,
        'placeholder' => 'Enter description here...'
    ]);
    ?>
    -->
<?php endif; ?>