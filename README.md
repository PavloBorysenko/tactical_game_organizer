# Tactical Game Organizer

WordPress plugin for organizing airsoft events with participant registration and Telegram integration.

## Description

Tactical Game Organizer is a WordPress plugin designed to help airsoft communities manage their game events. It provides a comprehensive solution for event organization, field management, and player registration.

### Features

-   Custom post types for Events and Fields
-   Event management with:
    -   Date and time scheduling
    -   Maximum participants limit
    -   Field location assignment
    -   Event details and rules
-   Field management with:
    -   Location information
    -   Field description
    -   Photo gallery
    -   Geolocation
-   Player registration system:
    -   Custom Player role
    -   Registration/unregistration for events
    -   Player profile with callsign, role, and team
-   Telegram integration:
    -   Event registration via Telegram bot
    -   Event notifications
    -   Player management

## Requirements

-   WordPress 5.8 or higher
-   PHP 7.4 or higher
-   Composer (for development)

## Installation

1. Download the plugin
2. Upload to your WordPress plugins directory
3. Activate the plugin through WordPress admin interface

### Development Installation

```bash
# Clone the repository
git clone [repository-url]

# Install dependencies
composer install
```

## Usage

### Creating an Event

1. Go to WordPress admin panel
2. Navigate to "Events" → "Add New"
3. Fill in the event details:
    - Title
    - Description
    - Date and time
    - Maximum participants
    - Select game field
4. Publish the event

### Managing Fields

1. Go to "Fields" → "Add New"
2. Add field information:
    - Name
    - Description
    - Location
    - Photos
3. Publish the field

## Development

### Project Structure

```
tactical-game-organizer/
├── src/
│   ├── PostTypes/
│   │   ├── Event.php
│   │   └── Field.php
│   └── Meta/
│       └── EventMeta.php
├── languages/
├── vendor/
├── composer.json
├── composer.lock
├── README.md
└── tactical_game_organizer.php
```

### Coding Standards

This project follows WordPress Coding Standards and PSR-12. To check your code:

```bash
composer run-script phpcs
```

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

GPL-2.0-or-later - see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) for details.
