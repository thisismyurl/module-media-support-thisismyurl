# media-support-thisismyurl

Media Hub for the thisismyurl.com Shared Code Suite - parent for image/video/audio processing plugins with shared media optimization and transcoding logic.

## Purpose

This repository serves as the **Media Hub** - the central coordination layer for all media processing across the TIMU suite. It provides:

- Shared media optimization and transcoding infrastructure
- Cross-media-type features (usage tracking, collections, policies)
- Common media processing APIs and interfaces
- Batch processing coordination
- Media insights and analytics

## Repository Organization

The TIMU Media Suite is organized into specialized repositories:

- **media-support-thisismyurl** (this repository): Media Hub - shared infrastructure for ALL media types
- **plugin-images-thisismyurl**: Image-specific features (filters, editing, smart cropping, etc.)
- **plugin-video-thisismyurl**: Video-specific features (if applicable)
- **plugin-audio-thisismyurl**: Audio-specific features (if applicable)

### What Goes Here vs. Image Repository

**Use this repository for:**
- Features that work across multiple media types (images, video, audio)
- Shared optimization and transcoding logic
- Media usage tracking and analytics
- Collections and organization features for all media
- Permission policies and access control
- Batch processing infrastructure

**Use plugin-images-thisismyurl for:**
- Image-specific editing tools (crop, rotate, filters)
- Smart image tagging and categorization
- Face/object detection in images
- Image-specific social media features
- Interactive image galleries and lightboxes
- Image format conversions and optimizations

See [FEATURE-IDEAS.md](FEATURE-IDEAS.md) for a comprehensive breakdown of feature ideas by repository.

## Architecture

This module extends the TIMU Core architecture as a "Hub" module. It registers capabilities that other modules (like plugin-images-thisismyurl) can depend on.

## Requirements

- PHP 8.1.29+
- WordPress 6.4.0+
- TIMU Core Support module (core-support-thisismyurl)
