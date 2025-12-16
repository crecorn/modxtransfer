<?php
/**
 * ModxTransfer Connector
 *
 * @package modxtransfer
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$corePath = $modx->getOption('modxtransfer.core_path', null, 
    $modx->getOption('core_path') . 'components/modxtransfer/');
require_once $corePath . 'model/modxtransfer/modxtransfer.class.php';

$modx->transfer = new ModxTransfer($modx);
$modx->lexicon->load('modxtransfer:default');

// Handle request
$path = $modx->getOption('processorsPath', $modx->transfer->config, 
    $corePath . 'processors/');
$modx->request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);
