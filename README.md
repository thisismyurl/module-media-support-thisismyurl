# media-support-thisismyurl

Media Hub for the thisismyurl.com Shared Code Suite - parent for image/video/audio processing plugins with shared media optimization and transcoding logic.

## Features

This module provides comprehensive social media image enhancement features:

### 10 Social Media Enhancement Features

1. **Instagram-Style Filters** - Vintage, Warm, Cool, High Contrast, Grayscale
2. **Auto Social Crop Presets** - One-click crop for Instagram, Facebook, LinkedIn, Pinterest, TikTok, Twitter, YouTube
3. **Text Overlays** - Quick headlines, hashtags, and call-to-action text
4. **Watermark & Logo Placement** - Brand protection with position presets
5. **Hashtag & Caption Generator** - Auto-generated hashtags based on image content
6. **Multi-Platform Export** - Export in platform-specific sizes with ZIP bundling
7. **Social Preview Simulator** - Preview images across all platforms
8. **Branded Templates** - Save and reuse custom templates
9. **REST API Integration** - Full REST API at `/wp-json/timu-media/v1/`
10. **Media Library Integration** - Seamless WordPress Media Library enhancement

## Documentation

See [FEATURES.md](FEATURES.md) for comprehensive documentation including:
- Detailed feature descriptions
- Usage examples
- REST API reference
- Developer guide
- Configuration options
- Troubleshooting

## Requirements

- PHP 8.1.29 or higher
- WordPress 6.4.0 or higher
- Core Support plugin (thisismyurl-core-suite)
- GD or Imagick PHP extension

## Installation

This is a module loaded by the TIMU Core Module Loader. It is NOT a standalone WordPress plugin.

1. Ensure Core Support is installed and active
2. Place this module in the appropriate directory
3. Activate through the Core Support interface

## Architecture

This module extends `TIMU_Spoke_Base` and provides:
- Image processing infrastructure
- Social media optimization
- Batch processing capabilities
- Policy management
- REST API endpoints
- AJAX handlers

## Classes

- `Image_Processor` - Base image processing with GD/Imagick support
- `Filters_Manager` - Instagram-style filters
- `Crop_Manager` - Social media crop presets
- `Text_Overlay_Manager` - Text overlay engine
- `Watermark_Manager` - Watermark and logo placement
- `Hashtag_Generator` - Hashtag and caption generation
- `Export_Manager` - Multi-platform export
- `Preview_Simulator` - Social preview simulation
- `Template_Manager` - Branded template system
- `Ajax_Handler` - AJAX request handling
- `REST_API` - REST API endpoints

