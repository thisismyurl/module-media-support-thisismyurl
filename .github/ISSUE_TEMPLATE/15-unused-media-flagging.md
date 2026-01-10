---
name: Add Unused Media Flagging
about: Flag unused media for cleanup with batch operations
title: '[FEATURE] Add Unused Media Flagging'
labels: enhancement, organization
assignees: ''
---

## Feature Description

Automatically flag unused media assets and provide batch operations for cleanup to reduce storage costs and clutter.

## User Story

As a site administrator, I want to identify and bulk-delete unused media files so that I can free up storage space and keep my media library organized.

## Acceptance Criteria

- [ ] Identify media not used anywhere on site
- [ ] Flag unused media with visual indicator
- [ ] Filter to show only unused media
- [ ] Show last used date (if previously used)
- [ ] Bulk select unused media
- [ ] Bulk delete with confirmation
- [ ] Export list of unused media
- [ ] Safe mode (prevent accidental deletion)
- [ ] Exclude recently uploaded files

## Technical Considerations

- Build on Usage Visibility (#6)
- Scan all posts, pages, widgets, custom fields
- Check featured images
- Handle shortcodes and blocks
- Allow exclusion rules
- Verification before deletion
- Backup option integration

## Related Features

- Usage Visibility (#6)
- Usage Location Display (#14)
- Content-Aware Hashing (#3)

## Safety Features

- Confirmation dialog for bulk delete
- Dry-run mode to preview
- Exclusion by date (keep recent uploads)
- Exclusion by tag/category
- Restore from trash
- Integration with backup plugins

## UI/UX Considerations

- "Unused" filter in media library
- Visual badge for unused items
- Bulk action menu
- Cleanup wizard
- Storage savings estimate

## Performance Considerations

- Background scanning
- Progress indicator for large libraries
- Scheduled checks for unused media
- Efficient database queries

## References

- Media cleanup plugins
- Phase 3 tracking issue mention
