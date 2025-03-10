# Tactical Game Organizer

WordPress plugin for organizing airsoft events with participant registration and Telegram integration.

## Description

Tactical Game Organizer is a WordPress plugin designed to help airsoft communities manage their game events. It provides a comprehensive solution for event organization, field management, and player registration with role-based participation.

### Features

-   Custom post types for Events and Fields
-   Event management with:
    -   Date and time scheduling
    -   Maximum participants limit
    -   Field location assignment
    -   Event details and rules
    -   Role restrictions per event
    -   Participant progress tracking
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
    - Configure allowed roles
4. Publish the event

### Managing Fields

1. Go to "Fields" → "Add New"
2. Add field information:
    - Name
    - Description
    - Location
    - Photos
3. Publish the field

### Player Registration

1. Players must be logged in and have the 'Player' role
2. On the event page, players can:
    - View current participants
    - See available roles
    - Register with their preferred role
    - View event capacity
3. The system will:
    - Remember their last used role
    - Default to 'Assault' if preferred role is unavailable
    - Show registration status
    - Prevent registration if event is full

## Development

### Project Structure

```
tactical-game-organizer/
├── src/
│   ├── PostTypes/
│   │   ├── Event.php
│   │   └── Field.php
│   ├── Users/
│   │   ├── EventRegistration.php
│   │   ├── UserFields.php
│   │   └── Roles.php
│   ├── Roles/
│   │   └── PlayerRoles.php
│   └── Meta/
│       └── EventMeta.php
├── assets/
│   ├── css/
│   │   └── event-registration.css
│   └── js/
│       └── event-registration.js
├── tests/
│   ├── Unit/
│   │   └── PostTypes/
│   │       └── EventTest.php
│   └── bootstrap.php
├── languages/
├── vendor/
├── composer.json
├── composer.lock
├── phpunit.xml
├── README.md
└── tactical_game_organizer.php
```

### Coding Standards

This project follows WordPress Coding Standards and PSR-12. To check your code:

```bash
# Run PHP CodeSniffer
composer run-script phpcs

# Fix coding standards automatically
composer run-script phpcbf
```

### Testing

The plugin uses PHPUnit for unit testing and WP_Mock for mocking WordPress functions.

```bash
# Run all tests
composer run-script test

# Run tests with coverage report
composer run-script test:coverage
```

#### Writing Tests

1. Create a new test class in `tests/Unit` directory
2. Extend `PHPUnit\Framework\TestCase`
3. Use WP_Mock for WordPress functions
4. Follow the naming convention: `*Test.php`

Example test:

```php
class EventTest extends TestCase {
    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }

    public function testSomething(): void {
        // Arrange
        WP_Mock::userFunction('wp_function', [
            'times' => 1,
            'return' => 'expected value',
        ]);

        // Act
        $result = do_something();

        // Assert
        $this->assertEquals('expected value', $result);
    }
}
```

## Contributing

1. Fork the repository
2. Create your feature branch
3. Write tests for your changes
4. Ensure all tests pass
5. Commit your changes
6. Push to the branch
7. Create a Pull Request

## License

GPL-2.0-or-later - see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) for details.
