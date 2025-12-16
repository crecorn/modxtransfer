<?php
/**
 * ModxTransfer Elements Handler
 * 
 * Handles export and import of MODX elements (categories, templates, chunks, TVs, snippets, plugins)
 *
 * @package modxtransfer
 */
class ModxTransferElements {
    /** @var ModxTransfer $transfer */
    public $transfer;
    
    /** @var modX $modx */
    public $modx;

    /**
     * Constructor
     *
     * @param ModxTransfer $transfer
     */
    public function __construct(ModxTransfer &$transfer) {
        $this->transfer =& $transfer;
        $this->modx =& $transfer->modx;
    }

    /**
     * Export elements to array
     *
     * @param array $options
     * @return array
     */
    public function export(array $options = []) {
        $defaults = [
            'elementTypes' => ['categories', 'templates', 'chunks', 'tvs', 'snippets', 'plugins'],
            'category' => '',
        ];
        $options = array_merge($defaults, $options);
        
        $data = [
            'meta' => [
                'project' => $this->modx->getOption('site_name'),
                'description' => 'MODX Elements Export',
                'created' => date('c'),
                'exported_types' => $options['elementTypes'],
                'category_filter' => $options['category'],
            ],
        ];

        // Export each element type
        if (in_array('categories', $options['elementTypes'])) {
            $data['categories'] = $this->exportCategories($options['category']);
        }
        if (in_array('templates', $options['elementTypes'])) {
            $data['templates'] = $this->exportTemplates($options['category']);
        }
        if (in_array('chunks', $options['elementTypes'])) {
            $data['chunks'] = $this->exportChunks($options['category']);
        }
        if (in_array('tvs', $options['elementTypes'])) {
            $data['tvs'] = $this->exportTVs($options['category']);
        }
        if (in_array('snippets', $options['elementTypes'])) {
            $data['snippets'] = $this->exportSnippets($options['category']);
        }
        if (in_array('plugins', $options['elementTypes'])) {
            $data['plugins'] = $this->exportPlugins($options['category']);
        }

        return $data;
    }

    /**
     * Export categories
     *
     * @param string $categoryFilter
     * @return array
     */
    protected function exportCategories($categoryFilter = '') {
        $categories = [];
        $c = $this->modx->newQuery('modCategory');
        
        if ($categoryFilter) {
            $cat = $this->modx->getObject('modCategory', ['category' => $categoryFilter]);
            if ($cat) {
                $c->where([
                    'id' => $cat->get('id'),
                    'OR:parent:=' => $cat->get('id'),
                ]);
            }
        }
        
        $c->sortby('parent', 'ASC');
        $c->sortby('category', 'ASC');
        
        foreach ($this->modx->getIterator('modCategory', $c) as $cat) {
            $parentName = '';
            if ($cat->get('parent') > 0) {
                $parent = $this->modx->getObject('modCategory', $cat->get('parent'));
                if ($parent) {
                    $parentName = $parent->get('category');
                }
            }
            
            $categories[] = [
                'name' => $cat->get('category'),
                'parent' => $parentName,
            ];
        }
        
        return $categories;
    }

    /**
     * Export templates
     *
     * @param string $categoryFilter
     * @return array
     */
    protected function exportTemplates($categoryFilter = '') {
        $templates = [];
        $c = $this->modx->newQuery('modTemplate');
        
        if ($categoryFilter) {
            $categoryIds = $this->getCategoryIds($categoryFilter);
            if (!empty($categoryIds)) {
                $c->where(['category:IN' => $categoryIds]);
            }
        }
        
        $c->sortby('templatename', 'ASC');
        
        foreach ($this->modx->getIterator('modTemplate', $c) as $tpl) {
            $categoryName = '';
            if ($tpl->get('category') > 0) {
                $cat = $this->modx->getObject('modCategory', $tpl->get('category'));
                if ($cat) $categoryName = $cat->get('category');
            }
            
            $templates[] = [
                'name' => $tpl->get('templatename'),
                'description' => $tpl->get('description'),
                'category' => $categoryName,
                'icon' => $tpl->get('icon'),
                'content' => $tpl->get('content'),
            ];
        }
        
        return $templates;
    }

