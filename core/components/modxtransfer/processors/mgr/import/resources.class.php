<?php
/**
 * Import Resources Processor
 *
 * @package modxtransfer
 * @subpackage processors
 */
class ModxTransferImportResourcesProcessor extends modProcessor {
    
    public function process() {
        // Load the ModxTransfer class
        $corePath = $this->modx->getOption('modxtransfer.core_path', null,
            $this->modx->getOption('core_path') . 'components/modxtransfer/');
        require_once $corePath . 'model/modxtransfer/modxtransfer.class.php';
        
        $transfer = new ModxTransfer($this->modx);
        $handler = $transfer->getResourcesHandler();
        
        // Get parameters
        $file = $this->getProperty('file', '');
        $parentId = (int) $this->getProperty('parentId', 0);
        $mode = $this->getProperty('mode', 'preview');
        $defaultTemplate = $this->getProperty('defaultTemplate', '');
        
        // Validate file parameter
        if (empty($file)) {
            return $this->failure($this->modx->lexicon('modxtransfer.error_file_required'));
        }
        
        // Validate parentId for execution mode
        if ($mode === 'execute' && $parentId < 0) {
            return $this->failure($this->modx->lexicon('modxtransfer.error_parent_required'));
        }
        
        // Verify parent exists (if not root)
        if ($parentId > 0) {
            $parent = $this->modx->getObject('modResource', $parentId);
            if (!$parent) {
                return $this->failure($this->modx->lexicon('modxtransfer.error_parent_not_found', ['id' => $parentId]));
            }
        }
        
        // Resolve file path
        $filepath = $file;
        if (strpos($file, '/') !== 0 && strpos($file, MODX_BASE_PATH) !== 0) {
            // Try assets/exports/ first
            $tryPath = MODX_ASSETS_PATH . 'exports/' . $file;
            if (file_exists($tryPath)) {
                $filepath = $tryPath;
            } else {
                // Try relative to MODX root
                $filepath = MODX_BASE_PATH . $file;
            }
        }
        
        if (!file_exists($filepath)) {
            return $this->failure($this->modx->lexicon('modxtransfer.error_file_not_found', ['file' => $file]));
        }
        
        // Read and parse JSON
        $json = file_get_contents($filepath);
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->failure($this->modx->lexicon('modxtransfer.error_invalid_json', ['error' => json_last_error_msg()]));
        }
        
        // Validate data structure
        if (empty($data['resources'])) {
            return $this->failure($this->modx->lexicon('modxtransfer.error_no_resources'));
        }
        
        // Resolve default template ID
        $defaultTemplateId = '';
        if (!empty($defaultTemplate)) {
            if (is_numeric($defaultTemplate)) {
                $defaultTemplateId = (int) $defaultTemplate;
            } else {
                $tpl = $this->modx->getObject('modTemplate', ['templatename' => $defaultTemplate]);
                if ($tpl) {
                    $defaultTemplateId = $tpl->get('id');
                }
            }
        }
        
        // Perform import
        $results = $handler->import($data, [
            'mode' => $mode,
            'parentId' => $parentId,
            'defaultTemplate' => $defaultTemplateId,
        ]);
        
        // Build summary message
        $message = $mode === 'preview' 
            ? $this->modx->lexicon('modxtransfer.import_preview_resources', [
                'count' => count($data['resources']),
                'created' => $results['created'],
            ])
            : $this->modx->lexicon('modxtransfer.import_complete_resources', [
                'created' => $results['created'],
                'errors' => $results['errors'],
            ]);
        
        // Add parent info to response
        $parentInfo = null;
        if ($parentId > 0) {
            $parent = $this->modx->getObject('modResource', $parentId);
            if ($parent) {
                $parentInfo = [
                    'id' => $parent->get('id'),
                    'pagetitle' => $parent->get('pagetitle'),
                    'uri' => $parent->get('uri'),
                ];
            }
        } else {
            $parentInfo = [
                'id' => 0,
                'pagetitle' => 'Site Root',
                'uri' => '/',
            ];
        }
        
        return $this->success($message, [
            'preview' => $mode === 'preview',
            'created' => $results['created'],
            'skipped' => $results['skipped'],
            'errors' => $results['errors'],
            'items' => $results['items'],
            'parent' => $parentInfo,
            'resourceCount' => count($data['resources']),
        ]);
    }
}
return 'ModxTransferImportResourcesProcessor';
