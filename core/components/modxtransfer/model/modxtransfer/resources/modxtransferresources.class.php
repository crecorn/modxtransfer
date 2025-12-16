<?php
/**
 * ModxTransfer Resources Handler
 * 
 * Handles export and import of MODX resources with TV values
 *
 * @package modxtransfer
 */
class ModxTransferResources {
    /** @var ModxTransfer $transfer */
    public $transfer;
    
    /** @var modX $modx */
    public $modx;

    public function __construct(ModxTransfer &$transfer) {
        $this->transfer =& $transfer;
        $this->modx =& $transfer->modx;
    }

    /**
     * Export resources to array
     * 
     * @param array $options Export options
     * @return array
     */
    public function export(array $options = []) {
        $defaults = [
            'parents' => [],
            'depth' => 10,
            'templates' => [],
            'ids' => [],
            'excludeIds' => [],
            'includeTVs' => true,
            'includeContent' => true,
            'includeUnpublished' => false,
            'sortby' => 'menuindex',
            'sortdir' => 'ASC',
            'limit' => 0,
            'delete' => 0,
            'confirmDelete' => '',
        ];
        $options = array_merge($defaults, $options);
        
        // Build query
        $c = $this->modx->newQuery('modResource');
        $c->where(['deleted' => 0]);
        
        // Filter by published status
        if (!$options['includeUnpublished']) {
            $c->where(['published' => 1]);
        }
        
        // Filter by parents
        if (!empty($options['parents'])) {
            $parentIds = is_array($options['parents']) ? $options['parents'] : [$options['parents']];
            $allIds = [];
            foreach ($parentIds as $parentId) {
                $allIds[] = $parentId;
                $childIds = $this->modx->getChildIds($parentId, $options['depth'], ['context' => 'web']);
                $allIds = array_merge($allIds, $childIds);
            }
            if (!empty($allIds)) {
                $c->where(['id:IN' => array_unique($allIds)]);
            }
        }
        
        // Filter by specific IDs
        if (!empty($options['ids'])) {
            $c->where(['id:IN' => $options['ids']]);
        }
        
        // Exclude specific IDs
        if (!empty($options['excludeIds'])) {
            $c->where(['id:NOT IN' => $options['excludeIds']]);
        }
        
        // Filter by templates
        if (!empty($options['templates'])) {
            $templateIds = [];
            foreach ($options['templates'] as $tplName) {
                $tpl = $this->modx->getObject('modTemplate', ['templatename' => trim($tplName)]);
                if ($tpl) {
                    $templateIds[] = $tpl->get('id');
                }
            }
            if (!empty($templateIds)) {
                $c->where(['template:IN' => $templateIds]);
            }
        }
        
        // Sorting
        $c->sortby($options['sortby'], $options['sortdir']);
        
        // Limit
        if ($options['limit'] > 0) {
            $c->limit($options['limit']);
        }
        
        // Build result
        $resources = [];
        $deleteIds = [];
        
        foreach ($this->modx->getIterator('modResource', $c) as $resource) {
            $resourceData = $this->exportResource($resource, $options);
            $resources[] = $resourceData;
            $deleteIds[] = $resource->get('id');
        }
        
        // Handle deletion if requested
        $deleted = false;
        if ($options['delete'] > 0 && $options['confirmDelete'] === 'YES-DELETE' && !empty($deleteIds)) {
            $deleted = $this->deleteResources($deleteIds, $options['delete']);
        }
        
        return [
            'meta' => [
                'project' => $this->modx->getOption('site_name'),
                'description' => 'MODX Resources Export',
                'created' => date('c'),
                'count' => count($resources),
                'filters' => [
                    'parents' => $options['parents'],
                    'templates' => $options['templates'],
                    'includeUnpublished' => $options['includeUnpublished'],
                    'includeTVs' => $options['includeTVs'],
                ],
                'deleted_after_export' => $deleted,
            ],
            'resources' => $resources,
        ];
    }