    /**
     * Export chunks
     *
     * @param string $categoryFilter
     * @return array
     */
    protected function exportChunks($categoryFilter = '') {
        $chunks = [];
        $c = $this->modx->newQuery('modChunk');
        
        if ($categoryFilter) {
            $categoryIds = $this->getCategoryIds($categoryFilter);
            if (!empty($categoryIds)) {
                $c->where(['category:IN' => $categoryIds]);
            }
        }
        
        $c->sortby('name', 'ASC');
        
        foreach ($this->modx->getIterator('modChunk', $c) as $chunk) {
            $categoryName = '';
            if ($chunk->get('category') > 0) {
                $cat = $this->modx->getObject('modCategory', $chunk->get('category'));
                if ($cat) $categoryName = $cat->get('category');
            }
            
            $chunks[] = [
                'name' => $chunk->get('name'),
                'description' => $chunk->get('description'),
                'category' => $categoryName,
                'content' => $chunk->get('snippet'),
            ];
        }
        
        return $chunks;
    }

    /**
     * Export TVs with template assignments
     *
     * @param string $categoryFilter
     * @return array
     */
    protected function exportTVs($categoryFilter = '') {
        $tvs = [];
        $c = $this->modx->newQuery('modTemplateVar');
        
        if ($categoryFilter) {
            $categoryIds = $this->getCategoryIds($categoryFilter);
            if (!empty($categoryIds)) {
                $c->where(['category:IN' => $categoryIds]);
            }
        }
        
        $c->sortby('name', 'ASC');
        
        foreach ($this->modx->getIterator('modTemplateVar', $c) as $tv) {
            $categoryName = '';
            if ($tv->get('category') > 0) {
                $cat = $this->modx->getObject('modCategory', $tv->get('category'));
                if ($cat) $categoryName = $cat->get('category');
            }
            
            // Get template assignments
            $templateNames = [];
            $tvts = $this->modx->getCollection('modTemplateVarTemplate', ['tmplvarid' => $tv->get('id')]);
            foreach ($tvts as $tvt) {
                $tpl = $this->modx->getObject('modTemplate', $tvt->get('templateid'));
                if ($tpl) {
                    $templateNames[] = $tpl->get('templatename');
                }
            }
            
            $tvs[] = [
                'name' => $tv->get('name'),
                'caption' => $tv->get('caption'),
                'description' => $tv->get('description'),
                'type' => $tv->get('type'),
                'category' => $categoryName,
                'templates' => $templateNames,
                'default' => $tv->get('default_text'),
                'elements' => $tv->get('elements'),
                'display' => $tv->get('display'),
                'input_properties' => $tv->get('input_properties'),
                'output_properties' => $tv->get('output_properties'),
                'rank' => $tv->get('rank'),
            ];
        }
        
        return $tvs;
    }

    /**
     * Export snippets
     *
     * @param string $categoryFilter
     * @return array
     */
    protected function exportSnippets($categoryFilter = '') {
        $snippets = [];
        $c = $this->modx->newQuery('modSnippet');
        
        if ($categoryFilter) {
            $categoryIds = $this->getCategoryIds($categoryFilter);
            if (!empty($categoryIds)) {
                $c->where(['category:IN' => $categoryIds]);
            }
        }
        
        $c->sortby('name', 'ASC');
        
        foreach ($this->modx->getIterator('modSnippet', $c) as $snippet) {
            $categoryName = '';
            if ($snippet->get('category') > 0) {
                $cat = $this->modx->getObject('modCategory', $snippet->get('category'));
                if ($cat) $categoryName = $cat->get('category');
            }
            
            $snippets[] = [
                'name' => $snippet->get('name'),
                'description' => $snippet->get('description'),
                'category' => $categoryName,
                'content' => $snippet->get('snippet'),
                'properties' => $snippet->get('properties'),
            ];
        }
        
        return $snippets;
    }

