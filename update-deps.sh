#!/bin/bash

# Exit on error, undefined variables, and pipe failures
set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script version
VERSION="1.1.0"

# Default values
DRY_RUN=false
VERBOSE=false
SKIP_TESTS=false
SKIP_BUILD=false
BACKUP=true
PACKAGE=""

# Print with color and timestamp
print_status() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')] ==>${NC} $1"
}

print_error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] Error:${NC} $1" >&2
}

print_warning() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] Warning:${NC} $1"
}

print_info() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')] Info:${NC} $1"
}

# Show help message
show_help() {
    echo "WP OAuth Debugger Dependency Update Script v${VERSION}"
    echo
    echo "Usage: $0 [options] [package]"
    echo
    echo "Options:"
    echo "  -h, --help           Show this help message"
    echo "  -v, --version        Show version information"
    echo "  -d, --dry-run        Show what would be updated without making changes"
    echo "  -V, --verbose        Show verbose output"
    echo "  --skip-tests         Skip running tests after update"
    echo "  --skip-build         Skip building plugin after update"
    echo "  --no-backup          Skip creating backup files"
    echo "  -p, --package PKG    Update specific package (e.g., 'phpunit/phpunit')"
    echo
    echo "Examples:"
    echo "  $0                    # Update all dependencies"
    echo "  $0 -d                 # Show what would be updated"
    echo "  $0 -p phpunit/phpunit # Update specific package"
    echo "  $0 --skip-tests       # Update without running tests"
}

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        -v|--version)
            echo "Version ${VERSION}"
            exit 0
            ;;
        -d|--dry-run)
            DRY_RUN=true
            shift
            ;;
        -V|--verbose)
            VERBOSE=true
            shift
            ;;
        --skip-tests)
            SKIP_TESTS=true
            shift
            ;;
        --skip-build)
            SKIP_BUILD=true
            shift
            ;;
        --no-backup)
            BACKUP=false
            shift
            ;;
        -p|--package)
            PACKAGE="$2"
            shift 2
            ;;
        *)
            print_error "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install it first."
    exit 1
fi

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    print_error "composer.json not found. Please run this script from the plugin root directory."
    exit 1
fi

# Function to backup composer files
backup_composer_files() {
    if [ "$BACKUP" = true ]; then
        print_status "Backing up composer files..."
        local timestamp=$(date '+%Y%m%d_%H%M%S')
        cp composer.json "composer.json.bak.${timestamp}"
        cp composer.lock "composer.lock.bak.${timestamp}"
        print_info "Backup files created: composer.json.bak.${timestamp}, composer.lock.bak.${timestamp}"
    fi
}

# Function to restore composer files
restore_composer_files() {
    if [ "$BACKUP" = true ]; then
        print_status "Restoring composer files from backup..."
        local latest_json=$(ls -t composer.json.bak.* | head -n1)
        local latest_lock=$(ls -t composer.lock.bak.* | head -n1)

        if [ -f "$latest_json" ] && [ -f "$latest_lock" ]; then
            mv "$latest_json" composer.json
            mv "$latest_lock" composer.lock
            print_info "Restored from: $latest_json, $latest_lock"
        else
            print_error "No backup files found!"
            exit 1
        fi
    fi
}

# Function to check for outdated packages
check_outdated() {
    print_status "Checking for outdated packages..."
    if [ "$VERBOSE" = true ]; then
        composer outdated --direct
    else
        composer outdated --direct --format=json | jq -r '.installed[] | select(.latest != .version) | "\(.name): \(.version) -> \(.latest)"'
    fi
}

# Function to run tests
run_tests() {
    if [ "$SKIP_TESTS" = false ]; then
        if [ -f "vendor/bin/phpunit" ]; then
            print_status "Running tests..."
            if ! vendor/bin/phpunit; then
                print_error "Tests failed after update!"
                return 1
            fi
        else
            print_warning "PHPUnit not found, skipping tests"
        fi
    fi
    return 0
}

# Function to build plugin
build_plugin() {
    if [ "$SKIP_BUILD" = false ]; then
        if [ -f "build.sh" ]; then
            print_status "Building plugin to verify update..."
            if ! ./build.sh; then
                print_error "Build failed after update!"
                return 1
            fi
        else
            print_warning "build.sh not found, skipping build"
        fi
    fi
    return 0
}

# Function to update a specific package
update_package() {
    local package=$1
    print_status "Updating $package..."

    if [ "$DRY_RUN" = true ]; then
        composer update "$package" --dry-run
        return 0
    fi

    # Backup current state
    backup_composer_files

    # Try to update
    if composer update "$package" --with-dependencies; then
        print_status "Successfully updated $package"

        if ! run_tests; then
            print_error "Tests failed after updating $package"
            restore_composer_files
            composer install
            exit 1
        fi

        if ! build_plugin; then
            print_error "Build failed after updating $package"
            restore_composer_files
            composer install
            exit 1
        fi

        print_status "Update of $package completed successfully!"
    else
        print_error "Failed to update $package"
        restore_composer_files
        composer install
        exit 1
    fi
}

# Function to update all dependencies
update_all() {
    print_status "Updating all dependencies..."

    if [ "$DRY_RUN" = true ]; then
        composer update --dry-run
        return 0
    fi

    # Backup current state
    backup_composer_files

    # Try to update
    if composer update --with-all-dependencies; then
        print_status "Successfully updated dependencies"

        if ! run_tests; then
            print_error "Tests failed after update"
            restore_composer_files
            composer install
            exit 1
        fi

        if ! build_plugin; then
            print_error "Build failed after update"
            restore_composer_files
            composer install
            exit 1
        fi

        print_status "Update completed successfully!"
        print_status "Don't forget to commit the updated composer.json and composer.lock files"
    else
        print_error "Failed to update dependencies"
        restore_composer_files
        composer install
        exit 1
    fi
}

# Main process
main() {
    # Check for outdated packages first
    check_outdated

    # Update based on whether a specific package was specified
    if [ -n "$PACKAGE" ]; then
        update_package "$PACKAGE"
    else
        update_all
    fi
}

# Run the update
main
