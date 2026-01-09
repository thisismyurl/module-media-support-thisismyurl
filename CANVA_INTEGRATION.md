# Canva Integration for WordPress Media Hub

A comprehensive integration that brings Canva's design capabilities directly into WordPress, along with support for multiple design platforms through the unified Design Hub.

## Features

### 1. Canva Button in Media Library ✅
- **"Create in Canva"** button integrated directly in the Media Library
- Opens Canva editor in an embedded modal or new tab
- Automatically saves designs back to WordPress Media Library
- Works seamlessly with WordPress media picker

### 2. Real-Time Sync ✅
- Connect your Canva account via OAuth2
- Automatic hourly sync of designs from Canva to WordPress
- Option to auto-update when a design changes in Canva
- Manual sync option available in Media Library toolbar
- Background job processing for scheduled syncs

### 3. Template Picker ✅
- Browse Canva templates directly from within WordPress
- Filter templates by category:
  - Social Posts (Instagram, Facebook, etc.)
  - Blog Graphics (Headers, featured images)
  - Infographics (Process flows, data visualization)
- Instant import and customization
- Modal interface with beautiful template grid

### 4. Inline Editor ✅
- Launch Canva editor in a modal window without leaving WordPress dashboard
- Quick access to resize and text editing tools
- Changes sync back to WordPress automatically
- Perfect for quick tweaks and adjustments

### 5. Brand Kit Integration ✅
- Pull your Canva Brand Kit (colors, fonts, logos) via API
- Brand assets cached in WordPress for fast access
- API endpoints for retrieving brand kit data
- Ready for future auto-application features

### 6. Multi-App Hub ✅
- **Design Hub** - Unified interface for multiple design platforms:
  - **Canva** - Full integration with OAuth
  - **Crello (VistaCreate)** - Quick launch support
  - **Adobe Express** - Quick launch support
  - **Figma** - Quick launch support
- Toggle each platform on/off from settings
- Consistent interface across all design apps
- Recent designs gallery showing all imported designs

### 7. Scheduled Design Publishing 🔄
- Sync manager with scheduled task support
- Hourly automatic sync when enabled
- Framework ready for future post scheduler integration
- Design metadata tracking for version control

### 8. Collaborative Workflow 📝
- Framework in place for future enhancements
- Design metadata stored for tracking
- Ready for commenting and approval workflow additions

### 9. Auto-Optimize on Import ✅
- Automatic WebP conversion when importing from Canva
- Responsive image size generation
- WordPress standard image sizes support
- Performance-optimized by default

### 10. Quick Actions ✅
- **"Edit in Canva"** button on existing imported designs
- Opens design directly in Canva editor
- Metadata tracking links designs to Canva originals
- Version control through WordPress attachment system

## Installation

1. This is a WordPress module that extends the TIMU Core system
2. Requires `core-support-thisismyurl` to be installed and active
3. Automatically loaded when Core is present

## Configuration

### Canva API Credentials

1. Go to **WordPress Admin** → **TIMU Core** → **Media Support** tab
2. Locate the **Canva Integration** section
3. Enter your Canva API credentials:
   - **Client ID**: Your Canva API Client ID
   - **Client Secret**: Your Canva API Client Secret
4. Click **Connect to Canva** to authorize
5. You'll be redirected to Canva to grant permissions
6. Once connected, you can start creating and importing designs

### Getting Canva API Credentials

