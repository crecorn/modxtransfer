# ModxTransfer

A MODX 3 Extra for importing and exporting elements and resources.

![MODX Version](https://img.shields.io/badge/MODX-3.x-green.svg)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

## Overview

ModxTransfer provides an easy-to-use interface for exporting and importing MODX elements (Categories, Templates, Chunks, TVs, Snippets, Plugins) and Resources with their Template Variable values. Perfect for:

- **Site migrations** - Move content between MODX installations
- **Backups** - Export elements and resources to JSON files
- **Starter kits** - Package reusable templates and components
- **Content staging** - Move content from development to production

## Features

### Export
- Export all element types: Categories, Templates, Chunks, TVs, Snippets, Plugins
- Export resources with full hierarchy preservation
- Filter by category or template
- Include/exclude Template Variable values
- Include/exclude unpublished resources
- Automatic JSON file generation with download links

### Import
- Preview mode - see what will be created before executing
- Execute mode - create elements and resources
- Update existing elements option
- Automatic category and template assignment
- TV values imported including MIGX JSON data
- Resources imported as unpublished for review

## Requirements

- MODX Revolution 3.0+
- PHP 7.4+

## Installation

### Manual Installation

1. Download or clone this repository
2. Copy `core/components/modxtransfer/` to your MODX `core/components/` directory
3. Copy `assets/components/modxtransfer/` to your MODX `assets/components/` directory
4. Create a Namespace in MODX:
   - **Name:** `modxtransfer`
   - **Core Path:** `{core_path}components/modxtransfer/`
   - **Assets Path:** `{assets_path}components/modxtransfer/`
5. Create System Settings:
   - **Key:** `modxtransfer.core_path` **Value:** `{core_path}components/modxtransfer/`
   - **Key:** `modxtransfer.assets_url` **Value:** `{assets_url}components/modxtransfer/`
6. Create a Menu item:
   - **Lexicon Key:** `ModxTransfer`
   - **Action:** `index`
   - **Namespace:** `modxtransfer`

### Transport Package (Coming Soon)

Install via MODX Package Manager.

## Usage

### Via Custom Manager Page (CMP)

1. Go to **Extras → ModxTransfer**
2. Use the **Export** tab to export elements or resources
3. Use the **Import** tab to import from JSON files

### Export Elements

1. Select which element types to export (Categories, Templates, Chunks, TVs, Snippets, Plugins)
2. Optionally filter by Category
3. Enter a filename
4. Click **Export Elements**
5. Download the generated JSON file

### Export Resources

1. Enter Parent IDs (comma-separated) or leave empty for all
2. Set depth level
3. Optionally filter by template names
4. Choose whether to include TVs and unpublished resources
5. Enter a filename
6. Click **Export Resources**

### Import Elements

1. Enter the file path (relative to MODX root, e.g., `assets/exports/my-elements.json`)
2. Click **Preview Elements** to see what will be created
3. Check "Update Existing" if you want to update elements that already exist
4. Click **Import Elements** to execute

### Import Resources

1. Enter the file path
2. Enter the Parent ID where resources should be imported
3. Click **Preview Resources** to review
4. Click **Import Resources** to execute

**Note:** Resources are imported as unpublished so you can review them before publishing.

### Via Snippet

You can also use ModxTransfer programmatically:

```php
// Export elements to file
[[!ModxTransfer?
  &action=`export`
  &type=`elements`
  &output=`file`
  &file=`my-elements.json`
]]

// Export resources
[[!ModxTransfer?
  &action=`export`
  &type=`resources`
  &parents=`5,10`
  &output=`file`
  &file=`my-resources.json`
]]

// Import elements (preview)
[[!ModxTransfer?
  &action=`import`
  &type=`elements`
  &file=`assets/exports/my-elements.json`
  &mode=`preview`
]]

// Import elements (execute)
[[!ModxTransfer?
  &action=`import`
  &type=`elements`
  &file=`assets/exports/my-elements.json`
  &mode=`execute`
]]

// Import resources
[[!ModxTransfer?
  &action=`import`
  &type=`resources`
  &file=`assets/exports/my-resources.json`
  &parentId=`5`
  &mode=`execute`
]]
```

## JSON File Formats

### Elements JSON Structure

```json
{
  "meta": {
    "project": "My Project",
    "description": "MODX Elements Export",
    "created": "2025-12-16T12:00:00Z",
    "exported_types": ["categories", "templates", "chunks", "tvs", "snippets", "plugins"],
    "category_filter": ""
  },
  "categories": [
    {"name": "My Category", "parent": ""}
  ],
  "templates": [
    {"name": "tpl.MyTemplate", "description": "", "category": "My Category", "content": "..."}
  ],
  "chunks": [
    {"name": "myChunk", "description": "", "category": "My Category", "content": "..."}
  ],
  "tvs": [
    {
      "name": "myTV",
      "caption": "My TV",
      "description": "",
      "type": "text",
      "category": "My Category",
      "templates": ["tpl.MyTemplate"],
      "default": "",
      "elements": "",
      "display": "default"
    }
  ],
  "snippets": [
    {"name": "mySnippet", "description": "", "category": "My Category", "content": "<?php return 'Hello';"}
  ],
  "plugins": [
    {"name": "myPlugin", "description": "", "category": "My Category", "content": "<?php ...", "events": ["OnLoadWebDocument"]}
  ]
}
```

### Resources JSON Structure

```json
{
  "meta": {
    "project": "My Project",
    "description": "MODX Resources Export",
    "created": "2025-12-16T12:00:00Z",
    "count": 5
  },
  "resources": [
    {
      "original_id": 1,
      "pagetitle": "My Page",
      "longtitle": "My Long Title",
      "alias": "my-page",
      "description": "Meta description",
      "introtext": "Summary text",
      "menutitle": "Menu Title",
      "content": "<p>Page content...</p>",
      "template": "BaseTemplate",
      "parent": 0,
      "published": 1,
      "hidemenu": 0,
      "menuindex": 0,
      "searchable": 1,
      "cacheable": 1,
      "richtext": 1,
      "isfolder": 0,
      "tvs": {
        "myTV": "TV value",
        "myMIGX": [{"field": "value"}]
      }
    }
  ]
}
```

## Snippet Parameters

### Common Parameters

| Parameter | Values | Default | Description |
|-----------|--------|---------|-------------|
| `&action` | `export`, `import` | *required* | Operation to perform |
| `&type` | `elements`, `resources` | *required* | What to import/export |
| `&mode` | `preview`, `execute` | `preview` | Preview or execute (import only) |
| `&output` | `display`, `file`, `download` | `display` | Output method (export only) |
| `&file` | string | | Filename or path |

### Element Export Parameters

| Parameter | Default | Description |
|-----------|---------|-------------|
| `&elementTypes` | all | Comma-separated: categories,templates,chunks,tvs,snippets,plugins |
| `&category` | | Filter by category name |

### Resource Export Parameters

| Parameter | Default | Description |
|-----------|---------|-------------|
| `&parents` | | Comma-separated parent IDs |
| `&depth` | `10` | Depth to traverse |
| `&templates` | | Filter by template names |
| `&includeTVs` | `1` | Include TV values |
| `&includeContent` | `1` | Include content field |
| `&includeUnpublished` | `0` | Include unpublished resources |

### Import Parameters

| Parameter | Default | Description |
|-----------|---------|-------------|
| `&file` | *required* | Path to JSON file |
| `&parentId` | `0` | Parent ID for resources |
| `&update` | `0` | Update existing elements |
| `&defaultTemplate` | | Default template for resources |

## File Structure

```
core/components/modxtransfer/
├── controllers/
│   └── index.class.php
├── docs/
│   ├── changelog.txt
│   ├── license.txt
│   └── readme.txt
├── elements/
│   └── snippets/
│       └── modxtransfer.snippet.php
├── lexicon/
│   └── en/
│       ├── default.inc.php
│       └── properties.inc.php
├── model/
│   └── modxtransfer/
│       ├── modxtransfer.class.php
│       ├── elements/
│       │   └── modxtransferelements.class.php
│       └── resources/
│           └── modxtransferresources.class.php
├── processors/
│   └── mgr/
│       ├── export/
│       │   ├── elements.class.php
│       │   └── resources.class.php
│       └── import/
│           ├── elements.class.php
│           └── resources.class.php
└── templates/
    └── mgr/
        └── home.tpl

assets/components/modxtransfer/
├── connector.php
├── css/
│   └── mgr/
│       └── modxtransfer.css
└── js/
    └── mgr/
        ├── modxtransfer.js
        ├── sections/
        │   └── home.js
        └── widgets/
            ├── export.panel.js
            ├── home.panel.js
            └── import.panel.js
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT License - see [LICENSE](LICENSE) for details.

## Author

Built with ❤️ for the MODX Community

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.
