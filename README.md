# Media Support - thisismyurl

Media Hub for the thisismyurl.com Shared Code Suite - parent for image/video/audio processing plugins with shared media optimization and transcoding logic.

## Overview

This repository serves as the central hub for media processing capabilities in the TIMU (thisismyurl) suite. It coordinates with specialized spoke modules for:

- **Image Processing** - Modern formats, optimization, and editing
- **Vault** - Versioning, asset replacement, and audit trails
- **Documents** - PDF processing and security
- **Automation** - CLI tools and batch operations
- **AI** - Intelligent tagging and accessibility features

## Feature Roadmap

We've broken down a comprehensive list of ideas into actionable features. See our planning documents:

- **[IDEAS-SUMMARY.md](IDEAS-SUMMARY.md)** - Quick overview of all features (58+ ideas across 6 repos)
- **[IDEAS-BREAKDOWN.md](IDEAS-BREAKDOWN.md)** - Detailed breakdown of features for this repository (17 features)
- **[ISSUE-CREATION-GUIDE.md](ISSUE-CREATION-GUIDE.md)** - Guide for creating GitHub issues from templates

### Ready-to-Use Issue Templates

We have 17 ready-to-use issue templates in `.github/ISSUE_TEMPLATE/` for:

1. Media Taxonomies (Categories and Tags)
2. Smart Attributes (orientation, colors, license)
3. Content-Aware Hashing & De-duplication
4. Grid/List DataViews with Saved Filters
5. Inline Details Panel
6. Usage Visibility
7. Advanced Search Capabilities
8. Metadata Filtering
9. Saved Searches
10. Safe File Replace Feature
11. Comment Threads on Assets
12. Approval States
13. Activity Feed
14. Usage Location Display
15. Unused Media Flagging
16. Replace Everywhere Option
17. Export Media Sets

### Features for Other Repositories

Planning documents for related repositories:

- **[VAULT-IDEAS.md](VAULT-IDEAS.md)** - Versioning, replacement, and audit features
- **[IMAGE-IDEAS.md](IMAGE-IDEAS.md)** - Image optimization, formats, and editing
- **[DOCUMENTS-IDEAS.md](DOCUMENTS-IDEAS.md)** - PDF processing and security
- **[AUTOMATION-IDEAS.md](AUTOMATION-IDEAS.md)** - WP-CLI and batch operations
- **[AI-IDEAS.md](AI-IDEAS.md)** - AI-powered enhancements

## Architecture

### Hub-Spoke Model

```
┌─────────────────────────┐
│   Media Support Hub     │
│   (This Repository)     │
└────────────┬────────────┘
             │
     ┌───────┴───────┐
     │               │
┌────▼────┐     ┌───▼────┐     ┌──────────┐
│  Image  │     │ Vault  │     │Documents │
│  Spoke  │     │ Spoke  │     │  Spoke   │
└─────────┘     └────────┘     └──────────┘
     │               │               │
┌────▼────┐     ┌───▼────┐
│   AI    │     │ Auto-  │
│  Spoke  │     │ mation │
└─────────┘     └────────┘
```

### Integration

- Shared settings and configuration
- Coordinated feature development
- Common APIs and standards
- Unified audit logging

## Requirements

- **PHP**: 8.1+ (8.1.29 or higher)
- **WordPress**: 6.4.0 or higher
- **Core Support**: Requires `core-support-thisismyurl` module

## Installation

This is a module for the TIMU Core system, not a standalone plugin.

1. Ensure TIMU Core Support is installed
2. Clone this repository into your modules directory
3. The module will auto-register with Core

## Development

### Current Status

This repository currently provides:
- Basic hub infrastructure
- Module registration with TIMU Core
- Settings framework
- Admin menu integration

### Planned Features

See the roadmap documents listed above. Implementation is organized in phases:

- **Phase 1**: Core functionality (taxonomies, DataViews, usage tracking)
- **Phase 2**: Enhanced features (smart attributes, search, collaboration)
- **Phase 3**: Advanced capabilities (activity feed, export, automation)

## Contributing

1. Review the feature breakdown documents
2. Check existing issues or create new ones from templates
3. Follow WordPress coding standards
4. Submit pull requests with clear descriptions

## License

Part of the thisismyurl Shared Code Suite.

## Links

- [TIMU Core Support](https://github.com/thisismyurl/core-support-thisismyurl)
- [Feature Planning Documentation](IDEAS-SUMMARY.md)
- [Issue Templates](.github/ISSUE_TEMPLATE/)
