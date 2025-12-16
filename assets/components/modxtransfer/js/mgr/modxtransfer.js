/**
 * ModxTransfer
 *
 * @package modxtransfer
 */
var ModxTransfer = function(config) {
    config = config || {};
    ModxTransfer.superclass.constructor.call(this, config);
};
Ext.extend(ModxTransfer, Ext.Component, {
    page: {},
    window: {},
    grid: {},
    tree: {},
    panel: {},
    combo: {},
    config: {},
    utils: {}
});
Ext.reg('modxtransfer', ModxTransfer);
ModxTransfer = new ModxTransfer();
