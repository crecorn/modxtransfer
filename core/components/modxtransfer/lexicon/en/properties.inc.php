<?php
/**
 * ModxTransfer Snippet Properties Lexicon
 *
 * @package modxtransfer
 * @subpackage lexicon
 */

// Core Parameters
$_lang['modxtransfer.prop_action_desc'] = 'Operation to perform: "export" or "import"';
$_lang['modxtransfer.prop_type_desc'] = 'What to import/export: "elements" or "resources"';
$_lang['modxtransfer.prop_mode_desc'] = 'For imports: "preview" to see changes without executing, "execute" to perform the import';
$_lang['modxtransfer.prop_output_desc'] = 'For exports: "display" to show JSON, "file" to save to file, "download" to trigger download';
$_lang['modxtransfer.prop_file_desc'] = 'Filename for export or path to import file (relative to MODX root or absolute)';
$_lang['modxtransfer.prop_path_desc'] = 'Directory for file output (default: assets/exports/)';
$_lang['modxtransfer.prop_pretty_desc'] = 'Pretty-print JSON output (1 or 0)';

// Element Export Parameters
$_lang['modxtransfer.prop_elementTypes_desc'] = 'Comma-separated element types: categories, templates, chunks, tvs, snippets, plugins';
$_lang['modxtransfer.prop_category_desc'] = 'Filter by category name (exports category and its children)';

// Resource Export Parameters
$_lang['modxtransfer.prop_parents_desc'] = 'Comma-separated parent resource IDs to export from';
$_lang['modxtransfer.prop_depth_desc'] = 'How deep to traverse resource tree (default: 10)';
$_lang['modxtransfer.prop_templates_desc'] = 'Comma-separated template names to filter by';
$_lang['modxtransfer.prop_ids_desc'] = 'Specific resource IDs to export';
$_lang['modxtransfer.prop_excludeIds_desc'] = 'Resource IDs to exclude from export';
$_lang['modxtransfer.prop_includeTVs_desc'] = 'Include TV values in export (1 or 0, default: 1)';
$_lang['modxtransfer.prop_includeContent_desc'] = 'Include content field in export (1 or 0, default: 1)';
$_lang['modxtransfer.prop_includeUnpublished_desc'] = 'Include unpublished resources (1 or 0, default: 0)';
$_lang['modxtransfer.prop_sortby_desc'] = 'Field to sort resources by (default: menuindex)';
$_lang['modxtransfer.prop_sortdir_desc'] = 'Sort direction: ASC or DESC (default: ASC)';
$_lang['modxtransfer.prop_limit_desc'] = 'Maximum resources to export (0 = unlimited)';
$_lang['modxtransfer.prop_delete_desc'] = 'Delete after export: 0=no, 1=soft delete (trash), 2=hard delete (permanent)';
$_lang['modxtransfer.prop_confirmDelete_desc'] = 'Must be "YES-DELETE" when using delete parameter';

// Import Parameters
$_lang['modxtransfer.prop_update_desc'] = 'Update existing elements during import (1 or 0, default: 0)';
$_lang['modxtransfer.prop_parentId_desc'] = 'Resource ID to nest imported resources under';
$_lang['modxtransfer.prop_defaultTemplate_desc'] = 'Default template ID for imported resources without template specified';
