# Changelog

All notable changes to ModxTransfer will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-16

### Added
- Initial release
- Custom Manager Page (CMP) with tabbed interface
- **Export Elements** functionality
  - Categories export with parent relationships
  - Templates export with content and descriptions
  - Chunks export with content
  - Template Variables export with template assignments
  - Snippets export with properties
  - Plugins export with event registrations
  - Filter by category
  - Select specific element types
- **Export Resources** functionality
  - Full resource field export
  - Template Variable values included
  - MIGX JSON data preserved
  - Hierarchy preservation via parent tracking
  - Filter by parent IDs
  - Filter by template names
  - Configurable depth
  - Include/exclude unpublished resources
- **Import Elements** functionality
  - Preview mode to review before importing
  - Execute mode to create elements
  - Update existing elements option
  - Automatic category assignment
  - Template-to-TV assignments restored
  - Plugin event registrations restored
- **Import Resources** functionality
  - Preview mode
  - Execute mode
  - Resources imported as unpublished for review
  - TV values restored including MIGX data
  - Hierarchy reconstruction
- **Snippet** for programmatic access
  - Export to file, display, or download
  - Import with preview or execute modes
- MODX 3.x compatibility
- MIT License

### Technical Details
- Uses MODX 3 namespaced classes
- Memory-efficient getIterator() for large exports
- JSON format with pretty printing
- Proper error handling and validation

## [Unreleased]

### Planned
- Transport package for Package Manager installation
- File upload support in CMP (in addition to file path)
- Batch delete after export with confirmation
- Progress indicator for large imports
- Resource tree picker for parent selection
- Template dropdown for resource filtering
