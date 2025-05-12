# WordPress OAuth Debugger

A comprehensive debugging and monitoring tool for OAuth implementations in WordPress. This plugin helps developers and administrators track, debug, and secure OAuth authentication flows in their WordPress applications.

## Features

- **Comprehensive Logging**: Detailed logging of OAuth requests, responses, and errors
- **Security Monitoring**:
  - Token management and monitoring
  - Security status checks
  - PKCE support verification
  - CORS configuration validation
  - Rate limiting monitoring
  - Security headers verification
- **Debug Tools**:
  - Real-time request logging
  - Token inspection and management
  - Server information gathering
  - Security status reporting
- **Admin Interface**: User-friendly dashboard for monitoring and managing OAuth operations
- **API Endpoints**: REST API endpoints for programmatic access to debug information
- **Security Features**:
  - Sensitive data masking in logs
  - Secure log storage
  - Access control and capability checks
  - Rate limiting protection

## Requirements

- WordPress 6.5 or higher
- PHP 8.3 or higher
- Composer for dependency management

## Installation

1. Download the plugin files
2. Upload the plugin directory to `/wp-content/plugins/`
3. Run `composer install` in the plugin directory
4. Activate the plugin through the WordPress admin interface

## Configuration

The plugin can be configured using constants in your `wp-config.php`:

```php
// Enable debugging
define('OAUTH_DEBUG', true);

// Set minimum log level (debug, info, warning, error)
define('OAUTH_DEBUG_LOG_LEVEL', 'info');

// Set log retention period in days
define('OAUTH_DEBUG_LOG_RETENTION', 7);
```

## Usage

### Logging

The plugin automatically logs OAuth-related activities when debugging is enabled. Logs are stored in `wp-content/oauth-debug-logs/` and include:

- Request/response data
- Token operations
- Error messages
- Security events
- User context

### Admin Interface

Access the debugger interface through:

- WordPress Admin → OAuth Debugger
- View logs, tokens, and security status
- Manage active tokens
- Monitor OAuth server health

### API Endpoints

The plugin provides REST API endpoints for programmatic access:

- `GET /wp-json/oauth-debugger/v1/logs` - Retrieve recent logs
- `GET /wp-json/oauth-debugger/v1/tokens` - List active tokens
- `DELETE /wp-json/oauth-debugger/v1/tokens/{token_id}` - Delete a specific token

## Security

- Logs are stored in a protected directory
- Sensitive data (tokens, secrets) is automatically masked
- Access to debug information is restricted to users with appropriate capabilities
- Rate limiting is implemented to prevent abuse
- Security headers are verified and reported

## Development

### Project Structure

- `includes/` - Plugin source code
- `assets/` - Static assets (JS, CSS, images)
- `languages/` - Translations
- `scripts/` - Development and build scripts
- `tests/` - Test suites for the plugin
- `docs/` - Developer documentation
- `logs/` - Build and debug logs
- `backups/` - Backup files

### Scripts

The plugin includes several utility scripts for development and deployment:

- `composer test` - Run unit and integration tests
- `composer build` - Build the plugin for deployment
- `composer update-deps` - Update dependencies
- `composer release` - Create a release package

### Running Tests

To set up the WordPress test environment and run all plugin tests:

```bash
composer test
```

This will:

- Automatically set up the WordPress test suite if needed
- Suppress deprecation, notice, and warning messages for cleaner output
- Run all unit and integration tests

To reset the test environment:

```bash
rm -rf /tmp/wordpress-tests-lib
composer setup-tests
```

### Building the Plugin

To build a deployment-ready version of the plugin:

```bash
composer build
```

The build process:

- Installs production-only dependencies
- Optimizes the autoloader
- Creates a zip file ready for installation
- Generates checksums and build info

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL v2 or later.

## Author

Abdullah Alzuwayed

## Support

For support, please open an issue on the GitHub repository.
