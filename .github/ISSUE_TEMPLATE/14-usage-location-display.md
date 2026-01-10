---
name: Implement Usage Location Display
about: Show where each asset is used across posts, pages, and blocks
title: '[FEATURE] Implement Usage Location Display'
labels: enhancement, organization
assignees: ''
---

## Feature Description

Display comprehensive usage information showing where each media asset is used across posts, pages, and blocks with visual representation.

## User Story

As a content editor, I want to see exactly where a media file is being used so that I can understand its importance and make informed decisions about editing or deleting it.

## Acceptance Criteria

- [ ] Show all posts using the media
- [ ] Show all pages using the media
- [ ] Show specific blocks using the media
- [ ] Display as attached image vs. inline usage
- [ ] Show featured image usage
- [ ] Link directly to edit pages
- [ ] Visual representation of usage (icons, counts)
- [ ] Show usage in widgets/sidebars
- [ ] Show usage in custom fields

## Technical Considerations

- Parse post content for media references
- Track Gutenberg block usage
- Handle classic editor content
- Index usage data for performance
- Update index on post save
- Handle shortcodes and embeds
- Background processing for initial index

## Related Features

- Usage Visibility (#6)
- Unused Media Flagging (#15)
- Replace Everywhere (#16)

## UI/UX Considerations

- Usage panel in media details
- Click to preview/edit location
- Group by post type
- Show post status (published, draft, etc.)
- Visual indicators for usage type

## Performance Considerations

- Efficient database queries
- Cached usage data
- Incremental updates
- Background indexing

## References

- WordPress Phase 3 tracking issue
- Media usage plugins
