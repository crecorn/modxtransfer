<?php
/**
 * ModxTransfer Snippet
 * 
 * Unified import/export for MODX elements and resources
 *
 * @var modX $modx
 * @var array $scriptProperties
 *
 * @package modxtransfer
 *
 * USAGE:
 * Export Elements: [[!ModxTransfer? &action=`export` &type=`elements` &output=`file` &file=`elements.json`]]
 * Export Resources: [[!ModxTransfer? &action=`export` &type=`resources` &parents=`5` &output=`file` &file=`resources.json`]]
 * Import Elements: [[!ModxTransfer? &action=`import` &type=`elements` &file=`elements.json` &mode=`preview`]]
 * Import Resources: [[!ModxTransfer? &action=`import` &type=`resources` &file=`resources.json` &parentId=`5` &mode=`preview`]]
 */

// Load the ModxTransfer class
$corePath = $modx->getOption('modxtransfer.core_path', null, 
    $modx->getOption('core_path') . 'components/modxtransfer/');
require_once $corePath . 'model/modxtransfer/modxtransfer.class.php';

$transfer = new ModxTransfer($modx);

// Get parameters
$action = $modx->getOption('action', $scriptProperties, '');
$type = $modx->getOption('type', $scriptProperties, '');
$mode = $modx->getOption('mode', $scriptProperties, 'preview');
$output = $modx->getOption('output', $scriptProperties, 'display');
$file = $modx->getOption('file', $scriptProperties, '');
$path = $modx->getOption('path', $scriptProperties, 'assets/exports/');
$pretty = (bool) $modx->getOption('pretty', $scriptProperties, true);

// Validate required parameters
if (empty($action)) {
    return '<p class="error">ModxTransfer Error: &action parameter required (export|import)</p>';
}
if (empty($type)) {
    return '<p class="error">ModxTransfer Error: &type parameter required (elements|resources)</p>';
}

// Route to appropriate handler
$result = [];
$outputHtml = '';

try {
    switch ($action) {
        case 'export':
            $result = handleExport($transfer, $type, $scriptProperties);
            break;
            
        case 'import':
            $result = handleImport($transfer, $type, $scriptProperties);
            break;
            
        default:
            return '<p class="error">ModxTransfer Error: Invalid action. Use "export" or "import".</p>';
    }
    
    // Handle output
    $jsonFlags = $pretty ? (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : JSON_UNESCAPED_SLASHES;
    
    switch ($output) {
        case 'file':
            if (empty($file)) {
                $file = $type . '-' . $action . '-' . date('Y-m-d-His') . '.json';
            }
            $fullPath = MODX_BASE_PATH . $path;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
            $filePath = $fullPath . $file;
            file_put_contents($filePath, json_encode($result, $jsonFlags));
            $outputHtml = '<p class="success">Exported to: ' . $path . $file . '</p>';
            $outputHtml .= '<p><a href="' . $modx->getOption('site_url') . $path . $file . '" target="_blank">Download File</a></p>';
            break;
            
        case 'download':
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . ($file ?: $type . '-export.json') . '"');
            echo json_encode($result, $jsonFlags);
            exit;
            
        case 'display':
        default:
            $outputHtml = '<pre style="background:#f5f5f5;padding:15px;overflow:auto;max-height:600px;font-size:12px;">';
            $outputHtml .= htmlspecialchars(json_encode($result, $jsonFlags));
            $outputHtml .= '</pre>';
            break;
    }
    
} catch (Exception $e) {
    return '<p class="error">ModxTransfer Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

return $outputHtml;

/**
 * Handle export operations
 */
function handleExport(ModxTransfer $transfer, string $type, array $props): array {
    $modx = $transfer->modx;
    
    if ($type === 'elements') {
        $handler = $transfer->getElementsHandler();
        
        // Parse element types
        $elementTypes = $modx->getOption('elementTypes', $props, 'categories,templates,chunks,tvs,snippets,plugins');
        if (is_string($elementTypes)) {
            $elementTypes = array_map('trim', explode(',', $elementTypes));
        }
        
        return $handler->export([
            'elementTypes' => $elementTypes,
            'category' => $modx->getOption('category', $props, ''),
        ]);
        
    } elseif ($type === 'resources') {
        $handler = $transfer->getResourcesHandler();
        
        // Parse parents
        $parents = $modx->getOption('parents', $props, '');
        if (is_string($parents) && !empty($parents)) {
            $parents = array_map('intval', array_map('trim', explode(',', $parents)));
        }
        
        // Parse templates filter
        $templates = $modx->getOption('templates', $props, '');
        if (is_string($templates) && !empty($templates)) {
            $templates = array_map('trim', explode(',', $templates));
        }
        
        // Parse specific IDs
        $ids = $modx->getOption('ids', $props, '');
        if (is_string($ids) && !empty($ids)) {
            $ids = array_map('intval', array_map('trim', explode(',', $ids)));
        }
        
        // Parse exclude IDs
        $excludeIds = $modx->getOption('excludeIds', $props, '');
        if (is_string($excludeIds) && !empty($excludeIds)) {
            $excludeIds = array_map('intval', array_map('trim', explode(',', $excludeIds)));
        }
        
        return $handler->export([
            'parents' => $parents,
            'depth' => (int) $modx->getOption('depth', $props, 10),
            'templates' => $templates,
            'ids' => $ids,
            'excludeIds' => $excludeIds,
            'includeTVs' => (bool) $modx->getOption('includeTVs', $props, true),
            'includeContent' => (bool) $modx->getOption('includeContent', $props, true),
            'includeUnpublished' => (bool) $modx->getOption('includeUnpublished', $props, false),
            'sortby' => $modx->getOption('sortby', $props, 'menuindex'),
            'sortdir' => $modx->getOption('sortdir', $props, 'ASC'),
            'limit' => (int) $modx->getOption('limit', $props, 0),
            'delete' => (int) $modx->getOption('delete', $props, 0),
            'confirmDelete' => $modx->getOption('confirmDelete', $props, ''),
        ]);
        
    } else {
        throw new Exception('Invalid type. Use "elements" or "resources".');
    }
}

/**
 * Handle import operations
 */
function handleImport(ModxTransfer $transfer, string $type, array $props): array {
    $modx = $transfer->modx;
    $file = $modx->getOption('file', $props, '');
    
    if (empty($file)) {
        throw new Exception('&file parameter required for import');
    }
    
    // Resolve file path
    $filePath = $file;
    if (strpos($file, '/') !== 0 && strpos($file, MODX_BASE_PATH) !== 0) {
        $filePath = MODX_BASE_PATH . $file;
    }
    
    if (!file_exists($filePath)) {
        throw new Exception('File not found: ' . $file);
    }
    
    $json = file_get_contents($filePath);
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    $mode = $modx->getOption('mode', $props, 'preview');
    $update = (bool) $modx->getOption('update', $props, false);
    
    if ($type === 'elements') {
        $handler = $transfer->getElementsHandler();
        return $handler->import($data, [
            'mode' => $mode,
            'update' => $update,
        ]);
        
    } elseif ($type === 'resources') {
        $handler = $transfer->getResourcesHandler();
        $parentId = (int) $modx->getOption('parentId', $props, 0);
        
        if ($parentId < 0) {
            throw new Exception('&parentId parameter required for resource import');
        }
        
        return $handler->import($data, [
            'mode' => $mode,
            'parentId' => $parentId,
            'defaultTemplate' => $modx->getOption('defaultTemplate', $props, ''),
        ]);
        
    } else {
        throw new Exception('Invalid type. Use "elements" or "resources".');
    }
}
