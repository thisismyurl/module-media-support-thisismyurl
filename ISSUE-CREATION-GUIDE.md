# Issue Creation Guide

This guide explains how to use the idea breakdown documents to create individual GitHub issues.

## Overview

The comprehensive idea list from the original issue has been broken down into structured, actionable feature requests organized by repository. This organization makes it easy to create focused GitHub issues that can be implemented independently.

## Repository Organization

### Media Support Repository (This Repo)
**File**: `IDEAS-BREAKDOWN.md` + `.github/ISSUE_TEMPLATE/01-17-*.md`

Contains 17 feature ideas focused on:
- Organization & navigation (taxonomies, smart attributes)
- Search & filtering capabilities
- Usage tracking and management
- Collaboration features (comments, approvals)
- Data management (import/export)

**Ready-to-use issue templates** are available in `.github/ISSUE_TEMPLATE/` for each feature.

### Vault Plugin Repository
**File**: `VAULT-IDEAS.md`

Contains 5 features focused on:
- Asset versioning and replacement
- Non-destructive edits
- Approval workflows
- Comprehensive audit trails

### Image Repository
**File**: `IMAGE-IDEAS.md`

Contains 16 features focused on:
- Modern image formats (WebP, AVIF)
- Performance optimization
- Rich editing capabilities
- Security (SVG, EXIF)
- Accessibility (alt text)

### Documents Module (New Repository)
**File**: `DOCUMENTS-IDEAS.md`

Contains features focused on:
- PDF processing and security
- Document preview generation
- Metadata extraction
- Role-based access control

### Automation Module (New Repository)
**File**: `AUTOMATION-IDEAS.md`

Contains features focused on:
- WP-CLI integration
- Batch processing
- Scheduled tasks
- Automated workflows

### AI Module (New Repository)
**File**: `AI-IDEAS.md`

Contains features focused on:
- AI-powered alt text generation
- Content recognition
- Smart tagging
- Quality analysis
- Accessibility enhancement

## How to Create Issues

### For Media Support Repository (This Repo)

**Option 1: Use Issue Templates (Recommended)**

1. Go to the GitHub repository
2. Click "Issues" → "New Issue"
3. Select one of the feature templates (01-17)
4. Review and adjust the template content
5. Submit the issue

**Option 2: Manual Creation**

1. Open `IDEAS-BREAKDOWN.md`
2. Find the feature you want to implement
3. Copy the feature description
4. Create a new issue with:
   - Title: `[FEATURE] <Feature Name>`
   - Label: `enhancement`
   - Body: Feature description from the breakdown
   - Additional sections: User Story, Acceptance Criteria, Technical Considerations

### For Other Repositories

1. Open the relevant ideas file (e.g., `VAULT-IDEAS.md`)
2. Each feature is documented with:
   - Description
   - User Story (where applicable)
   - Key Requirements
   - Technical Considerations
   - Related Features
   - References
3. Copy the feature details to create a new issue in the target repository
4. Use the format:
   ```markdown
   ## Feature Description
   [Copy from ideas file]
   
   ## User Story
   [Copy from ideas file]
   
   ## Key Requirements/Acceptance Criteria
   [Copy from ideas file as checklist]
   
   ## Technical Considerations
   [Copy from ideas file]
   
   ## Related Features
   [List cross-references]
   
   ## References
   [Copy from ideas file]
   ```

## Issue Template Format

All issue templates follow this structure:

```markdown
---
name: Feature Name
about: Brief description
title: '[FEATURE] Feature Name'
labels: enhancement, [category]
assignees: ''
---

## Feature Description
Clear description of what this feature does

## User Story
As a [role], I want [goal] so that [benefit]

## Acceptance Criteria
- [ ] Criterion 1
- [ ] Criterion 2
- [ ] Criterion 3

## Technical Considerations
- Technical detail 1
- Technical detail 2

## Related Features
- Feature #1
- Feature #2

## References
- Link 1
- Link 2
```

## Priority Guidelines

Each breakdown document includes an "Implementation Priority" section with suggested phases:

- **Phase 1**: Core functionality, high impact, foundational features
- **Phase 2**: Enhanced features that build on Phase 1
- **Phase 3**: Nice-to-have features, advanced capabilities
- **Phase 4**: Future enhancements, experimental features

Use these priorities to help plan development roadmaps.

## Cross-Repository Dependencies

Some features depend on or enhance features in other repositories. These are noted in the "Related Features" sections with references like:

- `(#1)` - Feature in the same repository
- `(Vault repo)` - Feature in the Vault repository
- `(Image repo)` - Feature in the Image repository

When creating issues, note these dependencies in the issue description.

## Labels to Use

Suggested labels for issues:

### Type Labels
- `enhancement` - New feature
- `bug` - Bug fix
- `documentation` - Documentation update
- `technical-debt` - Code refactoring/cleanup

### Category Labels (Media Support)
- `organization` - Organization and taxonomy features
- `search` - Search and filtering
- `collaboration` - Team workflow features
- `ui` - User interface improvements
- `accessibility` - Accessibility enhancements
- `storage` - Storage and file management
- `migration` - Import/export features
- `core-functionality` - Essential features

### Priority Labels
- `priority-high` - Phase 1 features
- `priority-medium` - Phase 2 features
- `priority-low` - Phase 3+ features

### Status Labels
- `needs-design` - Requires design work
- `needs-discussion` - Needs team discussion
- `ready-to-implement` - Approved and ready
- `in-progress` - Currently being worked on
- `blocked` - Blocked by dependencies

## Issue Numbering

Issue templates for this repository are numbered 01-17 for easy reference. When creating issues in other repositories, consider using a similar numbering scheme for consistency.

## Bulk Issue Creation

If you want to create all issues at once:

1. Use GitHub's API or CLI (`gh` tool)
2. Script to read template files and create issues
3. Example using GitHub CLI:

```bash
# For each template file
for template in .github/ISSUE_TEMPLATE/*.md; do
  gh issue create --template "$template"
done
```

## Tracking Progress

Consider creating a project board to track implementation:

1. Create columns: Backlog, To Do, In Progress, Review, Done
2. Add issues to the board
3. Move issues through columns as work progresses
4. Use milestones for phases (Phase 1, Phase 2, etc.)

## Related Documentation

- `IDEAS-BREAKDOWN.md` - Complete breakdown for this repository
- `VAULT-IDEAS.md` - Features for Vault plugin
- `IMAGE-IDEAS.md` - Features for Image repository
- `DOCUMENTS-IDEAS.md` - Features for Documents module
- `AUTOMATION-IDEAS.md` - Features for Automation module
- `AI-IDEAS.md` - Features for AI module

## Questions or Feedback

If you have questions about any feature or want to suggest modifications:

1. Create a discussion in the repository
2. Reference the feature by name or number
3. Provide specific feedback or questions

## Next Steps

1. Review all breakdown documents
2. Prioritize features based on your needs
3. Create issues for Phase 1 features first
4. Set up project boards for tracking
5. Begin implementation following the technical guidelines in each feature description

---

**Note**: These breakdowns are living documents. As features are implemented or requirements change, update the relevant documents to keep them current.