    /**
     * Export a single resource
     */
    protected function exportResource($resource, array $options) {
        // Get template name
        $templateName = '';
        if ($resource->get('template') > 0) {
            $tpl = $this->modx->getObject('modTemplate', $resource->get('template'));
            if ($tpl) {
                $templateName = $tpl->get('templatename');
            }
        }
        
        // Get parent path for reference
        $parentPath = '';
        if ($resource->get('parent') > 0) {
            $parent = $this->modx->getObject('modResource', $resource->get('parent'));
            if ($parent) {
                $parentPath = $parent->get('uri') ?: $parent->get('alias');
            }
        }
        
        $data = [
            'original_id' => $resource->get('id'),
            'pagetitle' => $resource->get('pagetitle'),
            'longtitle' => $resource->get('longtitle'),
            'alias' => $resource->get('alias'),
            'description' => $resource->get('description'),
            'introtext' => $resource->get('introtext'),
            'menutitle' => $resource->get('menutitle'),
            'template' => $templateName,
            'parent' => $resource->get('parent'),
            'parent_path' => $parentPath,
            'published' => $resource->get('published'),
            'hidemenu' => $resource->get('hidemenu'),
            'menuindex' => $resource->get('menuindex'),
            'searchable' => $resource->get('searchable'),
            'cacheable' => $resource->get('cacheable'),
            'richtext' => $resource->get('richtext'),
            'isfolder' => $resource->get('isfolder'),
            'class_key' => $resource->get('class_key'),
        ];
        
        // Include content if requested
        if ($options['includeContent']) {
            $data['content'] = $resource->get('content');
        }
        
        // Add publish/unpublish dates if set
        if ($resource->get('pub_date')) {
            $data['pub_date'] = date('c', $resource->get('pub_date'));
        }
        if ($resource->get('unpub_date')) {
            $data['unpub_date'] = date('c', $resource->get('unpub_date'));
        }
        
        // Include TVs if requested
        if ($options['includeTVs']) {
            $tvs = $this->getResourceTVs($resource);
            if (!empty($tvs)) {
                $data['tvs'] = $tvs;
            }
        }
        
        return $data;
    }

    /**
     * Get TV values for a resource
     */
    protected function getResourceTVs($resource) {
        $tvs = [];
        
        // Get all TVs assigned to this resource's template
        $c = $this->modx->newQuery('modTemplateVar');
        $c->innerJoin('modTemplateVarTemplate', 'tvt', 'tvt.tmplvarid = modTemplateVar.id');
        $c->where(['tvt.templateid' => $resource->get('template')]);
        
        foreach ($this->modx->getIterator('modTemplateVar', $c) as $tv) {
            $value = $tv->getValue($resource->get('id'));
            
            // Skip empty values
            if ($value === '' || $value === null) {
                continue;
            }
            
            // Try to decode MIGX/JSON data
            if ($this->isJson($value)) {
                $decoded = json_decode($value, true);
                if ($decoded !== null) {
                    $value = $decoded;
                }
            }
            
            $tvs[$tv->get('name')] = $value;
        }
        
        return $tvs;
    }

    /**
     * Check if a string is valid JSON
     */
    protected function isJson($string) {
        if (!is_string($string)) return false;
        $string = trim($string);
        return (
            ($string[0] ?? '') === '[' || 
            ($string[0] ?? '') === '{'
        ) && json_decode($string) !== null;
    }

    /**
     * Delete resources after export
     * 
     * @param array $ids Resource IDs to delete
     * @param int $mode 1=soft delete, 2=hard delete
     * @return bool
     */
    protected function deleteResources(array $ids, int $mode) {
        foreach ($ids as $id) {
            $resource = $this->modx->getObject('modResource', $id);
            if (!$resource) continue;
            
            if ($mode === 2) {
                // Hard delete
                $resource->remove();
            } else {
                // Soft delete (move to trash)
                $resource->set('deleted', 1);
                $resource->set('deletedby', $this->modx->user ? $this->modx->user->get('id') : 0);
                $resource->set('deletedon', time());
                $resource->save();
            }
        }
        
        return true;
    }

    /**
     * Import resources from array
     * 
     * @param array $data Import data
     * @param array $options Import options
     * @return array
     */
    public function import(array $data, array $options = []) {
        $defaults = [
            'mode' => 'preview',
            'parentId' => 0,
            'defaultTemplate' => '',
        ];
        $options = array_merge($defaults, $options);
        
        $results = [
            'created' => 0,
            'skipped' => 0,
            'errors' => 0,
            'preview' => $options['mode'] === 'preview',
            'items' => [],
        ];
        
        if (empty($data['resources'])) {
            $this->transfer->addError('No resources found in import data');
            return $results;
        }
        
        // Map original IDs to new IDs for parent resolution
        $idMap = [];
        
        // First pass: create resources without proper parent hierarchy
        foreach ($data['resources'] as $resourceData) {
            $result = $this->importResource($resourceData, $options, $idMap);
            $results['items'][] = $result;
            
            if ($result['action'] === 'created' || $result['action'] === 'will_create') {
                $results['created']++;
                if (isset($result['new_id'])) {
                    $idMap[$resourceData['original_id']] = $result['new_id'];
                }
            } elseif ($result['action'] === 'error') {
                $results['errors']++;
            } else {
                $results['skipped']++;
            }
        }
        
        // Second pass: fix parent relationships if executing
        if ($options['mode'] === 'execute' && !empty($idMap)) {
            $this->fixParentRelationships($data['resources'], $idMap, $options['parentId']);
        }
        
        return $results;
    }

