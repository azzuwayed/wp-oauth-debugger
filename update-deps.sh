#!/bin/bash

# Exit on error
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Print with color
print_status() {
    echo -e "${GREEN}==>${NC} $1"
}

print_error() {
    echo -e "${RED}Error:${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}Warning:${NC} $1"
}

# Function to backup composer files
backup_composer_files() {
    print_status "Backing up composer files..."
    cp composer.json composer.json.bak
    cp composer.lock composer.lock.bak
}

# Function to restore composer files
restore_composer_files() {
    print_status "Restoring composer files from backup..."
    mv composer.json.bak composer.json
    mv composer.lock.bak composer.lock
}

# Function to update a specific package
update_package() {
    local package=$1
    local current_version=$2
    local new_version=$3

    print_status "Updating $package from $current_version to $new_version..."

    # Backup current state
    backup_composer_files

    # Try to update
    if composer update "$package" --with-dependencies; then
        print_status "Successfully updated $package"

        # Run tests if they exist
        if [ -f "vendor/bin/phpunit" ]; then
            print_status "Running tests..."
            vendor/bin/phpunit
        fi

        # Build the plugin to verify everything works
        print_status "Building plugin to verify update..."
        ./build.sh

        print_status "Update of $package completed successfully!"
    else
        print_error "Failed to update $package"
        print_status "Restoring previous state..."
        restore_composer_files
        composer install
        exit 1
    fi
}

# Main process
main() {
    print_status "Checking for outdated packages..."
    composer outdated

    print_status "Updating all dependencies..."
    if composer update --with-all-dependencies; then
        print_status "Successfully updated dependencies"

        # Build the plugin to verify everything works
        print_status "Building plugin to verify update..."
        ./build.sh

        print_status "Update completed successfully!"
        print_status "Don't forget to commit the updated composer.json and composer.lock files"
    else
        print_error "Failed to update dependencies"
        exit 1
    fi
}

# Run the update
main
