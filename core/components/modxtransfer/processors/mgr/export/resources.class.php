<?php
/**
 * Export Resources Processor
 *
 * @package modxtransfer
 * @subpackage processors
 */
class ModxTransferExportResourcesProcessor extends modProcessor {
    
    public function process() {
        // Load the ModxTransfer class
        $corePath = $this->modx->getOption('modxtransfer.core_path', null,
            $this->modx->getOption('core_path') . 'components/modxtransfer/');
        require_once $corePath . 'model/modxtransfer/modxtransfer.class.php';
        
        $transfer = new ModxTransfer($this->modx);
        $handler = $transfer->getResourcesHandler();
        
        // Get parameters
        $parents = $this->getProperty('parents', '');
        $depth = (int) $this->getProperty('depth', 10);
        $templates = $this->getProperty('templates', '');
        $ids = $this->getProperty('ids', '');
        $excludeIds = $this->getProperty('excludeIds', '');
        $includeTVs = (bool) $this->getProperty('includeTVs', true);
        $includeContent = (bool) $this->getProperty('includeContent', true);
        $includeUnpublished = (bool) $this->getProperty('includeUnpublished', false);
        $sortby = $this->getProperty('sortby', 'menuindex');
        $sortdir = $this->getProperty('sortdir', 'ASC');
        $limit = (int) $this->getProperty('limit', 0);
        $filename = $this->getProperty('filename', '');
        
        // Parse comma-separated values
        $parentsArray = [];
        if (!empty($parents)) {
            $parentsArray = array_map('intval', array_filter(array_map('trim', explode(',', $parents))));
        }
        
        $templatesArray = [];
        if (!empty($templates)) {
            $templatesArray = array_filter(array_map('trim', explode(',', $templates)));
        }
        
        $idsArray = [];
        if (!empty($ids)) {
            $idsArray = array_map('intval', array_filter(array_map('trim', explode(',', $ids))));
        }
        
        $excludeIdsArray = [];
        if (!empty($excludeIds)) {
            $excludeIdsArray = array_map('intval', array_filter(array_map('trim', explode(',', $excludeIds))));
        }
        
        // Export
        $data = $handler->export([
            'parents' => $parentsArray,
            'depth' => $depth,
            'templates' => $templatesArray,
            'ids' => $idsArray,
            'excludeIds' => $excludeIdsArray,
            'includeTVs' => $includeTVs,
            'includeContent' => $includeContent,
            'includeUnpublished' => $includeUnpublished,
            'sortby' => $sortby,
            'sortdir' => $sortdir,
            'limit' => $limit,
        ]);
        
        // Generate filename if not provided
        if (empty($filename)) {
            $filename = 'resources-export-' . date('Y-m-d-His') . '.json';
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
        
        $count = count($data['resources'] ?? []);
        
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
return 'ModxTransferExportResourcesProcessor';
