# Social Media Image Enhancement Features

This document provides detailed information about the 10 new social media image enhancement features added to the Media Support plugin.

## Features Overview

### 1. Instagram-Style Filters

Apply professional filters to your images directly in the WordPress Media Library.

**Available Filters:**
- **Vintage**: Warm sepia tones with reduced contrast for a classic look
- **Warm**: Boost warm tones and reduce blues for a cozy feel
- **Cool**: Enhance cool blues and reduce warm tones for a modern look
- **High Contrast**: Increase contrast for dramatic, bold images
- **Grayscale**: Classic black and white conversion

**Usage:**
- Open any image in the Media Library
- Click the desired filter button in the "Filters" section
- A new filtered version is created (original preserved)

**REST API:**
```
GET  /wp-json/timu-media/v1/filters
POST /wp-json/timu-media/v1/filters/apply
```

### 2. Social Media Crop Presets

One-click cropping for major social platforms with optimal dimensions.

**Available Presets:**
- **Instagram Square**: 1080x1080 (1:1)
- **Instagram Portrait**: 1080x1350 (4:5)
- **Instagram Story**: 1080x1920 (9:16)
- **Facebook Post**: 1200x630 (1.91:1)
- **Facebook Cover**: 820x312 (2.63:1)
- **Twitter/X Post**: 1200x675 (16:9)
- **LinkedIn Post**: 1200x627 (1.91:1)
- **Pinterest Pin**: 1000x1500 (2:3)
- **TikTok**: 1080x1920 (9:16)
- **YouTube Thumbnail**: 1280x720 (16:9)

**Features:**
- Smart center cropping keeps subjects centered
- Maintains aspect ratio integrity
- Non-destructive (creates new file)

**REST API:**
```
GET  /wp-json/timu-media/v1/crop/presets
POST /wp-json/timu-media/v1/crop/apply
```

### 3. Text Overlays

Add text directly to images for social media posts.

**Features:**
- Customizable font size (12-200px)
- Color picker for text and background
- Adjustable background opacity
- Multiple position presets:
  - Top, Top-Left, Top-Right
  - Center
  - Bottom, Bottom-Left, Bottom-Right

**Supported Libraries:**
- Imagick (preferred for best quality)
- GD (fallback with built-in fonts)

**Usage:**
```php
Text_Overlay_Manager::add_text_overlay(
    $attachment_id,
    'Your Text Here',
    array(
        'font_size'   => 48,
        'font_color'  => '#FFFFFF',
        'bg_color'    => '#000000',
        'bg_opacity'  => 0.5,
        'position'    => 'bottom'
    )
);
```

### 4. Watermark & Logo Placement

Protect your brand with automatic watermark placement.

**Position Options:**
- Top-Left
- Top-Right
- Bottom-Left (default)
- Bottom-Right
- Center

**Features:**
- Adjustable opacity (0-100%)
- Automatic padding for clean appearance
- Works with any image format (PNG, JPEG, GIF)
- Supports both Imagick and GD

**Settings:**
Configure a default watermark in plugin settings:
- Navigate to Media Settings
- Enter "Default Watermark Attachment ID"
- This will be used as the default logo

### 5. Hashtag & Caption Generator

Automatically generate relevant hashtags based on image metadata.

**Categories:**
- General
- Business
- Travel
- Food
- Fashion
- Technology
- Nature
- Fitness

**Features:**
- Extracts keywords from image title, alt text, and caption
- Removes common stop words
- Generates 10 relevant hashtags per request
- Caption generation with 3 styles: short, medium, long

**REST API:**
```
POST /wp-json/timu-media/v1/hashtags/generate
```

**Usage:**
```php
$hashtags = Hashtag_Generator::generate_hashtags(
    $attachment_id,
    'travel',
    10
);
// Returns: ['#travel', '#wanderlust', '#travelgram', ...]

$caption = Hashtag_Generator::generate_caption(
    $attachment_id,
    'medium'
);
```

