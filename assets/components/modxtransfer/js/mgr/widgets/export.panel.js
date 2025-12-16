/**
 * ModxTransfer Export Panel
 *
 * @package modxtransfer
 */
ModxTransfer.panel.Export = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        id: 'modxtransfer-panel-export',
        border: false,
        baseCls: 'modx-formpanel',
        layout: 'form',
        labelWidth: 150,
        padding: '15px',
        items: [{
            xtype: 'fieldset',
            title: _('modxtransfer.export_elements'),
            collapsible: true,
            items: [{
                html: '<p style="margin-bottom:10px;color:#555;">' + _('modxtransfer.help_export_elements') + '</p>',
                border: false
            }, {
                xtype: 'checkboxgroup',
                fieldLabel: _('modxtransfer.element_types'),
                columns: 3,
                items: [
                    {boxLabel: _('modxtransfer.categories'), name: 'types[]', inputValue: 'categories', checked: true},
                    {boxLabel: _('modxtransfer.templates'), name: 'types[]', inputValue: 'templates', checked: true},
                    {boxLabel: _('modxtransfer.chunks'), name: 'types[]', inputValue: 'chunks', checked: true},
                    {boxLabel: _('modxtransfer.tvs'), name: 'types[]', inputValue: 'tvs', checked: true},
                    {boxLabel: _('modxtransfer.snippets'), name: 'types[]', inputValue: 'snippets', checked: true},
                    {boxLabel: _('modxtransfer.plugins'), name: 'types[]', inputValue: 'plugins', checked: true}
                ]
            }, {
                xtype: 'modx-combo-category',
                fieldLabel: _('modxtransfer.category_filter'),
                name: 'category',
                id: 'modxtransfer-export-category',
                hiddenName: 'category',
                allowBlank: true,
                width: 300
            }, {
                xtype: 'textfield',
                fieldLabel: _('modxtransfer.filename'),
                name: 'elements_filename',
                id: 'modxtransfer-elements-filename',
                value: 'elements-export.json',
                width: 300
            }, {
                xtype: 'container',
                style: 'margin-top: 15px;',
                items: [{
                    xtype: 'button',
                    text: _('modxtransfer.export_elements'),
                    cls: 'primary-button',
                    handler: this.exportElements,
                    scope: this
                }]
            }, {
                xtype: 'container',
                id: 'modxtransfer-elements-export-results',
                style: 'margin-top: 15px;',
                html: ''
            }]
        }, {
            xtype: 'fieldset',
            title: _('modxtransfer.export_resources'),
            collapsible: true,
            items: [{
                html: '<p style="margin-bottom:10px;color:#555;">' + _('modxtransfer.help_export_resources') + '</p>',
                border: false
            }, {
                xtype: 'textfield',
                fieldLabel: _('modxtransfer.parent_ids'),
                name: 'parents',
                id: 'modxtransfer-export-parents',
                width: 300,
                emptyText: _('modxtransfer.parent_ids_placeholder')
            }, {
                xtype: 'numberfield',
                fieldLabel: _('modxtransfer.depth'),
                name: 'depth',
                id: 'modxtransfer-export-depth',
                value: 10,
                width: 100,
                minValue: 1,
                maxValue: 100
            }, {
                xtype: 'textfield',
                fieldLabel: _('modxtransfer.templates_filter'),
                name: 'templates',
                id: 'modxtransfer-export-templates',
                width: 300,
                emptyText: _('modxtransfer.templates_filter_placeholder')
            }, {
                xtype: 'checkbox',
                fieldLabel: _('modxtransfer.include_tvs'),
                name: 'includeTVs',
                id: 'modxtransfer-export-include-tvs',
                checked: true
            }, {
                xtype: 'checkbox',
                fieldLabel: _('modxtransfer.include_content'),
                name: 'includeContent',
                id: 'modxtransfer-export-include-content',
                checked: true
            }, {
                xtype: 'checkbox',
                fieldLabel: _('modxtransfer.include_unpublished'),
                name: 'includeUnpublished',
                id: 'modxtransfer-export-include-unpublished',
                checked: false
            }, {
                xtype: 'textfield',
                fieldLabel: _('modxtransfer.filename'),
                name: 'resources_filename',
                id: 'modxtransfer-resources-filename',
                value: 'resources-export.json',
                width: 300
            }, {
                xtype: 'container',
                style: 'margin-top: 15px;',
                items: [{
                    xtype: 'button',
                    text: _('modxtransfer.export_resources'),
                    cls: 'primary-button',
                    handler: this.exportResources,
                    scope: this
                }]
            }, {
                xtype: 'container',
                id: 'modxtransfer-resources-export-results',
                style: 'margin-top: 15px;',
                html: ''
            }]
        }]
    });
    ModxTransfer.panel.Export.superclass.constructor.call(this, config);
};