    /**
     * Import a single resource
     */
    protected function importResource(array $resourceData, array $options, array &$idMap) {
        $result = [
            'pagetitle' => $resourceData['pagetitle'] ?? 'Untitled',
            'alias' => $resourceData['alias'] ?? '',
            'action' => 'will_create',
        ];
        
        // Validate required fields
        if (empty($resourceData['pagetitle'])) {
            $result['action'] = 'error';
            $result['message'] = 'Missing pagetitle';
            return $result;
        }
        
        // Resolve template
        $templateId = 0;
        if (!empty($resourceData['template'])) {
            $tpl = $this->modx->getObject('modTemplate', ['templatename' => $resourceData['template']]);
            if ($tpl) {
                $templateId = $tpl->get('id');
            }
        }
        if ($templateId === 0 && !empty($options['defaultTemplate'])) {
            $templateId = (int) $options['defaultTemplate'];
        }
        
        // Calculate parent ID
        $parentId = $options['parentId'];
        if (!empty($resourceData['parent']) && isset($idMap[$resourceData['parent']])) {
            $parentId = $idMap[$resourceData['parent']];
        }
        
        if ($options['mode'] === 'execute') {
            $resource = $this->modx->newObject('modResource');
            
            // Set fields
            $resource->set('pagetitle', $resourceData['pagetitle']);
            $resource->set('longtitle', $resourceData['longtitle'] ?? '');
            $resource->set('alias', $resourceData['alias'] ?? '');
            $resource->set('description', $resourceData['description'] ?? '');
            $resource->set('introtext', $resourceData['introtext'] ?? '');
            $resource->set('menutitle', $resourceData['menutitle'] ?? '');
            $resource->set('content', $resourceData['content'] ?? '');
            $resource->set('template', $templateId);
            $resource->set('parent', $parentId);
            $resource->set('published', 0); // Always import unpublished for review
            $resource->set('hidemenu', $resourceData['hidemenu'] ?? 0);
            $resource->set('menuindex', $resourceData['menuindex'] ?? 0);
            $resource->set('searchable', $resourceData['searchable'] ?? 1);
            $resource->set('cacheable', $resourceData['cacheable'] ?? 1);
            $resource->set('richtext', $resourceData['richtext'] ?? 1);
            $resource->set('isfolder', $resourceData['isfolder'] ?? 0);
            $resource->set('class_key', $resourceData['class_key'] ?? 'modDocument');
            $resource->set('context_key', 'web');
            
            // Set creator
            if ($this->modx->user) {
                $resource->set('createdby', $this->modx->user->get('id'));
            }
            
            if ($resource->save()) {
                $result['action'] = 'created';
                $result['new_id'] = $resource->get('id');
                
                // Import TVs
                if (!empty($resourceData['tvs'])) {
                    $this->importResourceTVs($resource, $resourceData['tvs']);
                }
            } else {
                $result['action'] = 'error';
                $result['message'] = 'Failed to save resource';
            }
        }
        
        return $result;
    }

    /**
     * Import TV values for a resource
     */
    protected function importResourceTVs($resource, array $tvs) {
        foreach ($tvs as $tvName => $value) {
            $tv = $this->modx->getObject('modTemplateVar', ['name' => $tvName]);
            if (!$tv) continue;
            
            // Encode arrays/objects back to JSON
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_SLASHES);
            }
            
            $tv->setValue($resource->get('id'), $value);
            $tv->save();
        }
    }

    /**
     * Fix parent relationships after all resources are created
     */
    protected function fixParentRelationships(array $resources, array $idMap, int $baseParentId) {
        foreach ($resources as $resourceData) {
            if (empty($resourceData['original_id'])) continue;
            if (!isset($idMap[$resourceData['original_id']])) continue;
            
            $newId = $idMap[$resourceData['original_id']];
            $resource = $this->modx->getObject('modResource', $newId);
            if (!$resource) continue;
            
            // Determine correct parent
            $correctParent = $baseParentId;
            if (!empty($resourceData['parent']) && isset($idMap[$resourceData['parent']])) {
                $correctParent = $idMap[$resourceData['parent']];
            }
            
            if ($resource->get('parent') !== $correctParent) {
                $resource->set('parent', $correctParent);
                $resource->save();
            }
        }
    }
}
