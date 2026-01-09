---
name: Add Content-Aware Hashing & De-duplication
about: Optional SHA-256 fingerprinting to flag duplicate uploads
title: '[FEATURE] Add Content-Aware Hashing & De-duplication'
labels: enhancement, storage
assignees: ''
---

## Feature Description

Implement optional SHA-256 fingerprinting on upload to flag potential duplicates before storage.

## User Story

As a site administrator, I want to prevent duplicate file uploads so that I can save disk space and avoid media library clutter with files like "IMG_1234(2).jpg".

## Acceptance Criteria

- [ ] Calculate SHA-256 hash on file upload
- [ ] Store hash in attachment metadata
- [ ] Check for existing files with same hash before upload
- [ ] Flag potential duplicates to user
- [ ] Provide option to use existing file or upload anyway
- [ ] Make de-duplication optional (admin setting)
- [ ] Handle hash collisions gracefully
- [ ] Provide bulk hash generation for existing files

## Technical Considerations

- Use PHP hash() function with sha256 algorithm
- Store hash efficiently in postmeta
- Index hash field for fast lookups
- Consider performance impact on upload process
- Handle large files efficiently (stream processing)
- Provide user-friendly duplicate resolution UI

## Related Features

- Advanced Search (#7)
- Unused Media Flagging (#15)

## Design Recommendations

This is a design recommendation to save storage space and reduce media library sprawl.

## References

- PHP hash functions
- WordPress attachment metadata
