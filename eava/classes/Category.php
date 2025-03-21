<?php
require_once __DIR__ . '/Model.php';

class Category extends Model {
    protected $table = 'categories';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'module'
    ];

    /**
     * Get category tree structure
     */
    public function getTree($module = null) {
        try {
            $conditions = ['parent_id' => null];
            if ($module) {
                $conditions['module'] = $module;
            }
            
            $categories = $this->findBy(array_keys($conditions)[0], array_values($conditions)[0]);
            return $this->buildTree($categories);
        } catch (Exception $e) {
            error_log("Get Category Tree Error: " . $e->getMessage());
            throw new Exception("Failed to get category tree");
        }
    }

    /**
     * Build hierarchical tree structure
     */
    private function buildTree($categories) {
        $tree = [];
        
        foreach ($categories as $category) {
            $category['children'] = $this->getChildren($category['id']);
            $tree[] = $category;
        }
        
        return $tree;
    }

    /**
     * Get child categories
     */
    public function getChildren($parentId) {
        try {
            $children = $this->findBy('parent_id', $parentId);
            
            foreach ($children as &$child) {
                $child['children'] = $this->getChildren($child['id']);
            }
            
            return $children;
        } catch (Exception $e) {
            error_log("Get Children Error: " . $e->getMessage());
            throw new Exception("Failed to get child categories");
        }
    }

    /**
     * Get parent category
     */
    public function getParent($categoryId) {
        try {
            $category = $this->find($categoryId);
            if ($category && $category['parent_id']) {
                return $this->find($category['parent_id']);
            }
            return null;
        } catch (Exception $e) {
            error_log("Get Parent Error: " . $e->getMessage());
            throw new Exception("Failed to get parent category");
        }
    }

    /**
     * Get category path (breadcrumb)
     */
    public function getPath($categoryId) {
        try {
            $path = [];
            $current = $this->find($categoryId);
            
            while ($current) {
                array_unshift($path, $current);
                $current = $current['parent_id'] ? $this->find($current['parent_id']) : null;
            }
            
            return $path;
        } catch (Exception $e) {
            error_log("Get Path Error: " . $e->getMessage());
            throw new Exception("Failed to get category path");
        }
    }

    /**
     * Create a new category
     */
    public function createCategory($data) {
        try {
            if (empty($data['slug'])) {
                $data['slug'] = Utility::generateSlug($data['name']);
            }

            // Verify unique slug
            if ($this->exists(['slug' => $data['slug']])) {
                throw new Exception("Category with this slug already exists");
            }

            return $this->create($data);
        } catch (Exception $e) {
            error_log("Create Category Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Update a category
     */
    public function updateCategory($id, $data) {
        try {
            if (!empty($data['name'])) {
                $data['slug'] = Utility::generateSlug($data['name']);
            }

            // Verify unique slug
            $existing = $this->findOneBy('slug', $data['slug']);
            if ($existing && $existing['id'] != $id) {
                throw new Exception("Category with this slug already exists");
            }

            // Prevent category from becoming its own parent
            if (!empty($data['parent_id']) && $data['parent_id'] == $id) {
                throw new Exception("Category cannot be its own parent");
            }

            return $this->update($id, $data);
        } catch (Exception $e) {
            error_log("Update Category Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Delete a category and handle its children
     */
    public function deleteCategory($id, $moveChildrenTo = null) {
        try {
            $this->db->beginTransaction();

            // Get children
            $children = $this->findBy('parent_id', $id);

            // Handle children
            if (!empty($children)) {
                if ($moveChildrenTo) {
                    // Move children to specified category
                    foreach ($children as $child) {
                        $this->update($child['id'], ['parent_id' => $moveChildrenTo]);
                    }
                } else {
                    // Make children top-level categories
                    foreach ($children as $child) {
                        $this->update($child['id'], ['parent_id' => null]);
                    }
                }
            }

            // Delete the category
            $this->delete($id);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Delete Category Error: " . $e->getMessage());
            throw new Exception("Failed to delete category");
        }
    }

    /**
     * Get categories by module
     */
    public function getByModule($module, $parentId = null) {
        try {
            $conditions = ['module' => $module];
            if ($parentId !== null) {
                $conditions['parent_id'] = $parentId;
            }
            
            $categories = $this->findBy(array_keys($conditions)[0], array_values($conditions)[0]);
            return $categories;
        } catch (Exception $e) {
            error_log("Get Categories By Module Error: " . $e->getMessage());
            throw new Exception("Failed to get categories by module");
        }
    }

    /**
     * Get category by slug
     */
    public function getBySlug($slug) {
        try {
            return $this->findOneBy('slug', $slug);
        } catch (Exception $e) {
            error_log("Get Category By Slug Error: " . $e->getMessage());
            throw new Exception("Failed to get category by slug");
        }
    }

    /**
     * Check if category has children
     */
    public function hasChildren($id) {
        try {
            return $this->count(['parent_id' => $id]) > 0;
        } catch (Exception $e) {
            error_log("Has Children Check Error: " . $e->getMessage());
            throw new Exception("Failed to check for children");
        }
    }

    /**
     * Get category select options (for forms)
     */
    public function getSelectOptions($module = null, $excludeId = null, $parentId = null, $level = 0) {
        try {
            $conditions = [];
            if ($module) {
                $conditions['module'] = $module;
            }
            if ($parentId !== null) {
                $conditions['parent_id'] = $parentId;
            }

            $categories = $this->findBy(array_keys($conditions)[0], array_values($conditions)[0]);
            $options = [];

            foreach ($categories as $category) {
                if ($excludeId && $category['id'] == $excludeId) {
                    continue;
                }

                $prefix = str_repeat('─ ', $level);
                $options[$category['id']] = $prefix . $category['name'];

                // Get children options
                $childOptions = $this->getSelectOptions($module, $excludeId, $category['id'], $level + 1);
                $options = array_merge($options, $childOptions);
            }

            return $options;
        } catch (Exception $e) {
            error_log("Get Select Options Error: " . $e->getMessage());
            throw new Exception("Failed to get category options");
        }
    }
}