/**
 * ModxTransfer Import Panel
 * 
 * @package modxtransfer
 */
ModxTransfer.panel.Import = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        id: 'modxtransfer-panel-import',
        border: false,
        baseCls: 'modx-formpanel',
        layout: 'form',
        labelWidth: 150,
        padding: '15px',
        items: [{
            xtype: 'fieldset',
            title: _('modxtransfer.import_elements'),
            collapsible: true,
            items: [{
                html: '<p style="margin-bottom:10px;color:#555;">' + _('modxtransfer.help_import_elements') + '</p>',
                border: false
            }, {
                xtype: 'textfield',
                fieldLabel: _('modxtransfer.file'),
                name: 'elements_file',
                id: 'modxtransfer-elements-file',
                width: 400,
                emptyText: _('modxtransfer.file_placeholder'),
                allowBlank: false
            }, {
                xtype: 'checkbox',
                fieldLabel: _('modxtransfer.update_existing'),
                name: 'elements_update',
                id: 'modxtransfer-elements-update',
                boxLabel: _('modxtransfer.update_existing_desc')
            }, {
                xtype: 'container',
                style: 'margin-top: 15px;',
                items: [{
                    xtype: 'button',
                    text: _('modxtransfer.preview'),
                    handler: this.previewElements,
                    scope: this,
                    style: 'margin-right: 10px;'
                }, {
                    xtype: 'button',
                    text: _('modxtransfer.execute'),
                    cls: 'primary-button',
                    handler: this.executeElements,
                    scope: this
                }]
            }, {
                xtype: 'container',
                id: 'modxtransfer-elements-results',
                style: 'margin-top: 15px;',
                html: ''
            }]
        }, {
            xtype: 'fieldset',
            title: _('modxtransfer.import_resources'),
            collapsible: true,
            items: [{
                html: '<p style="margin-bottom:10px;color:#555;">' + _('modxtransfer.help_import_resources') + '</p>',
                border: false
            }, {
                xtype: 'textfield',
                fieldLabel: _('modxtransfer.file'),
                name: 'resources_file',
                id: 'modxtransfer-resources-file',
                width: 400,
                emptyText: _('modxtransfer.file_placeholder'),
                allowBlank: false
            }, {
                xtype: 'modx-field-parent-change',
                fieldLabel: _('modxtransfer.parent_id'),
                name: 'resources_parent',
                id: 'modxtransfer-resources-parent',
                width: 400,
                value: 0
            }, {
                xtype: 'modx-combo-template',
                fieldLabel: _('modxtransfer.default_template'),
                name: 'resources_template',
                id: 'modxtransfer-resources-template',
                width: 300,
                allowBlank: true
            }, {
                xtype: 'container',
                style: 'margin-top: 15px;',
                items: [{
                    xtype: 'button',
                    text: _('modxtransfer.preview'),
                    handler: this.previewResources,
                    scope: this,
                    style: 'margin-right: 10px;'
                }, {
                    xtype: 'button',
                    text: _('modxtransfer.execute'),
                    cls: 'primary-button',
                    handler: this.executeResources,
                    scope: this
                }]
            }, {
                xtype: 'container',
                id: 'modxtransfer-resources-results',
                style: 'margin-top: 15px;',
                html: ''
            }]
        }]
    });
    ModxTransfer.panel.Import.superclass.constructor.call(this, config);
};