### 6. Multi-Platform Export

Export images optimized for multiple platforms simultaneously.

**Features:**
- Export to multiple social platforms at once
- Bundle export as ZIP file
- Web optimization with quality control (1-100%)
- Platform-specific dimensions automatically applied

**Usage:**
```php
// Export for multiple platforms
$results = Export_Manager::export_multi_platform(
    $attachment_id,
    array('instagram', 'facebook', 'twitter')
);

// Create ZIP bundle
$zip_path = Export_Manager::export_as_bundle(
    $attachment_id,
    array('instagram', 'facebook')
);

// Optimize for web
$result = Export_Manager::optimize_for_web(
    $attachment_id,
    85 // quality
);
```

### 7. Social Preview Simulator

Preview how your images will appear on different platforms.

**Features:**
- Analyzes image dimensions vs platform requirements
- Identifies if cropping is needed
- Shows how image will fit on each platform
- Supports all major platforms

**Preview Data Includes:**
- Target dimensions
- Current dimensions
- Fit type (exact, horizontal-crop, vertical-crop)
- Display dimensions

**REST API:**
```
POST /wp-json/timu-media/v1/preview
```

**Usage:**
```php
// Preview for specific platform
$preview = Preview_Simulator::generate_preview(
    $attachment_id,
    'instagram'
);

// Preview for all platforms
$previews = Preview_Simulator::generate_all_previews(
    $attachment_id
);
```

### 8. Branded Templates

Save and reuse your brand's image processing workflows.

**Features:**
- Combine multiple operations into one template
- Apply filters, crops, text, and watermarks in sequence
- Save unlimited custom templates
- Default templates included:
  - Instagram Square Post
  - Instagram Story
  - Facebook Post

**Template Structure:**
```php
array(
    'name'       => 'My Brand Template',
    'settings'   => array(
        'filter'             => 'warm',
        'crop_preset'        => 'instagram_square',
        'text'               => 'Your Brand',
        'text_size'          => 48,
        'text_color'         => '#FFFFFF',
        'text_position'      => 'bottom',
        'watermark_id'       => 123,
        'watermark_position' => 'bottom-right',
        'watermark_opacity'  => 80
    )
);
```

**REST API:**
```
GET  /wp-json/timu-media/v1/templates
POST /wp-json/timu-media/v1/templates/apply
```

### 9. REST API Integration

Complete REST API for programmatic access.

**Base URL:** `/wp-json/timu-media/v1/`

**Endpoints:**
- `GET /filters` - List available filters
- `POST /filters/apply` - Apply filter to image
- `GET /crop/presets` - List crop presets
- `POST /crop/apply` - Crop image to preset
- `POST /hashtags/generate` - Generate hashtags
- `POST /preview` - Generate platform preview
- `GET /templates` - List templates
- `POST /templates/apply` - Apply template

**Authentication:**
All endpoints require `upload_files` capability.

### 10. AJAX Integration

Seamless integration with WordPress Media Library via AJAX.

**Available Actions:**
- `timu_apply_filter`
- `timu_crop_image`
- `timu_add_text_overlay`
- `timu_add_watermark`
- `timu_generate_hashtags`
- `timu_generate_caption`
- `timu_export_multi_platform`
- `timu_generate_preview`
- `timu_apply_template`

**Security:**
- Nonce verification on all requests
- Capability checks (`upload_files`)
- Input sanitization and validation

## Plugin Settings

Navigate to **Media Settings** in WordPress admin to configure:

### Feature Toggles
Each feature can be individually enabled/disabled:
- Enable Image Filters
- Enable Social Media Crop Presets
- Enable Text Overlays
- Enable Watermark Placement
- Enable Hashtag Generator
- Enable Multi-Platform Export
- Enable Social Preview Simulator
- Enable Branded Templates

### Global Settings
- **Default Watermark Attachment ID**: Set a default logo/watermark for all operations

## Technical Requirements

