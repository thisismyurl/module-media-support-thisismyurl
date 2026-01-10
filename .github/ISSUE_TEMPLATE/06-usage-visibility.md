---
name: Implement Usage Visibility
about: Show where media is used and enable filtering by usage status
title: '[FEATURE] Implement Usage Visibility'
labels: enhancement, organization
assignees: ''
---

## Feature Description

Show "Attached to" posts/pages and blocks, and let editors filter by "used/unused" assets for cleanup.

## User Story

As a site administrator, I want to see where each media file is used across my site so that I can safely delete unused files and understand the impact of changes.

## Acceptance Criteria

- [ ] Display posts/pages where media is attached
- [ ] Show blocks using the media
- [ ] Add filter for "used" media
- [ ] Add filter for "unused" media
- [ ] Show usage count in media library
- [ ] Link to locations where media is used
- [ ] Update usage data when posts are saved/deleted
- [ ] Handle featured images
- [ ] Handle media in custom fields

## Technical Considerations

- Query post content for media references
- Index media usage for performance
- Handle Gutenberg blocks parsing
- Consider custom field usage
- Implement efficient database queries
- Provide bulk usage analysis
- Update usage cache on post save

## Related Features

- Unused Media Flagging (#15)
- Inline Details Panel (#5)
- Replace Everywhere (#16)

## Performance Considerations

- Cache usage data
- Background processing for large sites
- Incremental index updates
- Efficient query optimization

## References

- WordPress Phase 3 tracking issue
- GitHub collaboration features
