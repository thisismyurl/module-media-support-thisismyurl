# Contributing to TIMU Media Support

Thank you for your interest in contributing to the TIMU Media Suite!

## Repository Structure

Before contributing, it's important to understand where different features should be implemented:

### This Repository: media-support-thisismyurl (Media Hub)

This is the **shared infrastructure** repository for ALL media types. Features here should:

- Work across multiple media types (images, video, audio)
- Provide common APIs and interfaces
- Handle cross-cutting concerns like analytics, policies, and permissions

**Examples of features that belong here:**
- Media usage tracking and analytics
- Media collections and organization
- Batch processing infrastructure
- Media policies and permissions
- Shared optimization logic
- Transcoding infrastructure

### Image-Specific Repository: plugin-images-thisismyurl

Image-specific features should go in the dedicated images repository, not here.

**Examples of features that belong in plugin-images-thisismyurl:**
- Image filters and editing tools
- Smart image tagging and categorization
- Face/object detection
- Image-specific cropping and resizing
- Instagram-style filters
- Interactive image galleries
- Before/after sliders
- Image hotspots
- Social media image optimization

### When in Doubt

If a feature:
- **Only works with images** → Use plugin-images-thisismyurl
- **Works with any media type** → Use this repository (media-support-thisismyurl)
- **Is specific to video or audio** → Use the appropriate dedicated repository

## Submitting Issues

When creating issues:

1. **Check the repository** - Make sure you're in the right place:
   - Image features → plugin-images-thisismyurl
   - Video features → plugin-video-thisismyurl (if exists)
   - Cross-media features → media-support-thisismyurl (this repository)

2. **Reference existing documentation** - See [FEATURE-IDEAS.md](FEATURE-IDEAS.md) for how features are categorized

3. **Be specific** - Include:
   - Clear description of the feature
   - Use cases
   - Which media types it applies to
   - Any technical requirements

## Code Standards

- Follow WordPress coding standards
- Use PHP 8.1+ features and type declarations
- Write secure code (sanitize inputs, escape outputs)
- Add inline documentation for complex logic
- Extend the TIMU Core architecture appropriately

## Testing

- Test features with different media types
- Verify performance with large media libraries
- Ensure mobile compatibility
- Test with different WordPress versions (6.4.0+)

## Pull Requests

1. Create a feature branch from the main branch
2. Make focused, minimal changes
3. Include clear commit messages
4. Update documentation if needed
5. Test thoroughly before submitting

## Questions?

If you're unsure where a feature belongs, open a discussion issue and we'll help you determine the right repository.
