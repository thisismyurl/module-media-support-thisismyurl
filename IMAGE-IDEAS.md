# Ideas for Image Repository

This document contains feature ideas that should be implemented in the **Image Repository**, focused on image processing, optimization, and modern format support.

## Features Overview

The Image repo should handle:
- Modern image formats (WebP, AVIF)
- Responsive images
- Non-destructive editing
- Performance optimization
- Security (SVG, EXIF)
- Alt text assistance

---

## Performance & Modern Formats

### Feature 1: Real-Time Editing Compatibility

**Description**: Keep architecture compatible with Phase 3 real-time work (HTTP autosave + optional WebRTC) for safe multi-editor co-editing of attachment metadata.

**Requirements**:
- HTTP autosave support
- Optional WebRTC integration
- Conflict resolution
- Multi-editor awareness
- Lock/unlock mechanisms

---

### Feature 2: Performance by Default - Modern Formats

**Description**: Support WebP/AVIF formats, responsive images (srcset/sizes), and lazy loading with sensible defaults.

**Requirements**:
- WebP format generation
- AVIF format support
- Responsive images output
- Lazy loading configuration
- Automatic format selection
- Fallback handling

**Note**: WordPress supports WebP upload since 5.8, responsive images since 4.4, lazy loading since 5.5.

---

### Feature 3: Default WebP with Per-Format Overrides

**Description**: Auto-generate WebP renditions on upload with per-type overrides (e.g., keep PNG for logos, AVIF opt-in).

**Requirements**:
- Automatic WebP generation
- Format-specific rules
- AVIF opt-in
- Quality settings per format
- Fallback image serving
- Browser compatibility detection

---

### Feature 4: Responsive Images in Core

**Description**: Ensure all front-end outputs use WordPress's responsive image APIs for automatic browser-based size selection.

**Requirements**:
- srcset generation
- sizes attribute calculation
- Automatic best-size selection
- Integration with themes
- Block editor support

---

### Feature 5: Lazy Loading Policy

**Description**: Use lazy loading for below-the-fold assets with smart exceptions for hero images to avoid LCP regressions.

**Requirements**:
- Automatic lazy loading
- Hero image detection
- LCP optimization
- Configurable thresholds
- Above-the-fold exceptions

---

## Editing & Processing

### Feature 6: Richer Editing in Library

**Description**: Non-destructive cropping/rotating/flip, aspect-ratio presets, and better alt/caption workflows directly in Media Library.

**Requirements**:
- Non-destructive crop
- Rotate and flip
- Aspect ratio presets
- Alt text workflow
- Caption editing
- Inline preview

---

### Feature 7: Cropper with Aspect Presets

**Description**: Implement presets (1:1, 3:2, 4:3, 16:9), rotate, flip—recorded as transforms, not overwrites; derived files regenerated on demand.

**Requirements**:
- Preset aspect ratios (1:1, 3:2, 4:3, 16:9, custom)
- Rotate tool
- Flip horizontal/vertical
- Transform recording
- On-demand regeneration
- Preview before apply

---

### Feature 8: Alt Text Assistant (Local-First)

**Description**: A wizard that nudges users to write meaningful alt text with pattern prompts, warns if empty, uses filename/EXIF hints locally—no cloud calls.

**Requirements**:
- Alt text wizard
- Pattern-based prompts
- Empty alt warnings
- Filename parsing for suggestions
- EXIF data hints
- Local-only processing
- No external API calls
- Accessibility guidelines

---

### Feature 9: Caption/Credit/Licensing Panel

**Description**: Encourage proper attribution—especially when importing via Openverse.

**Requirements**:
- Caption field
- Credit/attribution field
- License dropdown
- Copyright information
- Source URL
- Openverse integration
- Required fields policy

---

## Security & Metadata

### Feature 10: Security & Format Coverage - SVG

**Description**: Safe SVG handling with XSS risk mitigation through sanitizing workflows.

