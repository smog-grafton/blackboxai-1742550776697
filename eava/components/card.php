<?php
class Card {
    private $options;
    private $header;
    private $content;
    private $footer;
    private $actions;

    /**
     * Constructor
     */
    public function __construct($options = []) {
        $this->options = array_merge([
            'class' => '',
            'hover' => false,
            'shadow' => true,
            'rounded' => true,
            'border' => true,
            'padding' => true,
            'clickable' => false,
            'href' => null,
            'onClick' => null
        ], $options);

        $this->header = null;
        $this->content = null;
        $this->footer = null;
        $this->actions = [];
    }

    /**
     * Set header
     */
    public function setHeader($content, $options = []) {
        $this->header = [
            'content' => $content,
            'options' => array_merge([
                'class' => '',
                'border' => true,
                'padding' => true
            ], $options)
        ];
        return $this;
    }

    /**
     * Set content
     */
    public function setContent($content, $options = []) {
        $this->content = [
            'content' => $content,
            'options' => array_merge([
                'class' => '',
                'padding' => true
            ], $options)
        ];
        return $this;
    }

    /**
     * Set footer
     */
    public function setFooter($content, $options = []) {
        $this->footer = [
            'content' => $content,
            'options' => array_merge([
                'class' => '',
                'border' => true,
                'padding' => true
            ], $options)
        ];
        return $this;
    }

    /**
     * Add action
     */
    public function addAction($label, $options = []) {
        $this->actions[] = array_merge([
            'label' => $label,
            'href' => null,
            'onClick' => null,
            'icon' => null,
            'class' => 'text-blue-600 hover:text-blue-800',
            'attributes' => []
        ], $options);
        return $this;
    }

    /**
     * Get base classes
     */
    private function getBaseClasses() {
        $classes = ['bg-white'];

        if ($this->options['rounded']) {
            $classes[] = 'rounded-lg';
        }

        if ($this->options['shadow']) {
            $classes[] = 'shadow-lg';
        }

        if ($this->options['border']) {
            $classes[] = 'border border-gray-200';
        }

        if ($this->options['hover']) {
            $classes[] = 'transition-all duration-200';
            $classes[] = 'hover:shadow-xl';
            if ($this->options['border']) {
                $classes[] = 'hover:border-gray-300';
            }
        }

        if ($this->options['clickable']) {
            $classes[] = 'cursor-pointer';
        }

        return implode(' ', $classes);
    }

    /**
     * Render card
     */
    public function render() {
        $baseClasses = $this->getBaseClasses();
        $wrapperTag = $this->options['href'] ? 'a' : 'div';
        $wrapperAttrs = $this->options['href'] ? 
            'href="' . htmlspecialchars($this->options['href']) . '"' : '';
        
        if ($this->options['onClick']) {
            $wrapperAttrs .= ' onclick="' . htmlspecialchars($this->options['onClick']) . '"';
        }
        ?>
        <<?= $wrapperTag ?> class="<?= $baseClasses ?> <?= $this->options['class'] ?>" <?= $wrapperAttrs ?>>
            <?php if ($this->header): ?>
                <div class="<?= $this->header['options']['border'] ? 'border-b border-gray-200' : '' ?> 
                           <?= $this->header['options']['padding'] ? 'p-4' : '' ?> 
                           <?= $this->header['options']['class'] ?>">
                    <?= $this->header['content'] ?>
                </div>
            <?php endif; ?>

            <?php if ($this->content): ?>
                <div class="<?= $this->content['options']['padding'] ? 'p-4' : '' ?> 
                           <?= $this->content['options']['class'] ?>">
                    <?= $this->content['content'] ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($this->actions)): ?>
                <div class="px-4 py-3 sm:px-6 flex justify-end space-x-3 border-t border-gray-200">
                    <?php foreach ($this->actions as $action): ?>
                        <?php if ($action['href']): ?>
                            <a href="<?= htmlspecialchars($action['href']) ?>" 
                               class="<?= $action['class'] ?>"
                               <?php foreach ($action['attributes'] as $attr => $value): ?>
                                   <?= $attr ?>="<?= htmlspecialchars($value) ?>"
                               <?php endforeach; ?>>
                                <?php if ($action['icon']): ?>
                                    <i class="<?= $action['icon'] ?> mr-1"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($action['label']) ?>
                            </a>
                        <?php else: ?>
                            <button type="button" 
                                    class="<?= $action['class'] ?>"
                                    <?php if ($action['onClick']): ?>
                                        onclick="<?= htmlspecialchars($action['onClick']) ?>"
                                    <?php endif; ?>
                                    <?php foreach ($action['attributes'] as $attr => $value): ?>
                                        <?= $attr ?>="<?= htmlspecialchars($value) ?>"
                                    <?php endforeach; ?>>
                                <?php if ($action['icon']): ?>
                                    <i class="<?= $action['icon'] ?> mr-1"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($action['label']) ?>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($this->footer): ?>
                <div class="<?= $this->footer['options']['border'] ? 'border-t border-gray-200' : '' ?> 
                           <?= $this->footer['options']['padding'] ? 'p-4' : '' ?> 
                           <?= $this->footer['options']['class'] ?>">
                    <?= $this->footer['content'] ?>
                </div>
            <?php endif; ?>
        </<?= $wrapperTag ?>>
        <?php
    }

    /**
     * Static helper to create and render a card in one go
     */
    public static function create($content, $options = []) {
        $card = new self($options);
        $card->setContent($content);
        $card->render();
    }

    /**
     * Static helper to create a grid of cards
     */
    public static function grid($cards, $options = []) {
        $options = array_merge([
            'columns' => [
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
                'xl' => 4
            ],
            'gap' => 6,
            'class' => ''
        ], $options);

        $gridClass = 'grid gap-' . $options['gap'];
        $gridClass .= ' grid-cols-' . $options['columns']['sm'];
        $gridClass .= ' md:grid-cols-' . $options['columns']['md'];
        $gridClass .= ' lg:grid-cols-' . $options['columns']['lg'];
        $gridClass .= ' xl:grid-cols-' . $options['columns']['xl'];
        ?>
        <div class="<?= $gridClass ?> <?= $options['class'] ?>">
            <?php foreach ($cards as $card): ?>
                <?php $card->render(); ?>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

// Usage example:
if (!isset($hideExample)) {
    // Basic card
    /*
    $card = new Card([
        'hover' => true,
        'clickable' => true,
        'href' => '/article/1'
    ]);

    $card->setHeader('
        <h3 class="text-lg font-semibold">Card Title</h3>
    ');

    $card->setContent('
        <p>This is the card content.</p>
    ');

    $card->setFooter('
        <p class="text-sm text-gray-500">Last updated 3 mins ago</p>
    ');

    $card->addAction('View Details', [
        'href' => '/article/1',
        'icon' => 'fas fa-arrow-right'
    ]);

    $card->addAction('Delete', [
        'class' => 'text-red-600 hover:text-red-800',
        'icon' => 'fas fa-trash',
        'onClick' => 'deleteArticle(1)'
    ]);

    $card->render();
    */

    // Card grid
    /*
    $cards = [
        new Card(['hover' => true])->setContent('Card 1'),
        new Card(['hover' => true])->setContent('Card 2'),
        new Card(['hover' => true])->setContent('Card 3'),
        new Card(['hover' => true])->setContent('Card 4')
    ];

    Card::grid($cards, [
        'columns' => [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 4
        ],
        'gap' => 6
    ]);
    */
}