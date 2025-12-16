<?php
/**
 * ModxTransfer Default Lexicon
 *
 * @package modxtransfer
 * @subpackage lexicon
 */

// Component
$_lang['modxtransfer'] = 'ModxTransfer';
$_lang['modxtransfer.desc'] = 'Import and Export MODX elements and resources';
$_lang['modxtransfer.menu_desc'] = 'Import/Export MODX Elements & Resources';

// Tabs & Sections
$_lang['modxtransfer.export'] = 'Export';
$_lang['modxtransfer.import'] = 'Import';
$_lang['modxtransfer.export_elements'] = 'Export Elements';
$_lang['modxtransfer.export_resources'] = 'Export Resources';
$_lang['modxtransfer.import_elements'] = 'Import Elements';
$_lang['modxtransfer.import_resources'] = 'Import Resources';

// Form Labels
$_lang['modxtransfer.element_types'] = 'Element Types';
$_lang['modxtransfer.category_filter'] = 'Category Filter';
$_lang['modxtransfer.filename'] = 'Filename';
$_lang['modxtransfer.parent_ids'] = 'Parent IDs';
$_lang['modxtransfer.depth'] = 'Depth';
$_lang['modxtransfer.include_tvs'] = 'Include TVs';
$_lang['modxtransfer.include_content'] = 'Include Content';
$_lang['modxtransfer.include_unpublished'] = 'Include Unpublished';
$_lang['modxtransfer.templates_filter'] = 'Templates Filter';
$_lang['modxtransfer.sortby'] = 'Sort By';
$_lang['modxtransfer.sortdir'] = 'Sort Direction';
$_lang['modxtransfer.limit'] = 'Limit';
$_lang['modxtransfer.file'] = 'File';
$_lang['modxtransfer.file_desc'] = 'Filename in assets/exports/ or full path';
$_lang['modxtransfer.parent_id'] = 'Parent Resource';
$_lang['modxtransfer.parent_id_desc'] = 'Resource ID to nest imports under (0 for root)';
$_lang['modxtransfer.default_template'] = 'Default Template';
$_lang['modxtransfer.update_existing'] = 'Update Existing';
$_lang['modxtransfer.update_existing_desc'] = 'Update elements that already exist';

// Buttons
$_lang['modxtransfer.preview'] = 'Preview';
$_lang['modxtransfer.execute'] = 'Execute Import';
$_lang['modxtransfer.download'] = 'Download';

// Placeholders
$_lang['modxtransfer.parents_placeholder'] = '0 for all, or comma-separated IDs';
$_lang['modxtransfer.templates_placeholder'] = 'Template names, comma-separated';
$_lang['modxtransfer.file_placeholder'] = 'my-export.json';

// Success Messages
$_lang['modxtransfer.export_success'] = 'Exported [[+count]] items to [[+filename]]';
$_lang['modxtransfer.export_elements_success'] = 'Exported [[+count]] elements to [[+filename]]';
$_lang['modxtransfer.export_resources_success'] = 'Exported [[+count]] resources to [[+filename]]';
$_lang['modxtransfer.import_preview_complete'] = 'Import preview complete. Review changes below.';
$_lang['modxtransfer.import_complete'] = 'Import completed successfully.';
$_lang['modxtransfer.import_preview_resources'] = 'Preview: [[+count]] resources found, [[+created]] will be created.';
$_lang['modxtransfer.import_complete_resources'] = 'Import complete: [[+created]] created, [[+errors]] errors.';

// Error Messages
$_lang['modxtransfer.error_file_required'] = 'File parameter is required';
$_lang['modxtransfer.error_file_not_found'] = 'File not found: [[+file]]';
$_lang['modxtransfer.error_invalid_json'] = 'Invalid JSON: [[+error]]';
$_lang['modxtransfer.error_parent_required'] = 'Parent ID is required for resource import';
$_lang['modxtransfer.error_parent_not_found'] = 'Parent resource not found: ID [[+id]]';
$_lang['modxtransfer.error_no_resources'] = 'No resources found in import file';
$_lang['modxtransfer.error_no_elements'] = 'No elements found in import file';

// Result Labels
$_lang['modxtransfer.result_created'] = 'Created';
$_lang['modxtransfer.result_updated'] = 'Updated';
$_lang['modxtransfer.result_skipped'] = 'Skipped';
$_lang['modxtransfer.result_errors'] = 'Errors';
$_lang['modxtransfer.result_will_create'] = 'Will Create';
$_lang['modxtransfer.result_exists'] = 'Already Exists';

// Element Types
$_lang['modxtransfer.type_categories'] = 'Categories';
$_lang['modxtransfer.type_templates'] = 'Templates';
$_lang['modxtransfer.type_chunks'] = 'Chunks';
$_lang['modxtransfer.type_tvs'] = 'Template Variables';
$_lang['modxtransfer.type_snippets'] = 'Snippets';
$_lang['modxtransfer.type_plugins'] = 'Plugins';
$_lang['modxtransfer.categories'] = 'Categories';
$_lang['modxtransfer.templates'] = 'Templates';
$_lang['modxtransfer.chunks'] = 'Chunks';
$_lang['modxtransfer.tvs'] = 'TVs';
$_lang['modxtransfer.snippets'] = 'Snippets';
$_lang['modxtransfer.plugins'] = 'Plugins';
$_lang['modxtransfer.processing'] = 'Processing...';
$_lang['modxtransfer.exported_count'] = 'Exported [[+count]] elements';
$_lang['modxtransfer.exported_resources_count'] = 'Exported [[+count]] resources';
$_lang['modxtransfer.parent_ids_placeholder'] = '0 for all, or comma-separated IDs';
$_lang['modxtransfer.templates_filter_placeholder'] = 'Comma-separated template names';

// Help Text
$_lang['modxtransfer.help_export_elements'] = 'Export MODX elements (templates, chunks, TVs, snippets, plugins) to a JSON file.';
$_lang['modxtransfer.help_export_resources'] = 'Export resources with their TV values to a JSON file.';
$_lang['modxtransfer.help_import_elements'] = 'Import elements from a JSON file. Preview first to see what will be created.';
$_lang['modxtransfer.help_import_resources'] = 'Import resources from a JSON file. All resources are imported as unpublished for review.';

// Confirmations
$_lang['modxtransfer.confirm_execute'] = 'Are you sure you want to execute this import? This will create new elements/resources.';
