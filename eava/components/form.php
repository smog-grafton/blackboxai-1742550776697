<?php
class Form {
    private $id;
    private $action;
    private $method;
    private $fields;
    private $buttons;
    private $options;

    /**
     * Constructor
     */
    public function __construct($id, $action = '', $method = 'POST', $options = []) {
        $this->id = $id;
        $this->action = $action;
        $this->method = strtoupper($method);
        $this->fields = [];
        $this->buttons = [];
        $this->options = array_merge([
            'class' => '',
            'enctype' => null,
            'ajax' => false,
            'validate' => true,
            'resetOnSuccess' => true,
            'successMessage' => 'Form submitted successfully!',
            'errorMessage' => 'Please check the form for errors.'
        ], $options);
    }

    /**
     * Add field
     */
    public function addField($name, $options = []) {
        $this->fields[$name] = array_merge([
            'type' => 'text',
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
            'attributes' => [],
            'options' => [], // For select, radio, checkbox
            'validate' => [] // Validation rules
        ], $options);

        return $this;
    }

    /**
     * Add button
     */
    public function addButton($text, $options = []) {
        $this->buttons[] = array_merge([
            'type' => 'submit',
            'text' => $text,
            'class' => 'bg-blue-600 text-white',
            'disabled' => false,
            'attributes' => []
        ], $options);

        return $this;
    }