Ext.extend(ModxTransfer.panel.Import, MODx.Panel, {
    
    /**
     * Preview elements import
     */
    previewElements: function() {
        this.importElements('preview');
    },
    
    /**
     * Execute elements import
     */
    executeElements: function() {
        Ext.Msg.confirm(
            _('modxtransfer.execute'),
            _('modxtransfer.confirm_execute'),
            function(btn) {
                if (btn === 'yes') {
                    this.importElements('execute');
                }
            },
            this
        );
    },
    
    /**
     * Import elements
     */
    importElements: function(mode) {
        var file = Ext.getCmp('modxtransfer-elements-file').getValue();
        var update = Ext.getCmp('modxtransfer-elements-update').getValue();
        
        if (!file) {
            MODx.msg.alert(_('error'), _('modxtransfer.error_file_required'));
            return;
        }
        
        var resultsContainer = Ext.getCmp('modxtransfer-elements-results');
        resultsContainer.update('<p><i>Processing...</i></p>');
        
        MODx.Ajax.request({
            url: ModxTransfer.config.connectorUrl,
            params: {
                action: 'mgr/import/elements',
                file: file,
                mode: mode,
                update: update ? 1 : 0
            },
            listeners: {
                success: {
                    fn: function(r) {
                        this.renderElementsResults(r.object, resultsContainer);
                        MODx.msg.status({
                            title: _('success'),
                            message: r.message
                        });
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        resultsContainer.update('<p style="color:red;">' + r.message + '</p>');
                    },
                    scope: this
                }
            }
        });
    },
    
    /**
     * Render elements import results
     */
    renderElementsResults: function(data, container) {
        var html = '<div style="background:#f5f5f5;padding:15px;border-radius:4px;">';
        
        if (data.preview) {
            html += '<h4 style="margin:0 0 10px;">Preview Results</h4>';
        } else {
            html += '<h4 style="margin:0 0 10px;">Import Complete</h4>';
        }
        
        if (data.summary) {
            html += '<table style="width:100%;border-collapse:collapse;">';
            html += '<tr style="background:#e0e0e0;"><th style="padding:8px;text-align:left;">Type</th><th style="padding:8px;">Created</th><th style="padding:8px;">Updated</th><th style="padding:8px;">Skipped</th></tr>';
            
            var types = ['categories', 'templates', 'chunks', 'tvs', 'snippets', 'plugins'];
            for (var i = 0; i < types.length; i++) {
                var type = types[i];
                if (data.summary[type]) {
                    var s = data.summary[type];
                    html += '<tr>';
                    html += '<td style="padding:8px;border-bottom:1px solid #ddd;">' + type.charAt(0).toUpperCase() + type.slice(1) + '</td>';
                    html += '<td style="padding:8px;border-bottom:1px solid #ddd;text-align:center;color:green;">' + (s.created || 0) + '</td>';
                    html += '<td style="padding:8px;border-bottom:1px solid #ddd;text-align:center;color:blue;">' + (s.updated || 0) + '</td>';
                    html += '<td style="padding:8px;border-bottom:1px solid #ddd;text-align:center;color:#888;">' + (s.skipped || 0) + '</td>';
                    html += '</tr>';
                }
            }
            html += '</table>';
        }
        
        html += '</div>';
        container.update(html);
    },
    
    /**
     * Preview resources import
     */
    previewResources: function() {
        this.importResources('preview');
    },
    
    /**
     * Execute resources import
     */
    executeResources: function() {
        Ext.Msg.confirm(
            _('modxtransfer.execute'),
            _('modxtransfer.confirm_execute'),
            function(btn) {
                if (btn === 'yes') {
                    this.importResources('execute');
                }
            },
            this
        );
    },
    
    /**
     * Import resources
     */
    importResources: function(mode) {
        var file = Ext.getCmp('modxtransfer-resources-file').getValue();
        var parentId = Ext.getCmp('modxtransfer-resources-parent').getValue();
        var template = Ext.getCmp('modxtransfer-resources-template').getValue();
        
        if (!file) {
            MODx.msg.alert(_('error'), _('modxtransfer.error_file_required'));
            return;
        }
        
        var resultsContainer = Ext.getCmp('modxtransfer-resources-results');
        resultsContainer.update('<p><i>Processing...</i></p>');
        
        MODx.Ajax.request({
            url: ModxTransfer.config.connectorUrl,
            params: {
                action: 'mgr/import/resources',
                file: file,
                parentId: parentId || 0,
                defaultTemplate: template || '',
                mode: mode
            },
            listeners: {
                success: {
                    fn: function(r) {
                        this.renderResourcesResults(r.object, resultsContainer);
                        MODx.msg.status({
                            title: _('success'),
                            message: r.message
                        });
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        resultsContainer.update('<p style="color:red;">' + r.message + '</p>');
                    },
                    scope: this
                }
            }
        });
    },
    
    /**
     * Render resources import results
     */
    renderResourcesResults: function(data, container) {
        var html = '<div style="background:#f5f5f5;padding:15px;border-radius:4px;">';
        
        if (data.preview) {
            html += '<h4 style="margin:0 0 10px;">Preview Results</h4>';
        } else {
            html += '<h4 style="margin:0 0 10px;">Import Complete</h4>';
        }
        
        // Summary stats
        html += '<p><strong>Resources in file:</strong> ' + (data.resourceCount || 0) + '</p>';
        html += '<p style="color:green;"><strong>To be created:</strong> ' + (data.created || 0) + '</p>';
        if (data.errors > 0) {
            html += '<p style="color:red;"><strong>Errors:</strong> ' + data.errors + '</p>';
        }
        
        // Parent info
        if (data.parent) {
            html += '<p><strong>Parent:</strong> ' + data.parent.pagetitle + ' (ID: ' + data.parent.id + ')</p>';
        }
        
        // Items list
        if (data.items && data.items.length > 0) {
            html += '<div style="max-height:300px;overflow:auto;margin-top:10px;">';
            html += '<table style="width:100%;border-collapse:collapse;font-size:12px;">';
            html += '<tr style="background:#e0e0e0;"><th style="padding:6px;text-align:left;">Title</th><th style="padding:6px;text-align:left;">Alias</th><th style="padding:6px;">Status</th></tr>';
            
            for (var i = 0; i < data.items.length && i < 100; i++) {
                var item = data.items[i];
                var statusColor = item.action === 'created' || item.action === 'will_create' ? 'green' : (item.action === 'error' ? 'red' : '#888');
                html += '<tr>';
                html += '<td style="padding:6px;border-bottom:1px solid #ddd;">' + (item.pagetitle || 'Untitled') + '</td>';
                html += '<td style="padding:6px;border-bottom:1px solid #ddd;">' + (item.alias || '') + '</td>';
                html += '<td style="padding:6px;border-bottom:1px solid #ddd;text-align:center;color:' + statusColor + ';">' + item.action + '</td>';
                html += '</tr>';
            }
            
            if (data.items.length > 100) {
                html += '<tr><td colspan="3" style="padding:6px;text-align:center;color:#888;">...and ' + (data.items.length - 100) + ' more</td></tr>';
            }
            
            html += '</table>';
            html += '</div>';
        }
        
        html += '</div>';
        container.update(html);
    }
});

Ext.reg('modxtransfer-panel-import', ModxTransfer.panel.Import);