    /**
     * Export plugins with events
     *
     * @param string $categoryFilter
     * @return array
     */
    protected function exportPlugins($categoryFilter = '') {
        $plugins = [];
        $c = $this->modx->newQuery('modPlugin');
        
        if ($categoryFilter) {
            $categoryIds = $this->getCategoryIds($categoryFilter);
            if (!empty($categoryIds)) {
                $c->where(['category:IN' => $categoryIds]);
            }
        }
        
        $c->sortby('name', 'ASC');
        
        foreach ($this->modx->getIterator('modPlugin', $c) as $plugin) {
            $categoryName = '';
            if ($plugin->get('category') > 0) {
                $cat = $this->modx->getObject('modCategory', $plugin->get('category'));
                if ($cat) $categoryName = $cat->get('category');
            }
            
            // Get events
            $events = [];
            $pluginEvents = $this->modx->getCollection('modPluginEvent', ['pluginid' => $plugin->get('id')]);
            foreach ($pluginEvents as $pe) {
                $events[] = $pe->get('event');
            }
            
            $plugins[] = [
                'name' => $plugin->get('name'),
                'description' => $plugin->get('description'),
                'category' => $categoryName,
                'content' => $plugin->get('plugincode'),
                'disabled' => (bool) $plugin->get('disabled'),
                'events' => $events,
                'properties' => $plugin->get('properties'),
            ];
        }
        
        return $plugins;
    }

    /**
     * Get category IDs including children
     *
     * @param string $categoryName
     * @return array
     */
    protected function getCategoryIds($categoryName) {
        $ids = [];
        $cat = $this->modx->getObject('modCategory', ['category' => $categoryName]);
        if ($cat) {
            $ids[] = $cat->get('id');
            // Get children
            $children = $this->modx->getCollection('modCategory', ['parent' => $cat->get('id')]);
            foreach ($children as $child) {
                $ids[] = $child->get('id');
            }
        }
        return $ids;
    }

    /**
     * Import elements from array
     *
     * @param array $data
     * @param array $options
     * @return array
     */
    public function import(array $data, array $options = []) {
        $defaults = [
            'mode' => 'preview',
            'update' => false,
        ];
        $options = array_merge($defaults, $options);
        
        $results = [
            'categories' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
            'templates' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
            'chunks' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
            'tvs' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
            'snippets' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
            'plugins' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
            'preview' => $options['mode'] === 'preview',
            'details' => [],
        ];

        // Import in order: categories first for proper assignment
        if (!empty($data['categories'])) {
            $results['categories'] = $this->importCategories($data['categories'], $options);
        }
        if (!empty($data['templates'])) {
            $results['templates'] = $this->importTemplates($data['templates'], $options);
        }
        if (!empty($data['chunks'])) {
            $results['chunks'] = $this->importChunks($data['chunks'], $options);
        }
        if (!empty($data['tvs'])) {
            $results['tvs'] = $this->importTVs($data['tvs'], $options);
        }
        if (!empty($data['snippets'])) {
            $results['snippets'] = $this->importSnippets($data['snippets'], $options);
        }
        if (!empty($data['plugins'])) {
            $results['plugins'] = $this->importPlugins($data['plugins'], $options);
        }

        return $results;
    }

    /**
     * Import categories
     *
     * @param array $categories
     * @param array $options
     * @return array
     */
    protected function importCategories($categories, $options) {
        $results = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'items' => []];
        
        foreach ($categories as $catData) {
            $existing = $this->modx->getObject('modCategory', ['category' => $catData['name']]);
            
            if ($existing) {
                if ($options['update'] && $options['mode'] === 'execute') {
                    $results['updated']++;
                } else {
                    $results['skipped']++;
                }
                $results['items'][] = ['name' => $catData['name'], 'action' => $existing ? 'exists' : 'create'];
                continue;
            }
            
            if ($options['mode'] === 'execute') {
                $cat = $this->modx->newObject('modCategory');
                $cat->set('category', $catData['name']);
                
                // Handle parent
                if (!empty($catData['parent'])) {
                    $parent = $this->modx->getObject('modCategory', ['category' => $catData['parent']]);
                    if ($parent) {
                        $cat->set('parent', $parent->get('id'));
                    }
                }
                
                if ($cat->save()) {
                    $results['created']++;
                    $results['items'][] = ['name' => $catData['name'], 'action' => 'created'];
                }
            } else {
                $results['created']++;
                $results['items'][] = ['name' => $catData['name'], 'action' => 'will_create'];
            }
        }
        