    /**
     * Render form
     */
    public function render() {
        ?>
        <form id="<?= $this->id ?>"
              action="<?= htmlspecialchars($this->action) ?>"
              method="<?= $this->method ?>"
              <?= $this->options['enctype'] ? 'enctype="' . $this->options['enctype'] . '"' : '' ?>
              class="space-y-6 <?= $this->options['class'] ?>"
              <?= $this->options['ajax'] ? 'x-data="formHandler(\'' . $this->id . '\')"' : '' ?>
              <?= $this->options['validate'] ? 'novalidate' : '' ?>>
            
            <?php if ($this->method === 'POST'): ?>
                <input type="hidden" name="csrf_token" value="<?= Session::getInstance()->getCsrfToken() ?>">
            <?php endif; ?>

            <!-- Form Fields -->
            <?php foreach ($this->fields as $name => $field): ?>
                <div class="form-group <?= $field['wrapper_class'] ?>">
                    <?php if ($field['label']): ?>
                        <label for="<?= $this->id ?>_<?= $name ?>" 
                               class="block text-sm font-medium text-gray-700 mb-1">
                            <?= htmlspecialchars($field['label']) ?>
                            <?php if ($field['required']): ?>
                                <span class="text-red-500">*</span>
                            <?php endif; ?>
                        </label>
                    <?php endif; ?>

                    <?php switch ($field['type']):
                        case 'textarea': ?>
                            <textarea id="<?= $this->id ?>_<?= $name ?>"
                                    name="<?= $name ?>"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?= $field['class'] ?>"
                                    placeholder="<?= htmlspecialchars($field['placeholder']) ?>"
                                    <?= $field['required'] ? 'required' : '' ?>
                                    <?= $field['disabled'] ? 'disabled' : '' ?>
                                    <?= $field['readonly'] ? 'readonly' : '' ?>
                                    <?php foreach ($field['attributes'] as $attr => $value): ?>
                                        <?= $attr ?>="<?= htmlspecialchars($value) ?>"
                                    <?php endforeach; ?>><?= htmlspecialchars($field['value']) ?></textarea>
                            <?php break;

                        case 'select': ?>
                            <select id="<?= $this->id ?>_<?= $name ?>"
                                   name="<?= $name ?>"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?= $field['class'] ?>"
                                   <?= $field['required'] ? 'required' : '' ?>
                                   <?= $field['disabled'] ? 'disabled' : '' ?>
                                   <?= $field['readonly'] ? 'readonly' : '' ?>
                                   <?php foreach ($field['attributes'] as $attr => $value): ?>
                                       <?= $attr ?>="<?= htmlspecialchars($value) ?>"
                                   <?php endforeach; ?>>
                                <?php foreach ($field['options'] as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>"
                                            <?= $field['value'] == $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php break;

                        case 'radio':
                        case 'checkbox': ?>
                            <div class="mt-1 space-y-2">
                                <?php foreach ($field['options'] as $value => $label): ?>
                                    <div class="flex items-center">
                                        <input type="<?= $field['type'] ?>"
                                               id="<?= $this->id ?>_<?= $name ?>_<?= $value ?>"
                                               name="<?= $name ?><?= $field['type'] === 'checkbox' ? '[]' : '' ?>"
                                               value="<?= htmlspecialchars($value) ?>"
                                               class="h-4 w-4 border-gray-300 <?= $field['type'] === 'radio' ? 'rounded-full' : 'rounded' ?> text-blue-600 focus:ring-blue-500 <?= $field['class'] ?>"
                                               <?= (is_array($field['value']) ? in_array($value, $field['value']) : $field['value'] == $value) ? 'checked' : '' ?>
                                               <?= $field['required'] ? 'required' : '' ?>
                                               <?= $field['disabled'] ? 'disabled' : '' ?>
                                               <?= $field['readonly'] ? 'readonly' : '' ?>
                                               <?php foreach ($field['attributes'] as $attr => $attrValue): ?>
                                                   <?= $attr ?>="<?= htmlspecialchars($attrValue) ?>"
                                               <?php endforeach; ?>>
                                        <label for="<?= $this->id ?>_<?= $name ?>_<?= $value ?>"
                                               class="ml-2 block text-sm text-gray-700">
                                            <?= htmlspecialchars($label) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php break;

                        default: ?>
                            <input type="<?= $field['type'] ?>"
                                   id="<?= $this->id ?>_<?= $name ?>"
                                   name="<?= $name ?>"
                                   value="<?= htmlspecialchars($field['value']) ?>"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?= $field['class'] ?>"
                                   placeholder="<?= htmlspecialchars($field['placeholder']) ?>"
                                   <?= $field['required'] ? 'required' : '' ?>
                                   <?= $field['disabled'] ? 'disabled' : '' ?>
                                   <?= $field['readonly'] ? 'readonly' : '' ?>
                                   <?php foreach ($field['attributes'] as $attr => $value): ?>
                                       <?= $attr ?>="<?= htmlspecialchars($value) ?>"
                                   <?php endforeach; ?>>
                    <?php endswitch; ?>

                    <?php if ($field['help']): ?>
                        <p class="mt-1 text-sm text-gray-500"><?= $field['help'] ?></p>
                    <?php endif; ?>

                    <?php if ($field['error']): ?>
                        <p class="mt-1 text-sm text-red-600"><?= $field['error'] ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Form Buttons -->
            <?php if (!empty($this->buttons)): ?>
                <div class="flex justify-end space-x-3">
                    <?php foreach ($this->buttons as $button): ?>
                        <button type="<?= $button['type'] ?>"
                                class="px-4 py-2 rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 <?= $button['class'] ?>"
                                <?= $button['disabled'] ? 'disabled' : '' ?>
                                <?php foreach ($button['attributes'] as $attr => $value): ?>
                                    <?= $attr ?>="<?= htmlspecialchars($value) ?>"
                                <?php endforeach; ?>>
                            <?= htmlspecialchars($button['text']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </form>

        <?php if ($this->options['ajax']): ?>
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
                            if (<?= json_encode($this->options['validate']) ?> && !form.checkValidity()) {
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
                                    this.message = data.message || <?= json_encode($this->options['successMessage']) ?>;
                                    
                                    if (<?= json_encode($this->options['resetOnSuccess']) ?>) {
                                        form.reset();
                                    }

                                    form.dispatchEvent(new CustomEvent('form:success', { detail: data }));
                                } else {
                                    throw new Error(data.message || <?= json_encode($this->options['errorMessage']) ?>);
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
}

// Usage example:
if (!isset($hideExample)) {
    // Basic form
    /*
    $form = new Form('contact-form', '/contact', 'POST', [
        'ajax' => true,
        'class' => 'max-w-lg mx-auto'
    ]);

    $form->addField('name', [
        'label' => 'Your Name',
        'required' => true,
        'placeholder' => 'Enter your name'
    ])
    ->addField('email', [
        'type' => 'email',
        'label' => 'Email Address',
        'required' => true,
        'placeholder' => 'Enter your email'
    ])
    ->addField('message', [
        'type' => 'textarea',
        'label' => 'Message',
        'required' => true,
        'placeholder' => 'Enter your message'
    ])
    ->addButton('Send Message', [
        'type' => 'submit',
        'class' => 'bg-blue-600 text-white'
    ])
    ->addButton('Cancel', [
        'type' => 'button',
        'class' => 'bg-gray-200 text-gray-700'
    ]);

    $form->render();
    */
}