#!/usr/bin/env bash
set -euo pipefail

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}==>${NC} $1"
}
print_error() {
    echo -e "${RED}Error:${NC} $1" >&2
}
print_warning() {
    echo -e "${YELLOW}Warning:${NC} $1"
}

# Check for composer
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install it first."
    exit 1
fi

# Ensure test environment is set up
if [ ! -d "/tmp/wordpress-tests-lib" ]; then
    print_status "Setting up WordPress test environment..."
    composer run setup-tests
else
    print_status "WordPress test environment already set up."
fi

# Run PHPUnit with error suppression for deprecations
if [ -f "vendor/bin/phpunit" ]; then
    print_status "Running PHPUnit tests (suppressing deprecations, notices, warnings)..."
    XDEBUG_MODE=off vendor/bin/phpunit
else
    print_error "PHPUnit not found. Run 'composer install' first."
    exit 1
fi
