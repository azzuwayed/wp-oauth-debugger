#!/bin/bash

# Exit on error
set -e

# Enable error tracing
trap 'handle_error $? $LINENO' ERR

# Configuration
PLUGIN_NAME="wp-oauth-debugger"
BUILD_DIR="build"
BACKUP_DIR="${BUILD_DIR}/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
LOG_FILE="${BUILD_DIR}/build_${TIMESTAMP}.log"

# Create build and log directories first
mkdir -p "$BUILD_DIR"
mkdir -p "$BACKUP_DIR"
mkdir -p "$(dirname "$LOG_FILE")"

# Parse command line arguments
VERBOSE=0
DRY_RUN=0
PARALLEL=0
while [[ $# -gt 0 ]]; do
    case $1 in
        -v|--verbose)
            VERBOSE=1
            shift
            ;;
        -d|--dry-run)
            DRY_RUN=1
            shift
            ;;
        -p|--parallel)
            PARALLEL=1
            shift
            ;;
        *)
            print_error "Unknown option: $1"
            print_usage
            exit 1
            ;;
    esac
done

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Print with color (without timestamp)
print_status() {
    if [ -d "$(dirname "$LOG_FILE")" ]; then
        echo -e "${GREEN}==>${NC} $1" | tee -a "$LOG_FILE"
    else
        echo -e "${GREEN}==>${NC} $1"
    fi
}

print_error() {
    if [ -d "$(dirname "$LOG_FILE")" ]; then
        echo -e "${RED}Error:${NC} $1" | tee -a "$LOG_FILE" >&2
    else
        echo -e "${RED}Error:${NC} $1" >&2
    fi
}

print_warning() {
    if [ -d "$(dirname "$LOG_FILE")" ]; then
        echo -e "${YELLOW}Warning:${NC} $1" | tee -a "$LOG_FILE"
    else
        echo -e "${YELLOW}Warning:${NC} $1"
    fi
}

print_debug() {
    if [ "$VERBOSE" -eq 1 ]; then
        if [ -d "$(dirname "$LOG_FILE")" ]; then
            echo -e "${BLUE}Debug:${NC} $1" | tee -a "$LOG_FILE"
        else
            echo -e "${BLUE}Debug:${NC} $1"
        fi
    fi
}

print_usage() {
    echo "Usage: $0 [options]"
    echo "Options:"
    echo "  -v, --verbose   Enable verbose output"
    echo "  -d, --dry-run   Show what would happen without making changes"
    echo "  -p, --parallel  Use parallel processing where possible"
    echo "  -h, --help      Show this help message"
}

# Error handler
handle_error() {
    local exit_code=$1
    local line_number=$2
    print_error "Error occurred in script at line $line_number (exit code: $exit_code)"
    cleanup
    exit "$exit_code"
}

# Cleanup function
cleanup() {
    print_debug "Running cleanup..."
    if [ -d "$TEMP_DIR" ]; then
        rm -rf "$TEMP_DIR"
    fi
}

# Register cleanup trap
trap cleanup EXIT

