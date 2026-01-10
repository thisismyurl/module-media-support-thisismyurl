---
name: Add Media Taxonomies (Categories and Tags)
about: Implement hierarchical categories and flat tags for media organization
title: '[FEATURE] Add Media Taxonomies (Categories and Tags)'
labels: enhancement, organization
assignees: ''
---

## Feature Description

Implement "Media Categories" and "Media Tags" (hierarchical + flat) to enable virtual organization without rigid folder paths.

## User Story

As a content editor, I want to organize my media files using categories and tags so that I can find and manage assets more efficiently without being constrained by physical folder structures.

## Acceptance Criteria

- [ ] Add hierarchical "Media Categories" taxonomy
- [ ] Add flat "Media Tags" taxonomy
- [ ] Enable virtual organization independent of physical file storage
- [ ] Keep uploads on disk organized by date (WordPress default)
- [ ] Expose virtual organization in media library UI
- [ ] Support bulk assignment of categories/tags
- [ ] Add filter UI in media library for taxonomies

## Technical Considerations

- Register custom taxonomies for attachment post type
- Ensure compatibility with existing media library
- Follow WordPress taxonomy registration best practices
- Consider performance impact of taxonomy queries

## Related Features

- Smart Attributes (#2)
- Advanced Search (#7)
- DataViews Implementation (#4)

## References

- WordPress Core: Taxonomies API
- Popular plugins: FileBird, Real Media Library, WP Media Folder, Media Library Assistant, Enhanced Media Library
