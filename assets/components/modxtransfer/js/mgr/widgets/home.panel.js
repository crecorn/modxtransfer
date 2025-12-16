/**
 * ModxTransfer Home Panel
 *
 * @package modxtransfer
 */
ModxTransfer.panel.Home = function(config) {
    config = config || {};
    Ext.apply(config, {
        border: false,
        baseCls: 'modx-formpanel',
        cls: 'container',
        items: [{
            html: '<h2>' + _('modxtransfer') + '</h2>',
            border: false,
            cls: 'modx-page-header'
        }, {
            xtype: 'modx-tabs',
            defaults: { 
                border: false, 
                autoHeight: true 
            },
            border: true,
            activeTab: 0,
            hideMode: 'offsets',
            items: [{
                title: _('modxtransfer.export'),
                items: [{
                    xtype: 'modxtransfer-panel-export'
                }]
            }, {
                title: _('modxtransfer.import'),
                items: [{
                    xtype: 'modxtransfer-panel-import'
                }]
            }]
        }]
    });
    ModxTransfer.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(ModxTransfer.panel.Home, MODx.Panel);
Ext.reg('modxtransfer-panel-home', ModxTransfer.panel.Home);
