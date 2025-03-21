<?php
/**
 * Form Template
 * Reusable form components with validation and AJAX support
 */

function renderForm($options = []) {
    $options = array_merge([
        'id' => 'form-' . uniqid(),
        'action' => '',
        'method' => 'POST',
        'enctype' => null,
        'class' => '',
        'ajax' => true,
        'validate' => true,
        'resetOnSuccess' => true,
        'successMessage' => 'Form submitted successfully!',
        'errorMessage' => 'Please check the form for errors.',
        'buttons' => [
            [
                'type' => 'submit',
                'text' => 'Submit',
                'class' => 'btn-primary'
            ]
        ]
    ], $options);
    ?>
    <form id="<?= $options['id'] ?>"
          action="<?= htmlspecialchars($options['action']) ?>"
          method="<?= $options['method'] ?>"
          <?= $options['enctype'] ? 'enctype="' . $options['enctype'] . '"' : '' ?>
          class="space-y-6 <?= $options['class'] ?>"
          <?= $options['ajax'] ? 'x-data="formHandler(\'' . $options['id'] . '\')"' : '' ?>
          <?= $options['validate'] ? 'novalidate' : '' ?>>
        
        <?php if ($options['method'] === 'POST'): ?>
            <input type="hidden" name="csrf_token" value="<?= Session::getInstance()->getCsrfToken() ?>">
        <?php endif; ?>

        <!-- Form content will be injected here -->
        <?php if (isset($options['content'])): ?>
            <?= $options['content'] ?>
        <?php endif; ?>

        <!-- Form buttons -->
        <?php if (!empty($options['buttons'])): ?>
            <div class="flex justify-end space-x-3">
                <?php foreach ($options['buttons'] as $button): ?>
                    <button type="<?= $button['type'] ?>"
                            class="btn <?= $button['class'] ?>"
                            <?= isset($button['disabled']) && $button['disabled'] ? 'disabled' : '' ?>
                            <?php if (isset($button['onClick'])): ?>
                                onclick="<?= htmlspecialchars($button['onClick']) ?>"
                            <?php endif; ?>>
                        <?= htmlspecialchars($button['text']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </form>

    <?php if ($options['ajax']): ?>
        <script>
            function formHandler(formId) {
                return {
                    submitting: false,
                    success: false,
                    error: false,
                    message: '',

                    async submitForm(event) {
                        event.preventDefault();
                        if (this.submitting) return;

                        const form = event.target;
                        const formData = new FormData(form);

                        // Client-side validation
                        if (<?= json_encode($options['validate']) ?> && !form.checkValidity()) {
                            form.reportValidity();
                            return;
                        }

                        this.submitting = true;
                        this.success = false;
                        this.error = false;
                        this.message = '';

                        try {
                            const response = await fetch(form.action, {
                                method: form.method,
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            const data = await response.json();

                            if (response.ok) {
                                this.success = true;
                                this.message = data.message || <?= json_encode($options['successMessage']) ?>;
                                
                                if (<?= json_encode($options['resetOnSuccess']) ?>) {
                                    form.reset();
                                }

                                form.dispatchEvent(new CustomEvent('form:success', { detail: data }));
                            } else {
                                throw new Error(data.message || <?= json_encode($options['errorMessage']) ?>);
                            }
                        } catch (error) {
                            this.error = true;
                            this.message = error.message;
                            form.dispatchEvent(new CustomEvent('form:error', { detail: error }));
                        } finally {
                            this.submitting = false;
                        }
                    }
                };
            }
        </script>
    <?php endif; ?>
    <?php
}

function renderField($type, $name, $options = []) {
    $options = array_merge([
        'label' => ucwords(str_replace('_', ' ', $name)),
        'placeholder' => '',
        'value' => '',
        'required' => false,
        'disabled' => false,
        'readonly' => false,
        'class' => '',
        'wrapper_class' => '',
        'help' => '',
        'error' => '',
        'attributes' => []
    ], $options);

    $id = preg_replace('/[^a-z0-9]/i', '_', $name);
    ?>
    <div class="form-group <?= $options['wrapper_class'] ?>">
        <?php if ($options['label']): ?>
            <label for="<?= $id ?>" class="block text-sm font-medium text-gray-700 mb-1">
                <?= htmlspecialchars($options['label']) ?>
                <?php if ($options['required']): ?>
                    <span class="text-red-500">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <?php switch ($type):
            case 'textarea': ?>
                <textarea id="<?= $id ?>"
                        name="<?= $name ?>"
                        class="form-input <?= $options['class'] ?>"
                        placeholder="<?= htmlspecialchars($options['placeholder']) ?>"
                        <?= $options['required'] ? 'required' : '' ?>
                        <?= $options['disabled'] ? 'disabled' : '' ?>
                        <?= $options['readonly'] ? 'readonly' : '' ?>
                        <?php foreach ($options['attributes'] as $attr => $value): ?>
                            <?= $attr ?>="<?= htmlspecialchars($value) ?>"
                        <?php endforeach; ?>><?= htmlspecialchars($options['value']) ?></textarea>
                <?php break;

            case 'select': ?>
                <select id="<?= $id ?>"
                       name="<?= $name ?>"
                       class="form-select <?= $options['class'] ?>"
                       <?= $options['required'] ? 'required' : '' ?>
                       <?= $options['disabled'] ? 'disabled' : '' ?>
                       <?= $options['readonly'] ? 'readonly' : '' ?>
                       <?php foreach ($options['attributes'] as $attr => $value): ?>
                           <?= $attr ?>="<?= htmlspecialchars($value) ?>"
                       <?php endforeach; ?>>
                    <?php foreach ($options['options'] as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>"
                                <?= $options['value'] == $value ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php break;

            case 'radio':
            case 'checkbox': ?>
                <div class="space-y-2">
                    <?php foreach ($options['options'] as $value => $label): ?>
                        <div class="flex items-center">
                            <input type="<?= $type ?>"
                                   id="<?= $id ?>_<?= $value ?>"
                                   name="<?= $name ?><?= $type === 'checkbox' ? '[]' : '' ?>"
                                   value="<?= htmlspecialchars($value) ?>"
                                   class="form-<?= $type ?> <?= $options['class'] ?>"
                                   <?= (is_array($options['value']) ? in_array($value, $options['value']) : $options['value'] == $value) ? 'checked' : '' ?>
                                   <?= $options['required'] ? 'required' : '' ?>
                                   <?= $options['disabled'] ? 'disabled' : '' ?>
                                   <?= $options['readonly'] ? 'readonly' : '' ?>
                                   <?php foreach ($options['attributes'] as $attr => $attrValue): ?>
                                       <?= $attr ?>="<?= htmlspecialchars($attrValue) ?>"
                                   <?php endforeach; ?>>
                            <label for="<?= $id ?>_<?= $value ?>"
                                   class="ml-2 text-sm text-gray-700">
                                <?= htmlspecialchars($label) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php break;

            default: ?>
                <input type="<?= $type ?>"
                       id="<?= $id ?>"
                       name="<?= $name ?>"
                       value="<?= htmlspecialchars($options['value']) ?>"
                       class="form-input <?= $options['class'] ?>"
                       placeholder="<?= htmlspecialchars($options['placeholder']) ?>"
                       <?= $options['required'] ? 'required' : '' ?>
                       <?= $options['disabled'] ? 'disabled' : '' ?>
                       <?= $options['readonly'] ? 'readonly' : '' ?>
                       <?php foreach ($options['attributes'] as $attr => $value): ?>
                           <?= $attr ?>="<?= htmlspecialchars($value) ?>"
                       <?php endforeach; ?>>
        <?php endswitch; ?>

        <?php if ($options['help']): ?>
            <p class="mt-1 text-sm text-gray-500"><?= $options['help'] ?></p>
        <?php endif; ?>

        <?php if ($options['error']): ?>
            <p class="mt-1 text-sm text-red-600"><?= $options['error'] ?></p>
        <?php endif; ?>
    </div>
    <?php
}
?>

<!-- Usage Example -->
<?php if (!isset($hideExample)): ?>
    <!--
    <?php
    // Basic form
    ob_start();
    ?>
    <div class="space-y-6">
        <?php
        renderField('text', 'name', [
            'required' => true,
            'placeholder' => 'Enter your name'
        ]);

        renderField('email', 'email', [
            'required' => true,
            'placeholder' => 'Enter your email'
        ]);

        renderField('textarea', 'message', [
            'required' => true,
            'placeholder' => 'Enter your message'
        ]);
        ?>
    </div>
    <?php
    $formContent = ob_get_clean();

    renderForm([
        'action' => '/contact',
        'content' => $formContent,
        'buttons' => [
            [
                'type' => 'submit',
                'text' => 'Send Message',
                'class' => 'btn-primary'
            ],
            [
                'type' => 'button',
                'text' => 'Cancel',
                'class' => 'btn-outline',
                'onClick' => 'history.back()'
            ]
        ]
    ]);
    ?>
    -->
<?php endif; ?>