# Extract version with better error handling
if ! VERSION=$(grep -E "^[[:space:]]*\*[[:space:]]*Version:" wp-oauth-debugger.php | sed -E 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*([0-9]+\.[0-9]+\.[0-9]+).*/\1/'); then
    print_error "Failed to extract version number"
    exit 1
fi

# Validate version format
if ! echo "$VERSION" | grep -E '^[0-9]+\.[0-9]+\.[0-9]+$' >/dev/null 2>&1; then
    print_error "Invalid version format: $VERSION. Expected format: x.y.z"
    exit 1
fi

RELEASE_DIR="$BUILD_DIR/$PLUGIN_NAME"
ZIP_NAME="$PLUGIN_NAME-v$VERSION.zip"

# Backup previous build
backup_previous_build() {
    if [ -f "$BUILD_DIR/$ZIP_NAME" ]; then
        print_status "Backing up previous build..."
        mkdir -p "$BACKUP_DIR"
        cp "$BUILD_DIR/$ZIP_NAME" "$BACKUP_DIR/${ZIP_NAME%.zip}_${TIMESTAMP}.zip"
        print_debug "Previous build backed up to $BACKUP_DIR/${ZIP_NAME%.zip}_${TIMESTAMP}.zip"
    fi
}

# Generate checksums
generate_checksums() {
    print_status "Generating checksums..."
    cd "$BUILD_DIR" || exit 1
    {
        echo "MD5:"
        md5sum "$ZIP_NAME"
        echo -e "\nSHA1:"
        sha1sum "$ZIP_NAME"
        echo -e "\nSHA256:"
        sha256sum "$ZIP_NAME"
    } > "${ZIP_NAME%.zip}_checksums.txt"
    cd .. || exit 1
    print_debug "Checksums saved to ${ZIP_NAME%.zip}_checksums.txt"
}

# Clean previous build
clean_build() {
    print_status "Cleaning previous build..."

    if [ "$DRY_RUN" -eq 1 ]; then
        print_debug "Would remove: $BUILD_DIR"
        return
    fi

    # Backup previous build before cleaning
    if [ -f "$BUILD_DIR/$ZIP_NAME" ]; then
        print_status "Backing up previous build..."
        cp "$BUILD_DIR/$ZIP_NAME" "$BACKUP_DIR/${ZIP_NAME%.zip}_${TIMESTAMP}.zip"
        print_debug "Previous build backed up to $BACKUP_DIR/${ZIP_NAME%.zip}_${TIMESTAMP}.zip"
    fi

    # Force remove build directory and any zip files if they exist
    if [ -d "$BUILD_DIR" ]; then
        print_status "Removing existing build directory..."
        rm -rf "$BUILD_DIR"
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

    if [ "$DRY_RUN" -eq 1 ]; then
        print_debug "Would run: composer install --no-dev --optimize-autoloader"
        return
    fi

    # Check if composer is installed
    if ! command -v composer >/dev/null 2>&1; then
        print_error "Composer is not installed. Please install composer first."
        exit 1
    fi

    # Run composer install with error handling
    if [ "$VERBOSE" -eq 1 ]; then
        composer install --no-dev --optimize-autoloader --no-interaction
    else
        composer install --no-dev --optimize-autoloader --no-interaction --quiet
    fi

    # Verify vendor directory exists
    if [ ! -d "vendor" ]; then
        print_error "Vendor directory was not created"
        exit 1
    fi
}

# Copy plugin files
copy_files() {
    print_status "Copying plugin files..."

    # First, copy the main directories
    local main_dirs=(
        "includes"
        "vendor"
        "languages"
        "assets"
    )

    # Copy main directories
    for dir in "${main_dirs[@]}"; do
        if [ -d "$dir" ]; then
            print_debug "Copying directory $dir..."
            if ! cp -R "$dir" "$RELEASE_DIR/"; then
                print_error "Failed to copy directory $dir"
                exit 1
            fi
            # Verify the directory was copied
            if [ ! -d "$RELEASE_DIR/$dir" ]; then
                print_error "Failed to verify copy of directory $dir"
                exit 1
            fi
        else
            print_warning "Directory not found: $dir"
        fi
    done

    # Then copy individual files
    local main_files=(
        "wp-oauth-debugger.php"
        "uninstall.php"
        "composer.json"
        "composer.lock"
        "README.md"
    )

    # Copy individual files
    for file in "${main_files[@]}"; do
        if [ -f "$file" ]; then
            print_debug "Copying file $file..."
            if ! cp "$file" "$RELEASE_DIR/"; then
                print_error "Failed to copy file $file"
                exit 1
            fi
            # Verify the file was copied
            if [ ! -f "$RELEASE_DIR/$file" ]; then
                print_error "Failed to verify copy of file $file"
                exit 1
            fi
        else
            print_warning "File not found: $file"
        fi
    done

    # Fix case sensitivity
    fix_case_sensitivity
}

# Print environment information
print_env_info() {
    print_status "Environment Information:"
    echo "  • Shell: $SHELL (version: $BASH_VERSION)"
    echo "  • OS: $(uname -s) $(uname -r)"
    echo "  • Current Directory: $(pwd)"
    echo "  • User: $(whoami)"
    echo "  • Date: $(date)"
    echo "  • Build Directory: $BUILD_DIR"
    echo "  • Plugin Version: $VERSION"
}

# Fix case sensitivity issues
fix_case_sensitivity() {
    print_status "Fixing case sensitivity issues..."

    # Define directory mappings using parallel arrays (more compatible approach)
    local src_dirs=("core" "admin" "debug" "security" "templates")
    local target_dirs=("Core" "Admin" "Debug" "Security" "templates")

    # Check and fix each directory
    for i in "${!src_dirs[@]}"; do
        src_dir="${src_dirs[$i]}"
        target_dir="${target_dirs[$i]}"
        src_path="$RELEASE_DIR/includes/$src_dir"
        target_path="$RELEASE_DIR/includes/$target_dir"

        # Skip if directory is already in correct case
        if [ -d "$target_path" ]; then
            print_debug "Directory $target_dir is already in correct case"
            continue
        fi

        # If source exists in wrong case, rename it
        if [ -d "$src_path" ]; then
            print_status "Renaming $src_dir to $target_dir"
            mv "$src_path" "$target_path"
        fi
    done

    # Verify file permissions
    print_status "Setting file permissions..."
    if ! find "$RELEASE_DIR/includes" -type f -exec chmod 644 {} \; || \
       ! find "$RELEASE_DIR/includes" -type d -exec chmod 755 {} \; ; then
        print_error "Failed to set file permissions"
        exit 1
    fi
}

# Create zip file
create_zip() {
    print_status "Creating zip file..."

    # Check if zip command is available
    if ! command -v zip >/dev/null 2>&1; then
        print_error "zip command not found. Please install zip utility."
        exit 1
    fi

    cd "$BUILD_DIR" || exit 1
    if ! zip -r "$ZIP_NAME" "$PLUGIN_NAME" -x "*.DS_Store" "*.git*" "*.zip" >/dev/null 2>&1; then
        print_error "Failed to create zip file"
        cd .. || exit 1
        exit 1
    fi
    cd .. || exit 1
}

# Verify build
verify_build() {
    print_status "Verifying build..."

    # Check essential files
    essential_files=(
        "$RELEASE_DIR/wp-oauth-debugger.php"
        "$RELEASE_DIR/includes/Core/Activator.php"
        "$RELEASE_DIR/vendor/autoload.php"
        "$RELEASE_DIR/composer.json"
    )

    for file in "${essential_files[@]}"; do
        if [ ! -f "$file" ]; then
            print_error "Missing essential file: $file"
            exit 1
        fi
    done

    # Verify directory structure and case sensitivity
    print_status "Verifying directory structure and case sensitivity..."
    required_dirs=(
        "Core:includes/Core"
        "Admin:includes/Admin"
        "Debug:includes/Debug"
        "Security:includes/Security"
        "templates:includes/templates"
    )

    for dir_mapping in "${required_dirs[@]}"; do
        dir_name="${dir_mapping%%:*}"
        dir_path="$RELEASE_DIR/${dir_mapping#*:}"
        if [ ! -d "$dir_path" ]; then
            print_error "Missing or incorrectly cased directory: ${dir_mapping#*:}"
            exit 1
        fi
    done

    # Verify autoloader files
    print_status "Verifying autoloader files..."
    autoloader_files=(
        "$RELEASE_DIR/vendor/composer/autoload_psr4.php"
        "$RELEASE_DIR/vendor/composer/autoload_classmap.php"
        "$RELEASE_DIR/vendor/composer/autoload_real.php"
    )

    for file in "${autoloader_files[@]}"; do
        if [ ! -f "$file" ]; then
            print_error "Missing autoloader file: $file"
            exit 1
        fi
    done

    # Check zip file
    if [ ! -f "$BUILD_DIR/$ZIP_NAME" ]; then
        print_error "Zip file was not created"
        exit 1
    fi

    # Get zip file size
    if ! ZIP_SIZE=$(du -h "$BUILD_DIR/$ZIP_NAME" 2>/dev/null | cut -f1); then
        print_warning "Could not determine zip file size"
        ZIP_SIZE="unknown"
    fi

    # Count total files in zip
    if ! TOTAL_FILES=$(unzip -l "$BUILD_DIR/$ZIP_NAME" 2>/dev/null | tail -n 1 | awk '{print $2}'); then
        print_warning "Could not count files in zip"
        TOTAL_FILES="unknown"
    fi

    # Verify zip contents
    print_status "Verifying zip contents..."
    TEMP_DIR=$(mktemp -d)
    if ! unzip -q "$BUILD_DIR/$ZIP_NAME" -d "$TEMP_DIR"; then
        print_error "Failed to extract zip file for verification"
        rm -rf "$TEMP_DIR"
        exit 1
    fi

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
    local start_time=$(date '+%Y-%m-%d %H:%M:%S')
    print_status "Starting build process for $PLUGIN_NAME v$VERSION at $start_time"

    # Print environment information
    print_env_info

    print_debug "Build options: Verbose=$VERBOSE, Dry Run=$DRY_RUN, Parallel=$PARALLEL"

    # Verify we're in the plugin directory
    if [ ! -f "wp-oauth-debugger.php" ]; then
        print_error "Must run build script from plugin root directory"
        exit 1
    fi

    # Check for required commands
    for cmd in composer zip unzip; do
        if ! command -v "$cmd" >/dev/null 2>&1; then
            print_error "Required command '$cmd' not found. Please install it first."
            exit 1
        fi
    done

    if [ "$DRY_RUN" -eq 1 ]; then
        print_status "DRY RUN - No changes will be made"
    fi

    clean_build
    install_dependencies
    copy_files
    create_zip
    generate_checksums
    verify_build

    local end_time=$(date '+%Y-%m-%d %H:%M:%S')
    print_status "Build completed successfully at $end_time!"
    print_status "Release package: $BUILD_DIR/$ZIP_NAME"
}

# Run the build
main
