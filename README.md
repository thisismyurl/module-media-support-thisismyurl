# media-support-thisismyurl
Media Hub for the thisismyurl.com Shared Code Suite - parent for image/video/audio processing plugins with shared media optimization and transcoding logic

## Modules

### Video Support Module (`module-videos-support-thisismyurl.php`)

Advanced video processing, editing, and playback enhancements for WordPress.

#### Admin-Side Features

- **Drag-and-Drop Video Editor** - Trim, crop, and merge clips directly in the Media Library
- **Auto-Generate Thumbnails & Preview GIFs** - Create multiple thumbnail options and animated previews
- **Video Chapters & Timestamps** - Add chapters in the Media Library with clickable timestamps in posts
- **Bulk Video Optimization** - Compress videos without losing quality; auto-generate multiple resolutions
- **Smart Video Tagging** - Detect objects/scenes and suggest tags for better organization
- **Video Collections & Playlists** - Group videos into playlists for campaigns or tutorials
- **Inline Caption & Subtitle Editor** - Upload or auto-generate captions; edit them directly in WordPress
- **Brand Overlay & Watermark Tool** - Apply logos or text overlays to videos for branding
- **Social Media Export Presets** - Export videos in Instagram Reel, TikTok, YouTube Shorts formats
- **Video Analytics Dashboard** - Track views, engagement, and performance per video
- **Replace Video Without Breaking Links** - Safe replace feature with version history
- **Scheduled Video Publishing** - Upload now, schedule for later—sync with post scheduler
- **Interactive Thumbnail Designer** - Add text, stickers, and filters to thumbnails inside WordPress
- **Video Metadata Control** - Manage SEO fields, schema, and Open Graph tags for better social previews
- **Integration with Canva & Adobe Express** - Create video intros/outros and import directly into WordPress

#### End-User Experience Features

- **Adaptive Streaming (HLS/DASH)** - Serve videos in multiple resolutions for smooth playback
- **Picture-in-Picture Mode** - Let users keep watching while scrolling
- **Interactive Video Hotspots** - Clickable areas inside videos for links or product info
- **Video Chapters in Player** - Clickable chapter navigation for tutorials or long-form content
- **Social Sharing from Video Player** - Share specific timestamps or clips directly to social platforms
- **Lightbox Video Playback** - Open videos in a fullscreen modal for distraction-free viewing
- **Autoplay with Smart Controls** - Autoplay muted videos on scroll with hover-to-unmute
- **Video Reaction & Comment Overlay** - Allow reactions or comments tied to timestamps
- **360° & VR Video Support** - Interactive panoramic videos for immersive experiences
- **End-Screen CTAs & Links** - Add clickable calls-to-action at the end of videos

#### Usage

The video support module is automatically loaded by the Media Hub when available. Configure video features through the TIMU Core settings panel under the Video Support section.

**Shortcode:**
```
[timu_video id="123" chapters="true" pip="true" sharing="true"]
```

**Parameters:**
- `id` - Video attachment ID (required)
- `chapters` - Enable chapter navigation (default: true)
- `pip` - Enable picture-in-picture mode (default: true)
- `sharing` - Enable social sharing buttons (default: true)
