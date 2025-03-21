<?php
class Menu extends Model {
    protected $table = 'menus';
    protected $fillable = [
        'location',
        'item_id',
        'parent_id',
        'type',
        'label',
        'url',
        'target',
        'order',
        'status'
    ];

    /**
     * Get menu items by location
     */
    public function getByLocation($location) {
        $sql = "SELECT m.*, p.label as parent_label 
                FROM {$this->table} m 
                LEFT JOIN {$this->table} p ON m.parent_id = p.id 
                WHERE m.location = ? AND m.status = 'active' 
                ORDER BY m.order ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$location]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->buildMenuTree($items);
    }

    /**
     * Build menu tree from flat array
     */
    private function buildMenuTree(array $items, $parentId = null) {
        $branch = [];

        foreach ($items as $item) {
            if ($item['parent_id'] === $parentId) {
                $children = $this->buildMenuTree($items, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $branch[] = $item;
            }
        }

        return $branch;
    }

    /**
     * Save menu structure
     */
    public function saveMenu($location, array $items) {
        try {
            $this->db->beginTransaction();

            // First, update all items in this location to be inactive
            $sql = "UPDATE {$this->table} SET status = 'inactive' WHERE location = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$location]);

            // Then, update the provided items
            foreach ($items as $order => $item) {
                if (isset($item['id'])) {
                    // Update existing item
                    $this->update($item['id'], [
                        'order' => $order,
                        'status' => 'active'
                    ]);
                } else {
                    // Create new item
                    $this->create([
                        'location' => $location,
                        'type' => $item['type'],
                        'label' => $item['label'],
                        'url' => $item['url'],
                        'target' => $item['target'] ?? '_self',
                        'parent_id' => $item['parent_id'] ?? null,
                        'order' => $order,
                        'status' => 'active'
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Add menu item
     */
    public function addItem($location, array $data) {
        // Get the highest order number for this location
        $sql = "SELECT MAX(`order`) as max_order FROM {$this->table} WHERE location = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$location]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $order = ($result['max_order'] ?? -1) + 1;

        return $this->create([
            'location' => $location,
            'type' => $data['type'],
            'label' => $data['label'],
            'url' => $data['url'],
            'target' => $data['target'] ?? '_self',
            'parent_id' => $data['parent_id'] ?? null,
            'order' => $order,
            'status' => 'active'
        ]);
    }

    /**
     * Update menu item
     */
    public function updateItem($id, array $data) {
        return $this->update($id, array_intersect_key($data, array_flip([
            'type',
            'label',
            'url',
            'target',
            'parent_id',
            'order',
            'status'
        ])));
    }

    /**
     * Delete menu item
     */
    public function deleteItem($location, $id) {
        try {
            $this->db->beginTransaction();

            // Get the item to be deleted
            $item = $this->find($id);
            if (!$item || $item['location'] !== $location) {
                throw new Exception('Menu item not found');
            }

            // Update children to point to the deleted item's parent
            $sql = "UPDATE {$this->table} SET parent_id = ? WHERE parent_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$item['parent_id'], $id]);

            // Delete the item
            $this->delete($id);

            // Reorder remaining items
            $this->reorderItems($location);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Reorder menu items
     */
    private function reorderItems($location) {
        $sql = "SELECT id FROM {$this->table} 
                WHERE location = ? AND status = 'active' 
                ORDER BY `order` ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$location]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $order => $item) {
            $this->update($item['id'], ['order' => $order]);
        }
    }

    /**
     * Move menu item
     */
    public function moveItem($id, $newParentId = null, $newOrder = null) {
        $item = $this->find($id);
        if (!$item) {
            throw new Exception('Menu item not found');
        }

        $updates = [];
        if ($newParentId !== null) {
            $updates['parent_id'] = $newParentId;
        }
        if ($newOrder !== null) {
            $updates['order'] = $newOrder;
        }

        if (!empty($updates)) {
            $this->update($id, $updates);
            $this->reorderItems($item['location']);
        }

        return true;
    }

    /**
     * Get available menu locations
     */
    public function getLocations() {
        return [
            'main_menu' => 'Main Navigation',
            'footer_menu' => 'Footer Navigation',
            'social_menu' => 'Social Links'
        ];
    }

    /**
     * Get menu item types
     */
    public function getItemTypes() {
        return [
            'custom' => [
                'label' => 'Custom Link',
                'fields' => ['url']
            ],
            'page' => [
                'label' => 'Page',
                'fields' => ['page_id']
            ],
            'category' => [
                'label' => 'Category',
                'fields' => ['category_id']
            ],
            'post' => [
                'label' => 'Post',
                'fields' => ['post_id']
            ]
        ];
    }

    /**
     * Render menu
     */
    public function render($location, $options = []) {
        $items = $this->getByLocation($location);
        $options = array_merge([
            'class' => '',
            'item_class' => '',
            'link_class' => '',
            'depth' => 0
        ], $options);

        if (empty($items)) {
            return '';
        }

        $output = '<ul class="' . $options['class'] . '">';
        foreach ($items as $item) {
            $output .= $this->renderMenuItem($item, $options);
        }
        $output .= '</ul>';

        return $output;
    }

    /**
     * Render menu item
     */
    private function renderMenuItem($item, $options, $level = 0) {
        if ($options['depth'] > 0 && $level >= $options['depth']) {
            return '';
        }

        $hasChildren = !empty($item['children']);
        $itemClass = $options['item_class'];
        if ($hasChildren) {
            $itemClass .= ' has-children';
        }

        $output = '<li class="' . $itemClass . '">';
        $output .= '<a href="' . htmlspecialchars($item['url']) . '" ';
        $output .= 'class="' . $options['link_class'] . '" ';
        $output .= 'target="' . htmlspecialchars($item['target']) . '">';
        $output .= htmlspecialchars($item['label']);
        $output .= '</a>';

        if ($hasChildren) {
            $output .= '<ul class="submenu">';
            foreach ($item['children'] as $child) {
                $output .= $this->renderMenuItem($child, $options, $level + 1);
            }
            $output .= '</ul>';
        }

        $output .= '</li>';

        return $output;
    }
}