        return $results;
    }

    /**
     * Import templates
     *
     * @param array $templates
     * @param array $options
     * @return array
     */
    protected function importTemplates($templates, $options) {
        $results = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'items' => []];
        
        foreach ($templates as $tplData) {
            $existing = $this->modx->getObject('modTemplate', ['templatename' => $tplData['name']]);
            
            if ($existing) {
                if ($options['update'] && $options['mode'] === 'execute') {
                    $existing->set('description', $tplData['description'] ?? '');
                    $existing->set('content', $tplData['content'] ?? '');
                    if (!empty($tplData['category'])) {
                        $cat = $this->modx->getObject('modCategory', ['category' => $tplData['category']]);
                        if ($cat) $existing->set('category', $cat->get('id'));
                    }
                    $existing->save();
                    $results['updated']++;
                    $results['items'][] = ['name' => $tplData['name'], 'action' => 'updated'];
                } else {
                    $results['skipped']++;
                    $results['items'][] = ['name' => $tplData['name'], 'action' => 'skipped'];
                }
                continue;
            }
            
            if ($options['mode'] === 'execute') {
                $tpl = $this->modx->newObject('modTemplate');
                $tpl->set('templatename', $tplData['name']);
                $tpl->set('description', $tplData['description'] ?? '');
                $tpl->set('content', $tplData['content'] ?? '');
                $tpl->set('icon', $tplData['icon'] ?? '');
                
                if (!empty($tplData['category'])) {
                    $cat = $this->modx->getObject('modCategory', ['category' => $tplData['category']]);
                    if ($cat) $tpl->set('category', $cat->get('id'));
                }
                
                if ($tpl->save()) {
                    $results['created']++;
                    $results['items'][] = ['name' => $tplData['name'], 'action' => 'created'];
                }
            } else {
                $results['created']++;
                $results['items'][] = ['name' => $tplData['name'], 'action' => 'will_create'];
            }
        }
        
        return $results;
    }

    /**
     * Import chunks
     *
     * @param array $chunks
     * @param array $options
     * @return array
     */
    protected function importChunks($chunks, $options) {
        $results = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'items' => []];
        
        foreach ($chunks as $chunkData) {
            $existing = $this->modx->getObject('modChunk', ['name' => $chunkData['name']]);
            
            if ($existing) {
                if ($options['update'] && $options['mode'] === 'execute') {
                    $existing->set('description', $chunkData['description'] ?? '');
                    $existing->set('snippet', $chunkData['content'] ?? '');
                    if (!empty($chunkData['category'])) {
                        $cat = $this->modx->getObject('modCategory', ['category' => $chunkData['category']]);
                        if ($cat) $existing->set('category', $cat->get('id'));
                    }
                    $existing->save();
                    $results['updated']++;
                    $results['items'][] = ['name' => $chunkData['name'], 'action' => 'updated'];
                } else {
                    $results['skipped']++;
                    $results['items'][] = ['name' => $chunkData['name'], 'action' => 'skipped'];
                }
                continue;
            }
            
            if ($options['mode'] === 'execute') {
                $chunk = $this->modx->newObject('modChunk');
                $chunk->set('name', $chunkData['name']);
                $chunk->set('description', $chunkData['description'] ?? '');
                $chunk->set('snippet', $chunkData['content'] ?? '');
                
                if (!empty($chunkData['category'])) {
                    $cat = $this->modx->getObject('modCategory', ['category' => $chunkData['category']]);
                    if ($cat) $chunk->set('category', $cat->get('id'));
                }
                
                if ($chunk->save()) {
                    $results['created']++;
                    $results['items'][] = ['name' => $chunkData['name'], 'action' => 'created'];
                }
            } else {
                $results['created']++;
                $results['items'][] = ['name' => $chunkData['name'], 'action' => 'will_create'];
            }
        }
        
        return $results;
    }

    /**
     * Import TVs
     *
     * @param array $tvs
     * @param array $options
     * @return array
     */
    protected function importTVs($tvs, $options) {
        $results = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'items' => []];
        
        foreach ($tvs as $tvData) {
            $existing = $this->modx->getObject('modTemplateVar', ['name' => $tvData['name']]);
            
            if ($existing) {
                if ($options['update'] && $options['mode'] === 'execute') {
                    $existing->set('caption', $tvData['caption'] ?? '');
                    $existing->set('description', $tvData['description'] ?? '');
                    $existing->set('type', $tvData['type'] ?? 'text');
                    $existing->set('default_text', $tvData['default'] ?? '');
                    $existing->set('elements', $tvData['elements'] ?? '');
                    $existing->set('display', $tvData['display'] ?? 'default');
                    if (isset($tvData['input_properties'])) {
                        $existing->set('input_properties', $tvData['input_properties']);
                    }
                    if (isset($tvData['output_properties'])) {
                        $existing->set('output_properties', $tvData['output_properties']);
                    }
                    if (!empty($tvData['category'])) {
                        $cat = $this->modx->getObject('modCategory', ['category' => $tvData['category']]);
                        if ($cat) $existing->set('category', $cat->get('id'));
                    }
                    $existing->save();
                    
                    // Update template assignments
                    $this->assignTVToTemplates($existing, $tvData['templates'] ?? []);
                    
                    $results['updated']++;
                    $results['items'][] = ['name' => $tvData['name'], 'action' => 'updated'];
                } else {
                    $results['skipped']++;
                    $results['items'][] = ['name' => $tvData['name'], 'action' => 'skipped'];
                }
                continue;
            }
            
            if ($options['mode'] === 'execute') {
                $tv = $this->modx->newObject('modTemplateVar');
                $tv->set('name', $tvData['name']);
                $tv->set('caption', $tvData['caption'] ?? '');
                $tv->set('description', $tvData['description'] ?? '');
                $tv->set('type', $tvData['type'] ?? 'text');
                $tv->set('default_text', $tvData['default'] ?? '');
                $tv->set('elements', $tvData['elements'] ?? '');
                $tv->set('display', $tvData['display'] ?? 'default');
                $tv->set('rank', $tvData['rank'] ?? 0);
                
                if (isset($tvData['input_properties'])) {
                    $tv->set('input_properties', $tvData['input_properties']);
                }
                if (isset($tvData['output_properties'])) {
                    $tv->set('output_properties', $tvData['output_properties']);
                }
                
                if (!empty($tvData['category'])) {
                    $cat = $this->modx->getObject('modCategory', ['category' => $tvData['category']]);
                    if ($cat) $tv->set('category', $cat->get('id'));
                }
                
                if ($tv->save()) {
                    // Assign to templates
                    $this->assignTVToTemplates($tv, $tvData['templates'] ?? []);
                    
                    $results['created']++;
                    $results['items'][] = ['name' => $tvData['name'], 'action' => 'created'];
                }
            } else {
                $results['created']++;
                $results['items'][] = ['name' => $tvData['name'], 'action' => 'will_create'];
            }
        }
        
        return $results;
    }

    /**
     * Assign TV to templates
     *
     * @param modTemplateVar $tv
     * @param array $templateNames
     */
    protected function assignTVToTemplates($tv, array $templateNames) {
        foreach ($templateNames as $tplName) {
            $tpl = $this->modx->getObject('modTemplate', ['templatename' => $tplName]);
            if (!$tpl) continue;
            
            // Check if already assigned
            $existing = $this->modx->getObject('modTemplateVarTemplate', [
                'tmplvarid' => $tv->get('id'),
                'templateid' => $tpl->get('id'),
            ]);
            
            if (!$existing) {
                $tvt = $this->modx->newObject('modTemplateVarTemplate');
                $tvt->set('tmplvarid', $tv->get('id'));
                $tvt->set('templateid', $tpl->get('id'));
                $tvt->save();
            }
        }
    }

    /**
     * Import snippets
     *
     * @param array $snippets
     * @param array $options
     * @return array
     */
    protected function importSnippets($snippets, $options) {
        $results = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'items' => []];
        
        foreach ($snippets as $snippetData) {
            $existing = $this->modx->getObject('modSnippet', ['name' => $snippetData['name']]);
            
            if ($existing) {
                if ($options['update'] && $options['mode'] === 'execute') {
                    $existing->set('description', $snippetData['description'] ?? '');
                    $existing->set('snippet', $snippetData['content'] ?? '');
                    if (isset($snippetData['properties'])) {
                        $existing->set('properties', $snippetData['properties']);
                    }
                    if (!empty($snippetData['category'])) {
                        $cat = $this->modx->getObject('modCategory', ['category' => $snippetData['category']]);
                        if ($cat) $existing->set('category', $cat->get('id'));
                    }
                    $existing->save();
                    $results['updated']++;
                    $results['items'][] = ['name' => $snippetData['name'], 'action' => 'updated'];
                } else {
                    $results['skipped']++;
                    $results['items'][] = ['name' => $snippetData['name'], 'action' => 'skipped'];
                }
                continue;
            }
            
            if ($options['mode'] === 'execute') {
                $snippet = $this->modx->newObject('modSnippet');
                $snippet->set('name', $snippetData['name']);
                $snippet->set('description', $snippetData['description'] ?? '');
                $snippet->set('snippet', $snippetData['content'] ?? '');
                
                if (isset($snippetData['properties'])) {
                    $snippet->set('properties', $snippetData['properties']);
                }
                
                if (!empty($snippetData['category'])) {
                    $cat = $this->modx->getObject('modCategory', ['category' => $snippetData['category']]);
                    if ($cat) $snippet->set('category', $cat->get('id'));
                }
                
                if ($snippet->save()) {
                    $results['created']++;
                    $results['items'][] = ['name' => $snippetData['name'], 'action' => 'created'];
                }
            } else {
                $results['created']++;
                $results['items'][] = ['name' => $snippetData['name'], 'action' => 'will_create'];
            }
        }
        
        return $results;
    }

    /**
     * Import plugins
     *
     * @param array $plugins
     * @param array $options
     * @return array
     */
    protected function importPlugins($plugins, $options) {
        $results = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'items' => []];
        
        foreach ($plugins as $pluginData) {
            $existing = $this->modx->getObject('modPlugin', ['name' => $pluginData['name']]);
            
            if ($existing) {
                if ($options['update'] && $options['mode'] === 'execute') {
                    $existing->set('description', $pluginData['description'] ?? '');
                    $existing->set('plugincode', $pluginData['content'] ?? '');
                    $existing->set('disabled', $pluginData['disabled'] ?? false);
                    if (isset($pluginData['properties'])) {
                        $existing->set('properties', $pluginData['properties']);
                    }
                    if (!empty($pluginData['category'])) {
                        $cat = $this->modx->getObject('modCategory', ['category' => $pluginData['category']]);
                        if ($cat) $existing->set('category', $cat->get('id'));
                    }
                    $existing->save();
                    
                    // Update events
                    $this->assignPluginEvents($existing, $pluginData['events'] ?? []);
                    
                    $results['updated']++;
                    $results['items'][] = ['name' => $pluginData['name'], 'action' => 'updated'];
                } else {
                    $results['skipped']++;
                    $results['items'][] = ['name' => $pluginData['name'], 'action' => 'skipped'];
                }
                continue;
            }
            
            if ($options['mode'] === 'execute') {
                $plugin = $this->modx->newObject('modPlugin');
                $plugin->set('name', $pluginData['name']);
                $plugin->set('description', $pluginData['description'] ?? '');
                $plugin->set('plugincode', $pluginData['content'] ?? '');
                $plugin->set('disabled', $pluginData['disabled'] ?? false);
                
                if (isset($pluginData['properties'])) {
                    $plugin->set('properties', $pluginData['properties']);
                }
                
                if (!empty($pluginData['category'])) {
                    $cat = $this->modx->getObject('modCategory', ['category' => $pluginData['category']]);
                    if ($cat) $plugin->set('category', $cat->get('id'));
                }
                
                if ($plugin->save()) {
                    // Assign events
                    $this->assignPluginEvents($plugin, $pluginData['events'] ?? []);
                    
                    $results['created']++;
                    $results['items'][] = ['name' => $pluginData['name'], 'action' => 'created'];
                }
            } else {
                $results['created']++;
                $results['items'][] = ['name' => $pluginData['name'], 'action' => 'will_create'];
            }
        }
        
        return $results;
    }

    /**
     * Assign plugin to events
     *
     * @param modPlugin $plugin
     * @param array $events
     */
    protected function assignPluginEvents($plugin, array $events) {
        // First, remove existing events
        $existingEvents = $this->modx->getCollection('modPluginEvent', ['pluginid' => $plugin->get('id')]);
        foreach ($existingEvents as $pe) {
            $pe->remove();
        }
        
        // Add new events
        foreach ($events as $eventName) {
            $event = $this->modx->getObject('modEvent', ['name' => $eventName]);
            if ($event) {
                $pe = $this->modx->newObject('modPluginEvent');
                $pe->set('pluginid', $plugin->get('id'));
                $pe->set('event', $eventName);
                $pe->set('priority', 0);
                $pe->save();
            }
        }
    }
}
