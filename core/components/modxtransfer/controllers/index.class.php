<?php
/**
 * ModxTransfer Main Controller
 *
 * @package modxtransfer
 */
abstract class ModxTransferManagerController extends modExtraManagerController {
    /** @var ModxTransfer $transfer */
    public $transfer;

    public function initialize() {
        $corePath = $this->modx->getOption('modxtransfer.core_path', null, 
            $this->modx->getOption('core_path') . 'components/modxtransfer/');
        
        require_once $corePath . 'model/modxtransfer/modxtransfer.class.php';
        $this->transfer = new ModxTransfer($this->modx);
        
        $this->addCss($this->transfer->config['cssUrl'] . 'mgr/modxtransfer.css');
        $this->addJavascript($this->transfer->config['jsUrl'] . 'mgr/modxtransfer.js');
        
        $this->addHtml('<script>
            Ext.onReady(function() {
                ModxTransfer.config = ' . json_encode($this->transfer->config) . ';
            });
        </script>');
        
        return parent::initialize();
    }

    public function getLanguageTopics() {
        return ['modxtransfer:default'];
    }

    public function checkPermissions() {
        return $this->modx->hasPermission('view_document');
    }
}

/**
 * Index Controller - Routes to default controller
 */
class ModxTransferIndexManagerController extends ModxTransferManagerController {
    
    public static function getDefaultController() {
        return 'home';
    }
}
