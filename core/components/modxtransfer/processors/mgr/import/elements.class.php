<?php
/**
 * Import Elements Processor
 *
 * @package modxtransfer
 * @subpackage processors
 */
class ModxTransferImportElementsProcessor extends modProcessor {
    
    public function process() {
        // Load the ModxTransfer class
        $corePath = $this->modx->getOption('modxtransfer.core_path', null,
            $this->modx->getOption('core_path') . 'components/modxtransfer/');
        require_once $corePath . 'model/modxtransfer/modxtransfer.class.php';
        
        $transfer = new ModxTransfer($this->modx);
        $handler = $transfer->getElementsHandler();
        
        // Get parameters
        $file = $this->getProperty('file', '');
        $mode = $this->getProperty('mode', 'preview');
        $update = (bool) $this->getProperty('update', false);
        
        // Validate file parameter
        if (empty($file)) {
            return $this->failure($this->modx->lexicon('modxtransfer.error_file_required'));
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
        
        // Perform import
        $results = $handler->import($data, [
            'mode' => $mode,
            'update' => $update,
        ]);
        
        // Build summary
        $summary = [];
        foreach (['categories', 'templates', 'chunks', 'tvs', 'snippets', 'plugins'] as $type) {
            if (isset($results[$type])) {
                $r = $results[$type];
                if (($r['created'] ?? 0) > 0 || ($r['updated'] ?? 0) > 0 || ($r['skipped'] ?? 0) > 0) {
                    $summary[$type] = [
                        'created' => $r['created'] ?? 0,
                        'updated' => $r['updated'] ?? 0,
                        'skipped' => $r['skipped'] ?? 0,
                    ];
                }
            }
        }
        
        $message = $mode === 'preview' 
            ? $this->modx->lexicon('modxtransfer.import_preview_complete')
            : $this->modx->lexicon('modxtransfer.import_complete');
        
        return $this->success($message, [
            'preview' => $mode === 'preview',
            'summary' => $summary,
            'results' => $results,
        ]);
    }
}
return 'ModxTransferImportElementsProcessor';