Ext.extend(ModxTransfer.panel.Export, MODx.Panel, {
    
    /**
     * Export elements
     */
    exportElements: function() {
        var types = [];
        Ext.each(this.findByType('checkbox'), function(cb) {
            if (cb.name === 'types[]' && cb.checked) {
                types.push(cb.inputValue);
            }
        });
        
        var category = Ext.getCmp('modxtransfer-export-category').getValue();
        var filename = Ext.getCmp('modxtransfer-elements-filename').getValue();
        var resultsContainer = Ext.getCmp('modxtransfer-elements-export-results');
        
        resultsContainer.update('<p><i>' + _('modxtransfer.processing') + '</i></p>');
        
        MODx.Ajax.request({
            url: ModxTransfer.config.connectorUrl,
            params: {
                action: 'mgr/export/elements',
                types: types.join(','),
                category: category,
                filename: filename
            },
            listeners: {
                success: {
                    fn: function(r) {
                        var html = '<div style="background:#d4edda;padding:15px;border-radius:4px;border:1px solid #c3e6cb;">';
                        html += '<p style="color:#155724;margin:0;"><strong>' + _('modxtransfer.export_success') + '</strong></p>';
                        html += '<p style="margin:10px 0 0;">' + _('modxtransfer.exported_count', {count: r.object.count}) + '</p>';
                        if (r.object.downloadUrl) {
                            html += '<p style="margin:10px 0 0;"><a href="' + r.object.downloadUrl + '" target="_blank" class="modxtransfer-download-link">' + _('modxtransfer.download') + '</a></p>';
                        }
                        html += '</div>';
                        resultsContainer.update(html);
                        
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
     * Export resources
     */
    exportResources: function() {
        var parents = Ext.getCmp('modxtransfer-export-parents').getValue();
        var depth = Ext.getCmp('modxtransfer-export-depth').getValue();
        var templates = Ext.getCmp('modxtransfer-export-templates').getValue();
        var includeTVs = Ext.getCmp('modxtransfer-export-include-tvs').getValue();
        var includeContent = Ext.getCmp('modxtransfer-export-include-content').getValue();
        var includeUnpublished = Ext.getCmp('modxtransfer-export-include-unpublished').getValue();
        var filename = Ext.getCmp('modxtransfer-resources-filename').getValue();
        var resultsContainer = Ext.getCmp('modxtransfer-resources-export-results');
        
        resultsContainer.update('<p><i>' + _('modxtransfer.processing') + '</i></p>');
        
        MODx.Ajax.request({
            url: ModxTransfer.config.connectorUrl,
            params: {
                action: 'mgr/export/resources',
                parents: parents,
                depth: depth,
                templates: templates,
                includeTVs: includeTVs ? 1 : 0,
                includeContent: includeContent ? 1 : 0,
                includeUnpublished: includeUnpublished ? 1 : 0,
                filename: filename
            },
            listeners: {
                success: {
                    fn: function(r) {
                        var html = '<div style="background:#d4edda;padding:15px;border-radius:4px;border:1px solid #c3e6cb;">';
                        html += '<p style="color:#155724;margin:0;"><strong>' + _('modxtransfer.export_success') + '</strong></p>';
                        html += '<p style="margin:10px 0 0;">' + _('modxtransfer.exported_resources_count', {count: r.object.count}) + '</p>';
                        if (r.object.downloadUrl) {
                            html += '<p style="margin:10px 0 0;"><a href="' + r.object.downloadUrl + '" target="_blank" class="modxtransfer-download-link">' + _('modxtransfer.download') + '</a></p>';
                        }
                        html += '</div>';
                        resultsContainer.update(html);
                        
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
    }
});

Ext.reg('modxtransfer-panel-export', ModxTransfer.panel.Export);
