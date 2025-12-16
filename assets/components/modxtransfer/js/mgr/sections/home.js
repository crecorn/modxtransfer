/**
 * ModxTransfer Home Section
 *
 * @package modxtransfer
 */
Ext.onReady(function() {
    MODx.load({
        xtype: 'modxtransfer-page-home'
    });
});

/**
 * Home Page
 */
ModxTransfer.page.Home = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'modxtransfer-panel-home',
            renderTo: 'modxtransfer-panel-home-div'
        }]
    });
    ModxTransfer.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(ModxTransfer.page.Home, MODx.Component);
Ext.reg('modxtransfer-page-home', ModxTransfer.page.Home);
