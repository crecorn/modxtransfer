<?php
/**
 * ModxTransfer - Import/Export utility for MODX Revolution
 *
 * @package modxtransfer
 */
class ModxTransfer {
    /** @var modX $modx */
    public $modx;
    
    /** @var array $config */
    public $config = [];
    
    /** @var array $errors */
    public $errors = [];

    /**
     * Constructor
     *
     * @param modX $modx
     * @param array $config
     */
    public function __construct(modX &$modx, array $config = []) {
        $this->modx =& $modx;
        
        $corePath = $modx->getOption('modxtransfer.core_path', $config, 
            $modx->getOption('core_path') . 'components/modxtransfer/');
        $assetsUrl = $modx->getOption('modxtransfer.assets_url', $config,
            $modx->getOption('assets_url') . 'components/modxtransfer/');
        $assetsPath = $modx->getOption('modxtransfer.assets_path', $config,
            $modx->getOption('assets_path') . 'components/modxtransfer/');
        
        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',
            'controllersPath' => $corePath . 'controllers/',
            'elementsPath' => $corePath . 'elements/',
            'chunksPath' => $corePath . 'elements/chunks/',
            'snippetsPath' => $corePath . 'elements/snippets/',
            'templatesPath' => $corePath . 'templates/',
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'connectorUrl' => $assetsUrl . 'connector.php',
            'exportPath' => MODX_ASSETS_PATH . 'exports/',
        ], $config);
        
        $this->modx->lexicon->load('modxtransfer:default');
    }

    /**
     * Get the Elements handler
     *
     * @return ModxTransferElements
     */
    public function getElementsHandler() {
        require_once $this->config['modelPath'] . 'modxtransfer/elements/modxtransferelements.class.php';
        return new ModxTransferElements($this);
    }

    /**
     * Get the Resources handler
     *
     * @return ModxTransferResources
     */
    public function getResourcesHandler() {
        require_once $this->config['modelPath'] . 'modxtransfer/resources/modxtransferresources.class.php';
        return new ModxTransferResources($this);
    }

    /**
     * Ensure export directory exists
     *
     * @return string Path to export directory
     */
    public function ensureExportDirectory() {
        if (!is_dir($this->config['exportPath'])) {
            mkdir($this->config['exportPath'], 0755, true);
        }
        
        // Create .htaccess to prevent direct access
        $htaccess = $this->config['exportPath'] . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "# Prevent directory listing\nOptions -Indexes\n");
        }
        
        return $this->config['exportPath'];
    }

    /**
     * Add an error message
     *
     * @param string $message
     */
    public function addError($message) {
        $this->errors[] = $message;
    }

    /**
     * Get all errors
     *
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Check if there are errors
     *
     * @return bool
     */
    public function hasErrors() {
        return !empty($this->errors);
    }

    /**
     * Clear errors
     */
    public function clearErrors() {
        $this->errors = [];
    }

    /**
     * Get list of export files
     *
     * @return array
     */
    public function getExportFiles() {
        $files = [];
        $exportPath = $this->config['exportPath'];
        
        if (is_dir($exportPath)) {
            $handle = opendir($exportPath);
            if ($handle) {
                while (($file = readdir($handle)) !== false) {
                    if ($file === '.' || $file === '..' || $file === '.htaccess') {
                        continue;
                    }
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                        $filepath = $exportPath . $file;
                        $files[] = [
                            'name' => $file,
                            'path' => $filepath,
                            'size' => filesize($filepath),
                            'modified' => filemtime($filepath),
                        ];
                    }
                }
                closedir($handle);
            }
        }
        
        // Sort by modified date, newest first
        usort($files, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });
        
        return $files;
    }
}
