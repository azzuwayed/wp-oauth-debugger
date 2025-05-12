#!/bin/bash

# Exit on error
set -e

# Configuration
PLUGIN_NAME="wp-oauth-debugger"
VERSION=$(grep "Version:" wp-oauth-debugger.php | cut -d' ' -f2 | tr -d '\r')
BUILD_DIR="build"
RELEASE_DIR="$BUILD_DIR/$PLUGIN_NAME"
ZIP_NAME="$PLUGIN_NAME-v$VERSION.zip"

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

# Clean previous build
clean_build() {
    print_status "Cleaning previous build..."

    # Force remove build directory and any zip files if they exist
    if [ -d "$BUILD_DIR" ]; then
        print_status "Removing existing build directory..."
        rm -rf "$BUILD_DIR"
    fi

    # Also clean any stray zip files in the build directory
    if [ -f "$BUILD_DIR"/*.zip ]; then
        print_status "Removing existing zip files..."
        rm -f "$BUILD_DIR"/*.zip
    fi

    # Verify complete cleanup
    if [ -d "$BUILD_DIR" ] || [ -f "$BUILD_DIR"/*.zip ]; then
        print_error "Failed to completely clean build directory"
        exit 1
    fi

    # Create fresh build directory
    print_status "Creating fresh build directory..."
    mkdir -p "$RELEASE_DIR"

    # Verify directory was created
    if [ ! -d "$RELEASE_DIR" ]; then
        print_error "Failed to create build directory"
        exit 1
    fi
}

# Install production dependencies
install_dependencies() {
    print_status "Installing production dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction --quiet
}

# Fix case sensitivity issues
fix_case_sensitivity() {
    print_status "Fixing case sensitivity issues..."

    # Create temporary directory for case-sensitive operations
    TEMP_DIR=$(mktemp -d)

    # Copy includes directory to temp
    cp -R "$RELEASE_DIR/includes" "$TEMP_DIR/"

    # Remove original includes directory
    rm -rf "$RELEASE_DIR/includes"

    # Create new includes directory with correct case
    mkdir -p "$RELEASE_DIR/includes"

    # Move directories with correct case
    # Core, Admin, and Debug use PascalCase to match namespace
    mv "$TEMP_DIR/includes/core" "$RELEASE_DIR/includes/Core"
    mv "$TEMP_DIR/includes/debug" "$RELEASE_DIR/includes/Debug"
    mv "$TEMP_DIR/includes/admin" "$RELEASE_DIR/includes/Admin"
    # Other directories use lowercase
    mv "$TEMP_DIR/includes/Security" "$RELEASE_DIR/includes/security"
    mv "$TEMP_DIR/includes/templates" "$RELEASE_DIR/includes/templates"

    # Clean up temp directory
    rm -rf "$TEMP_DIR"

    # Verify the case-sensitive structure
    if [ ! -d "$RELEASE_DIR/includes/Core" ]; then
        print_error "Failed to fix case sensitivity for Core directory"
        exit 1
    fi
    if [ ! -d "$RELEASE_DIR/includes/security" ]; then
        print_error "Failed to fix case sensitivity for security directory"
        exit 1
    fi
}

# Copy plugin files
copy_files() {
    print_status "Copying plugin files..."

    # List of files and directories to include
    FILES=(
        "includes"
        "vendor"
        "languages"
        "assets"
        "wp-oauth-debugger.php"
        "uninstall.php"
        "composer.json"
        "composer.lock"
        "README.md"
    )

    # Copy each file/directory
    for item in "${FILES[@]}"; do
        if [ -e "$item" ]; then
            print_status "Copying $item..."
            cp -R "$item" "$RELEASE_DIR/"
            # Verify the copy
            if [ ! -e "$RELEASE_DIR/$item" ]; then
                print_error "Failed to copy $item"
                exit 1
            fi
        else
            print_error "Required file/directory not found: $item"
            exit 1
        fi
    done

    # Fix case sensitivity after copying
    fix_case_sensitivity
}

# Create zip file
create_zip() {
    print_status "Creating zip file..."
    cd "$BUILD_DIR" || exit 1
    zip -r "$ZIP_NAME" "$PLUGIN_NAME" -x "*.DS_Store" "*.git*" "*.zip" > /dev/null
    cd .. || exit 1
}

# Verify build
verify_build() {
    print_status "Verifying build..."

    # Check essential files
    ESSENTIAL_FILES=(
        "$RELEASE_DIR/wp-oauth-debugger.php"
        "$RELEASE_DIR/includes/Core/Activator.php"
        "$RELEASE_DIR/vendor/autoload.php"
        "$RELEASE_DIR/composer.json"
    )

    for file in "${ESSENTIAL_FILES[@]}"; do
        if [ ! -f "$file" ]; then
            print_error "Missing essential file: $file"
            exit 1
        fi
    done

    # Verify directory structure
    print_status "Verifying directory structure..."
    if [ ! -d "$RELEASE_DIR/includes/Core" ]; then
        print_error "Missing includes/Core directory"
        exit 1
    fi

    # Check zip file
    if [ ! -f "$BUILD_DIR/$ZIP_NAME" ]; then
        print_error "Zip file was not created"
        exit 1
    fi

    # Get zip file size
    ZIP_SIZE=$(du -h "$BUILD_DIR/$ZIP_NAME" | cut -f1)

    # Count total files in zip
    TOTAL_FILES=$(unzip -l "$BUILD_DIR/$ZIP_NAME" | tail -n 1 | awk '{print $2}')

    # Verify zip contents
    print_status "Verifying zip contents..."
    TEMP_DIR=$(mktemp -d)
    unzip -q "$BUILD_DIR/$ZIP_NAME" -d "$TEMP_DIR"

    if [ ! -f "$TEMP_DIR/$PLUGIN_NAME/includes/Core/Activator.php" ]; then
        print_error "Activator.php missing from zip file"
        rm -rf "$TEMP_DIR"
        exit 1
    fi

    rm -rf "$TEMP_DIR"

    # Print build summary
    print_status "Build Summary:"
    echo "  • Version: $VERSION"
    echo "  • Package: $ZIP_NAME"
    echo "  • Size: $ZIP_SIZE"
    echo "  • Total Files: $TOTAL_FILES"
    echo "  • Essential Files: ✓"
    echo "  • Directory Structure: ✓"
    echo "  • Zip Contents: ✓"
}

# Main build process
main() {
    print_status "Starting build process for $PLUGIN_NAME v$VERSION"

    # Verify we're in the plugin directory
    if [ ! -f "wp-oauth-debugger.php" ]; then
        print_error "Must run build script from plugin root directory"
        exit 1
    fi

    clean_build
    install_dependencies
    copy_files
    create_zip
    verify_build

    print_status "Build completed successfully!"
    print_status "Release package: $BUILD_DIR/$ZIP_NAME"

    # Print instructions
    echo
    print_status "Installation Instructions:"
    echo "1. Upload $BUILD_DIR/$ZIP_NAME to your WordPress site"
    echo "2. Install through WordPress admin panel"
    echo "3. After installation, verify these files exist:"
    echo "   • wp-content/plugins/wp-oauth-debugger/includes/Core/Activator.php"
    echo "   • wp-content/plugins/wp-oauth-debugger/vendor/autoload.php"
}

# Run the build
main
