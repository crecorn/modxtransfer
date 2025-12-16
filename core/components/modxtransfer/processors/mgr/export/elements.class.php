<?php
/**
 * Export Elements Processor
 *
 * @package modxtransfer
 * @subpackage processors
 */
class ModxTransferExportElementsProcessor extends modProcessor {
    
    public function process() {
        // Load the ModxTransfer class
        $corePath = $this->modx->getOption('modxtransfer.core_path', null,
            $this->modx->getOption('core_path') . 'components/modxtransfer/');
        require_once $corePath . 'model/modxtransfer/modxtransfer.class.php';
        
        $transfer = new ModxTransfer($this->modx);
        $handler = $transfer->getElementsHandler();
        
        // Get parameters
        $types = $this->getProperty('types', 'categories,templates,chunks,tvs,snippets,plugins');
        $category = $this->getProperty('category', '');
        $filename = $this->getProperty('filename', '');
        
        // Parse types
        $typesArray = is_array($types) ? $types : array_filter(array_map('trim', explode(',', $types)));
        
        // Export
        $data = $handler->export([
            'elementTypes' => $typesArray,
            'category' => $category,
        ]);
        
        // Generate filename if not provided
        if (empty($filename)) {
            $filename = 'elements-export-' . date('Y-m-d-His') . '.json';
        }
        
        // Ensure filename has .json extension
        if (substr($filename, -5) !== '.json') {
            $filename .= '.json';
        }
        
        // Save to file
        $transfer->ensureExportDirectory();
        $filepath = $transfer->config['exportPath'] . $filename;
        
        $jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        file_put_contents($filepath, json_encode($data, $jsonFlags));
        
        // Count exported elements
        $count = 0;
        foreach (['categories', 'templates', 'chunks', 'tvs', 'snippets', 'plugins'] as $type) {
            if (isset($data[$type])) {
                $count += count($data[$type]);
            }
        }
        
        return $this->success(
            $this->modx->lexicon('modxtransfer.export_success', ['count' => $count, 'filename' => $filename]),
            [
                'filepath' => $filepath,
                'downloadUrl' => $this->modx->getOption('assets_url') . 'exports/' . $filename,
                'count' => $count,
                'filename' => $filename,
            ]
        );
    }
}
return 'ModxTransferExportElementsProcessor';