**Requirements**:
- SVG sanitization
- XSS prevention
- Role-based upload permissions
- Sanitizer integration (like "Safe SVG")
- Policy-gated workflows
- Security audit logging

**Note**: WordPress blocks raw SVG uploads by default due to XSS risks.

---

### Feature 11: Security & Format Coverage - PSD

**Description**: PDF/PSD preview generation, EXIF control, and role-based policies.

**Requirements**:
- PDF preview generation
- PSD preview generation
- EXIF data handling
- Role-based access
- Preview quality settings
- Thumbnail generation

---

### Feature 12: Role-Gated SVG Uploads with Sanitization

**Description**: Allow unsafe SVG only for Site & Super Admins per policy; sanitize for other roles via hardened pipeline.

**Requirements**:
- Role-based SVG permissions
- Site Admin: unrestricted
- Super Admin: unrestricted
- Other roles: sanitized only
- Sanitization pipeline
- Clear warnings
- Audit logs

**References**: "Safe SVG" plugin approach

---

### Feature 13: EXIF and Metadata Control

**Description**: Give Editors ability to strip metadata on upload; Admins can enforce stripping (especially geolocation).

**Requirements**:
- Strip metadata option
- Admin enforcement setting
- Selective stripping (keep certain data)
- Geolocation removal
- Privacy protection
- Performance optimization
- Batch processing for existing media

**References**: Common privacy/performance best practices

---

## Discovery & AI

### Feature 14: Discovery & Sourcing - Openverse Integration

**Description**: Integration with Openverse for searching free images in core (appeared in 6.2's efforts).

**Requirements**:
- Openverse search interface
- In-editor integration
- License information
- Attribution auto-fill
- Direct import
- Search filters

---

### Feature 15: Optional Local AI for Tag Suggestions

**Description**: Rule-based or local AI suggestions for tags based on filename, EXIF, or dominant colors.

**Requirements**:
- Filename parsing
- EXIF data analysis
- Color extraction
- Tag suggestions
- Local-only processing
- No external AI calls
- User approval required

---

### Feature 16: Bulk Optimization Wizard

**Description**: Process legacy media with bulk operations and progress tracking.

**Requirements**:
- Select existing media
- Format conversion
- Resize operations
- Compression
- Progress tracking
- Pause/resume
- Rollback capability
- Before/after comparison
- Storage savings report

---

## Implementation Priority

### Phase 1: Performance Essentials
1. Default WebP with Per-Format Overrides
2. Responsive Images in Core
3. Lazy Loading Policy

### Phase 2: Security & Editing
4. Role-Gated SVG Uploads with Sanitization
5. EXIF and Metadata Control
6. Cropper with Aspect Presets
7. Richer Editing in Library

### Phase 3: Enhanced Features
8. Alt Text Assistant
9. Caption/Credit/Licensing Panel
10. Bulk Optimization Wizard

### Phase 4: Advanced Features
11. Real-Time Editing Compatibility
12. Discovery & Sourcing - Openverse Integration
13. Optional Local AI for Tag Suggestions

---

## Integration with Media Support Hub

The Image repo should:
- Register as a spoke with the Media Support hub
- Expose image processing APIs
- Share optimization settings
- Coordinate with Vault for versioning

---

## Technical Architecture

### Image Processing Pipeline
1. Upload
2. Format detection
3. Sanitization (if needed)
4. Metadata extraction
5. Transform application
6. Rendition generation
7. Modern format conversion
8. Storage

### APIs to Expose
- `image_convert_format()`
- `image_apply_transform()`
- `image_optimize()`
- `image_sanitize_svg()`
- `image_strip_exif()`
- `image_generate_srcset()`

---

## References

- WordPress 5.8+ WebP support
- WordPress 4.4+ responsive images
- WordPress 5.5+ lazy loading
- wpbeginner.com, sitesaga.com image optimization guides
- developer.wordpress.org responsive images API
- wp-rocket.me performance best practices
- hostinger.com security guides
- requestmetrics.com EXIF handling
- metaslider.com Openverse integration
- Safe SVG plugin approach