1. Visit [Canva Developers](https://www.canva.com/developers/)
2. Create a new app or use an existing one
3. Set the redirect URI to: `https://yoursite.com/wp-admin/admin.php?page=timu-canva-oauth-callback`
4. Copy your Client ID and Client Secret
5. Paste them into the WordPress settings

### Settings Options

**Canva Integration:**
- **Client ID**: Canva API Client ID
- **Client Secret**: Canva API Client Secret (stored securely)
- **Auto-Sync**: Enable automatic hourly sync from Canva
- **Auto-Update**: Automatically update designs when they change in Canva

**Design Hub:**
- **Enable Canva**: Show Canva in Design Hub (default: on)
- **Enable Crello**: Show Crello/VistaCreate in Design Hub
- **Enable Adobe Express**: Show Adobe Express in Design Hub
- **Enable Figma**: Show Figma in Design Hub

## Usage

### Creating Designs

**From Media Library:**
1. Go to **Media** → **Library**
2. Click the **"Create in Canva"** button in the toolbar
3. Design your content in Canva
4. Close Canva when done - you'll be prompted to import

**From Design Hub:**
1. Go to **Media** → **Design Hub**
2. Click **Launch** on any enabled design platform
3. Create your design
4. Import back to WordPress when ready

### Browsing Templates

1. Click **"Browse Templates"** in Media Library
2. Filter by category (Social Posts, Blog Graphics, Infographics)
3. Click any template to open it in Canva
4. Customize and import to WordPress

### Editing Existing Designs

1. Go to **Media** → **Library**
2. Find a design imported from Canva (shows "Edit in Canva" in details)
3. Click **"Edit in Canva"** to open the design
4. Make changes in Canva
5. Import the updated version

### Syncing Designs

**Manual Sync:**
1. Go to **Media** → **Library**
2. Click **"Sync from Canva"** in the toolbar
3. Recent designs will be imported

**Automatic Sync:**
1. Enable **Auto-Sync** in settings
2. Designs sync automatically every hour
3. Enable **Auto-Update** to refresh existing designs

## Technical Details

### Architecture

**Classes:**
- `Canva_Integration` - Main integration handler
- `OAuth_Handler` - OAuth2 authentication flow
- `Sync_Manager` - Real-time sync and scheduling
- `Design_Hub` - Multi-platform design hub

**Hooks:**
- `timu_canva_sync_designs` - Scheduled sync action
- `attachment_fields_to_edit` - Add Canva edit buttons

**AJAX Actions:**
- `timu_canva_create` - Create new design
- `timu_canva_import` - Import design from Canva
- `timu_canva_sync` - Sync designs
- `timu_canva_get_templates` - Get template list
- `timu_canva_get_brand_kit` - Get brand kit data
- `timu_canva_edit` - Edit existing design
- `timu_canva_disconnect` - Disconnect account

### Database Storage

**Options:**
- `timu_canva_client_id` - API client ID
- `timu_canva_client_secret` - API client secret
- `timu_canva_access_token` - OAuth access token
- `timu_canva_refresh_token` - OAuth refresh token
- `timu_canva_token_expires` - Token expiration timestamp
- `timu_canva_auto_sync` - Auto-sync enabled flag
- `timu_canva_auto_update` - Auto-update enabled flag
- `timu_canva_brand_kit` - Cached brand kit data
- `timu_canva_last_sync` - Last sync timestamp
- `timu_canva_last_sync_results` - Last sync results
- `timu_design_hub_*_enabled` - Design Hub platform toggles

**Post Meta:**
- `_canva_design_id` - Links attachment to Canva design
- `_canva_imported` - Import timestamp

### API Integration

**Canva API Endpoints Used:**
- `/designs` - Create and list designs
- `/designs/{id}` - Get design info
- `/designs/{id}/export` - Export design as image
- `/brand-kits` - Get brand kit data
- `/oauth/authorize` - OAuth authorization
- `/oauth/token` - OAuth token exchange

**Scopes Required:**
- `design:read` - Read design data
- `design:write` - Create and modify designs
- `design:content:read` - Read design content
- `design:content:write` - Modify design content
- `folder:read` - Read folder data
- `brand:read` - Read brand kit data

## File Structure

```
/plugin-media-support-thisismyurl/
├── module.php                                    # Main module file
├── includes/
│   ├── canva/
│   │   ├── class-canva-integration.php          # Main Canva integration
│   │   ├── class-oauth-handler.php              # OAuth2 handler
│   │   └── class-sync-manager.php               # Sync manager
│   └── design-hub/
│       └── class-design-hub.php                 # Multi-app design hub
├── assets/
│   ├── js/
│   │   ├── canva-integration.js                 # Canva frontend JS
│   │   └── design-hub.js                        # Design Hub frontend JS
│   ├── css/
│   │   ├── canva-integration.css                # Canva styles
│   │   └── design-hub.css                       # Design Hub styles
│   └── images/
│       └── (template thumbnails)                # Template preview images
└── README.md                                     # This file
```

## Security

- OAuth2 authentication with secure token storage
- WordPress nonce verification on all AJAX requests
- Capability checks (`upload_files`, `manage_options`)
- Sanitization and validation of all inputs
- Secure API credential storage
- No credentials exposed in frontend JavaScript

## Performance

- Lazy loading of design data
- Cached brand kit data
- Background sync processing
- Optimized image imports (WebP, responsive sizes)
- Minimal database queries

## Browser Support

- Modern browsers with ES6 support
- WordPress 6.4.0+
- PHP 8.1.29+

## Future Enhancements

- **Collaborative workflow** with commenting and approvals
- **Post scheduler integration** for scheduled publishing
- **Advanced brand kit** auto-application
- **Bulk operations** for multiple designs
- **Design versioning** system
- **Analytics** for design performance
- **Additional platforms** (Photopea, Pixlr, etc.)

## Support

For issues, questions, or feature requests, please contact the TIMU Core support team.

## License

This module is part of the TIMU Core system and follows the same license terms.