### PHP Requirements
- PHP 8.1.29 or higher
- WordPress 6.4.0 or higher

### Image Library Support
- **Imagick** (recommended): Full feature support with best quality
- **GD** (fallback): Core features supported with some limitations

### Optional Dependencies
- **ZipArchive**: Required for bundle export feature

## Developer Usage

### Basic Example
```php
use TIMU\MediaSupport\Filters_Manager;
use TIMU\MediaSupport\Crop_Manager;

// Apply a filter
$result = Filters_Manager::apply_filter(
    $attachment_id,
    'vintage'
);

// Crop to Instagram square
$result = Crop_Manager::crop_to_preset(
    $attachment_id,
    'instagram_square'
);
```

### Advanced Template Example
```php
use TIMU\MediaSupport\Template_Manager;

// Create custom template
Template_Manager::save_template(
    'My Brand Post',
    array(
        'filter'             => 'warm',
        'crop_preset'        => 'instagram_square',
        'text'               => 'MyBrand',
        'text_size'          => 60,
        'text_color'         => '#FF6B6B',
        'text_position'      => 'bottom-right',
        'watermark_id'       => 456,
        'watermark_position' => 'bottom-left',
        'watermark_opacity'  => 70
    )
);

// Apply template to image
$result = Template_Manager::apply_template(
    $attachment_id,
    'my-brand-post'
);
```

## Architecture

### Class Structure
```
TIMU\MediaSupport\
├── Image_Processor (base class)
├── Filters_Manager
├── Crop_Manager
├── Text_Overlay_Manager
├── Watermark_Manager
├── Hashtag_Generator
├── Export_Manager
├── Preview_Simulator
├── Template_Manager
├── Ajax_Handler
└── REST_API
```

### Non-Destructive Processing
All operations create new files; original images are never modified.

### File Naming Convention
Processed images use descriptive suffixes:
- `image-filter-vintage.jpg`
- `image-crop-instagram_square.jpg`
- `image-text-overlay.jpg`
- `image-watermark.jpg`
- `image-optimized-q85.jpg`

## Troubleshooting

### Images Not Processing
1. Check PHP image library support: `php -m | grep -E 'gd|imagick'`
2. Verify file permissions on upload directory
3. Check PHP memory limit (recommend 256M+)

### Watermarks Not Appearing
1. Verify watermark attachment ID is valid
2. Check watermark image format (PNG recommended)
3. Ensure GD or Imagick is available

### ZIP Export Not Working
1. Verify ZipArchive extension: `php -m | grep zip`
2. Check server disk space
3. Review error logs for ZipArchive errors

## Performance Considerations

### Memory Usage
Image processing is memory-intensive. Recommended:
- PHP memory_limit: 256M minimum
- For large images (>4000px): 512M+

### Processing Time
- Filters: ~1-2 seconds per image
- Crops: ~0.5-1 second per image
- Text overlays: ~1-2 seconds per image
- Watermarks: ~1-2 seconds per image

### Optimization Tips
1. Process images on-demand, not in bulk
2. Use Imagick over GD when available
3. Consider background processing for large batches
4. Cache template results

## Security

### Input Validation
- All inputs sanitized and validated
- Nonce verification on AJAX requests
- Capability checks on all operations
- Type checking and bounds validation

### File Handling
- Uses WordPress native functions
- Temporary files properly managed
- Safe file operations (rename > copy)
- Error logging for debugging

## Future Enhancements

Potential features for future releases:
- Animated GIF support
- Video thumbnail generation
- AI-powered crop detection
- Bulk template application
- Social media direct posting
- Analytics integration
- Custom font upload support
- Advanced color grading tools

## Support

For issues, feature requests, or questions:
- Check plugin settings and configuration
- Review WordPress debug logs
- Verify PHP requirements and extensions
- Contact support with specific error messages

## License

This module is part of the TIMU Media Support suite and follows the same licensing terms as the core plugin.
