---
name: Add Comment Threads on Assets
about: Enable team feedback and discussion on media assets
title: '[FEATURE] Add Comment Threads on Assets'
labels: enhancement, collaboration
assignees: ''
---

## Feature Description

Enable comment threads on media assets for team feedback and collaboration.

## User Story

As a team member, I want to leave comments on media files so that I can provide feedback, ask questions, or discuss assets with my colleagues.

## Acceptance Criteria

- [ ] Add comments to attachment posts
- [ ] Display comments in media details panel
- [ ] Support threaded replies
- [ ] @ mention users
- [ ] Email notifications for new comments
- [ ] Edit/delete own comments
- [ ] Moderate comments (admin)
- [ ] Show comment count in media library
- [ ] Filter by commented/uncommented

## Technical Considerations

- Use WordPress comments system or custom implementation
- Extend comments for attachment post type
- Implement real-time updates (optional)
- Email notification system
- User mention parsing
- Permission checking

## Related Features

- Inline Details Panel (#5)
- Approval States (#12)
- Activity Feed (#13)

## Collaboration Considerations

- Align with WordPress Phase 3 collaboration focus
- Consider real-time collaboration (future)
- Integration with team workflows

## UI/UX Considerations

- Comment thread UI in details panel
- Rich text editor for comments
- User avatars
- Timestamp display
- Comment count badge

## References

- WordPress comments system
- Gutenberg Phase 3 collaboration
- make.wordpress.org collaboration discussions
