# Ideas for Vault Plugin Repository

This document contains feature ideas that should be implemented in the **Vault Plugin** repository, focused on asset versioning, replacement, and audit capabilities.

## Features Overview

The Vault plugin should handle:
- Safe asset replacement with version history
- Non-destructive edit tracking
- Approval workflows
- Comprehensive audit trails

---

## Feature 1: Replace & Version Assets Safely

### Description
Editors want to replace a file without breaking existing post references (similar to "Enable Media Replace" plugin), with versioning and audit capabilities.

### User Story
As a content editor, I want to replace an outdated asset while maintaining all references and keeping a history of previous versions so I can roll back if needed.

### Key Requirements
- Replace file without breaking post references
- Maintain attachment ID integrity
- Version tracking
- Rollback capability
- Audit trail of replacements

### Technical Considerations
- Integration with Media Support hub
- Version storage strategy
- Backward compatibility with standard WordPress media
- Performance impact of version storage

---

## Feature 2: Asset Entity with Non-Destructive Edits

### Description
Each upload stores a source plus derived renditions (sizes, formats), alongside an edits log (crop/rotate/flip, aspect ratios) so you can revert or branch.

### User Story
As a designer, I want to apply various edits to an image without losing the original so I can try different versions and revert changes if needed.

### Key Requirements
- Store original source file
- Track all derived renditions
- Log all edit operations
- Revert capability
- Branch edits (create variations)
- Non-destructive transformation pipeline

### Technical Considerations
- Storage strategy for originals and edits
- Edit operation serialization
- Regeneration of derivatives
- Integration with WordPress image sizes
- Memory and storage efficiency

### References
- WordPress Phase 3 "track edits" direction
- Image editing APIs

---

## Feature 3: Attachment Replace with Versioning

### Description
Replacing a file should keep the attachment ID, update derived renditions, and maintain a version stack for rollback.

### User Story
As a site administrator, I want to update an asset to a new version while keeping the ability to restore previous versions if something goes wrong.

### Key Requirements
- Keep attachment ID on replace
- Update all derived renditions automatically
- Maintain version stack
- Version metadata (date, user, reason)
- Rollback to any previous version
- Compare versions visually
- Version cleanup policies

### Technical Considerations
- Version storage location
- Disk space management
- Version limit policies
- Integration with CDN/caching
- Database schema for versions

### References
- "Enable Media Replace" plugin as precedent
- User demand signals

---

## Feature 4: Approvals, Comments, and Audit Trail

### Description
Add lightweight review states ("Draft/Approved/Deprecated"), per-asset comments, and an audit log of who changed what and when.

### User Story
As a team lead, I want to review and approve media assets before they're used in production and see a complete history of all changes made to assets.

### Key Requirements
- Review states: Draft, Approved, Deprecated
- Per-asset comment threads
- Comprehensive audit log
- User attribution for all changes
- Timestamp tracking
- Email notifications for state changes
- Role-based permissions for approval

### Technical Considerations
- Integration with WordPress user roles
- Comment system (native vs. custom)
- Audit log storage and retention
- Performance impact of logging
- Privacy compliance (GDPR)

### References
- WordPress Phase 3 collaboration emphasis
- make.wordpress.org collaboration discussions

---

## Feature 5: Maintain Version History

### Description
Comprehensive version history system allowing rollback and branching of edits.

### User Story
As a content manager, I want to see the complete history of an asset including all versions, edits, and replacements so I can understand its evolution and restore any previous state.

### Key Requirements
- Complete version timeline
- Visual version comparison
- Rollback to any version
- Branch from any version
- Version annotations/notes
- Version diff visualization
- Storage optimization for versions

### Technical Considerations
- Efficient version storage
- Delta storage vs. full copies
- Version browsing UI
- Integration with media library
- API for programmatic access

---

## Implementation Priority

### Phase 1: Core Versioning
1. Attachment Replace with Versioning (#3)
2. Replace & Version Assets Safely (#1)

### Phase 2: Non-Destructive Editing
3. Asset Entity with Non-Destructive Edits (#2)
4. Maintain Version History (#5)

### Phase 3: Collaboration
5. Approvals, Comments, and Audit Trail (#4)

---

## Integration with Media Support Hub

The Vault plugin should:
- Register as a spoke with the Media Support hub
- Expose versioning APIs for other plugins
- Share audit data with Activity Feed
- Coordinate with Usage Visibility features

---

## Technical Architecture

### Storage Strategy
- Original files in dedicated directory
- Version metadata in custom table
- Edits log as serialized data
- Derived renditions on-demand generation

### Database Schema
```sql
CREATE TABLE vault_versions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attachment_id BIGINT UNSIGNED NOT NULL,
  version_number INT UNSIGNED NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  created_by BIGINT UNSIGNED NOT NULL,
  notes TEXT,
  INDEX idx_attachment_id (attachment_id),
  INDEX idx_version_number (attachment_id, version_number)
);

CREATE TABLE vault_edits (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  version_id BIGINT UNSIGNED NOT NULL,
  edit_type VARCHAR(50) NOT NULL,
  edit_data TEXT NOT NULL,
  applied_at DATETIME NOT NULL,
  applied_by BIGINT UNSIGNED NOT NULL,
  INDEX idx_version_id (version_id)
);
```

### APIs to Expose
- `vault_create_version()`
- `vault_rollback_version()`
- `vault_get_versions()`
- `vault_apply_edit()`
- `vault_revert_edit()`

---

## User Roles and Permissions

- **Contributor**: Can upload, versions saved automatically
- **Editor**: Can create versions, rollback, approve
- **Administrator**: Full version management, cleanup, policies

---

## References

- Plugin demand as proxy for need (Enable Media Replace)
- Phase 3 "track edits" direction
- make.wordpress.org collaboration discussions
