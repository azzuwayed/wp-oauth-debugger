#!/bin/bash
set -e
trap 'handle_error $? $LINENO' ERR

# ----------------------------------------
# Config
# ----------------------------------------
config() {
    PLUGIN_NAME="wp-oauth-debugger"
    BUILD_DIR="build"
    BACKUP_DIR="${BUILD_DIR}/backups"
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    LOG_FILE="${BUILD_DIR}/build_${TIMESTAMP}.log"
    LATEST_LOG="${BUILD_DIR}/latest.log"
    VERBOSE=0
    DRY_RUN=0
    PARALLEL=0
    CUSTOM_VERSION=""
    SELF_TEST=0
}

# ----------------------------------------
# CLI Arguments
# ----------------------------------------
parse_args() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            -v|--verbose) VERBOSE=1 ;;
            -d|--dry-run) DRY_RUN=1 ;;
            -p|--parallel) PARALLEL=1 ;;
            -V|--version) CUSTOM_VERSION="$2"; shift ;;
            --self-test) SELF_TEST=1 ;;
            -h|--help)
                echo "Usage: $0 [--verbose] [--dry-run] [--parallel] [--version x.y.z] [--self-test]"
                exit 0 ;;
            *) log ERROR "Unknown option: $1"; exit 1 ;;
        esac
        shift
    done
}

# ----------------------------------------
# Logging
# ----------------------------------------
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
log() {
    local level="$1"; shift
    local color message="$*"
    case "$level" in
        INFO) color=$GREEN ;;
        DEBUG) [[ "$VERBOSE" -eq 1 ]] || return; color=$BLUE ;;
        WARN) color=$YELLOW ;;
        ERROR) color=$RED ;;
    esac
    echo -e "${color}${level}:${NC} $message" | tee -a "$LOG_FILE" >&2
}

handle_error() {
    log ERROR "Script failed at line $2 with exit code $1"
    cleanup
    exit "$1"
}

cleanup() {
    [[ -d "$TEMP_DIR" ]] && log INFO "Verifying zip contents structure..."
  TEMP_DIR=$(mktemp -d)
  unzip -q "$BUILD_DIR/$ZIP_NAME" -d "$TEMP_DIR"

  required_files=(
    "$TEMP_DIR/$PLUGIN_NAME/$MAIN_PLUGIN_FILE"
    "$TEMP_DIR/$PLUGIN_NAME/includes/Core/Activator.php"
    "$TEMP_DIR/$PLUGIN_NAME/vendor/autoload.php"
  )

  for file in "${required_files[@]}"; do
    [ -f "$file" ] || {
      log ERROR "Missing file in zip: $file"
      rm -rf "$TEMP_DIR"
      exit 1
    }
  done

  rm -rf "$TEMP_DIR"
}

# ----------------------------------------
# Helpers
# ----------------------------------------
detect_main_plugin_file() {
    for file in *.php; do
        if grep -q "Plugin Name:" "$file"; then
            echo "$file"
            return
        fi
    done
    log ERROR "No main plugin file found"; exit 1
}

extract_version() {
    MAIN_PLUGIN_FILE=$(detect_main_plugin_file)
    VERSION=$(grep -E "^[[:space:]]*\*[[:space:]]*Version:" "$MAIN_PLUGIN_FILE"         | sed -E 's/.*Version:[[:space:]]*([0-9]+\.[0-9]+\.[0-9]+).*/\1/')
    VERSION="${CUSTOM_VERSION:-$VERSION}"
    if ! echo "$VERSION" | grep -E '^[0-9]+\.[0-9]+\.[0-9]+$' > /dev/null; then
        log ERROR "Invalid version format: $VERSION"; exit 1
    fi
    ZIP_NAME="$PLUGIN_NAME-v$VERSION.zip"
    RELEASE_DIR="$BUILD_DIR/$PLUGIN_NAME"
}

verify_git_status() {
    if [ -d .git ]; then
        git diff --quiet || log WARN "You have uncommitted changes."
        GIT_TAG="v$VERSION"
        if git rev-parse "$GIT_TAG" >/dev/null 2>&1; then
            log DEBUG "Git tag $GIT_TAG exists."
        else
            git tag "$GIT_TAG"
            log INFO "Git tag created: $GIT_TAG"
        fi
    fi
}

init_dirs() {
    mkdir -p "$BUILD_DIR" "$BACKUP_DIR"
    ln -sf "$(basename "$LOG_FILE")" "$LATEST_LOG"
}

install_dependencies() {
    log INFO "Installing dependencies..."
    if [ "$DRY_RUN" -eq 1 ]; then
        log DEBUG "Would run: composer install"
    else
        composer install --no-dev --optimize-autoloader --no-interaction
    fi
}

clean_and_prepare() {
    [[ "$DRY_RUN" -eq 1 ]] && return
    rm -rf "$BUILD_DIR"
    mkdir -p "$RELEASE_DIR"
}

copy_files() {
    log INFO "Copying plugin files..."
    local dirs=(includes vendor languages assets)
    local files=(composer.json composer.lock uninstall.php README.md "$MAIN_PLUGIN_FILE")

    for dir in "${dirs[@]}"; do
        [ -d "$dir" ] && {
            if [ "$PARALLEL" -eq 1 ] && command -v rsync >/dev/null; then
                rsync -a "$dir" "$RELEASE_DIR/"
            else
                cp -R "$dir" "$RELEASE_DIR/"
            fi
        }
    done


  log INFO "Setting permissions..."
  find "$RELEASE_DIR" -type f -exec chmod 644 {} \;
  find "$RELEASE_DIR" -type d -exec chmod 755 {} \;


  for file in "${files[@]}"; do
        [ -f "$file" ] && cp "$file" "$RELEASE_DIR/"
    done
}

create_zip() {
    log INFO "Creating zip file..."
    cd "$BUILD_DIR"
    zip -r "$ZIP_NAME" "$PLUGIN_NAME" -x "*.git*" "*.DS_Store*" "*.zip" >/dev/null
    cd ..
}

generate_checksums() {
    cd "$BUILD_DIR"
    {
        echo "MD5:"; md5sum "$ZIP_NAME"
        echo -e "\nSHA256:"; sha256sum "$ZIP_NAME"
    } > "${ZIP_NAME%.zip}_checksums.txt"
    cd ..
}

verify_build() {
    log INFO "Verifying build..."
    [ -f "$BUILD_DIR/$ZIP_NAME" ] || {
        log ERROR "Zip not created"; exit 1
    }

    TEMP_DIR=$(mktemp -d)
    unzip -q "$BUILD_DIR/$ZIP_NAME" -d "$TEMP_DIR"
    [ -f "$TEMP_DIR/$PLUGIN_NAME/$MAIN_PLUGIN_FILE" ] || {
        log ERROR "Main plugin file missing in zip"; exit 1
    }
    log INFO "Verifying zip contents structure..."
  TEMP_DIR=$(mktemp -d)
  unzip -q "$BUILD_DIR/$ZIP_NAME" -d "$TEMP_DIR"

  required_files=(
    "$TEMP_DIR/$PLUGIN_NAME/$MAIN_PLUGIN_FILE"
    "$TEMP_DIR/$PLUGIN_NAME/includes/Core/Activator.php"
    "$TEMP_DIR/$PLUGIN_NAME/vendor/autoload.php"
  )

  for file in "${required_files[@]}"; do
    [ -f "$file" ] || {
      log ERROR "Missing file in zip: $file"
      rm -rf "$TEMP_DIR"
      exit 1
    }
  done

  rm -rf "$TEMP_DIR"
}

print_env_info() {
    log INFO "Environment Info:"
    echo "  Shell: $SHELL"
    echo "  OS: $(uname -s) $(uname -r)"
    echo "  Dir: $(pwd)"
    echo "  Version: $VERSION"
}


fix_case_sensitivity() {
  log INFO "Fixing case sensitivity issues..."

  declare -a from_dirs=("core" "admin" "debug" "security")
  declare -a to_dirs=("Core" "Admin" "Debug" "Security")

  for i in "${!from_dirs[@]}"; do
    src="$RELEASE_DIR/includes/${from_dirs[$i]}"
    dst="$RELEASE_DIR/includes/${to_dirs[$i]}"
    if [[ -d "$src" && ! -d "$dst" ]]; then
      mv "$src" "$dst"
      log INFO "Renamed $src → $dst"
    fi
  done
}

# Security scanning
security_scan() {
    log INFO "Running security audit..."
    if command -v composer-audit >/dev/null; then
        composer audit || log WARN "Security audit found issues"
    else
        log WARN "composer-audit not found, skipping security scan"
    fi
}

# PHP compatibility check
check_php_compatibility() {
    log INFO "Checking PHP compatibility..."
    if command -v phpcs >/dev/null; then
        phpcs --standard=PHPCompatibility --runtime-set testVersion 8.3- . || log WARN "PHP compatibility check found issues"
    else
        log WARN "phpcs not found, skipping PHP compatibility check"
    fi
}

# WordPress coding standards check
check_wp_standards() {
    log INFO "Checking WordPress coding standards..."
    if command -v phpcs >/dev/null; then
        phpcs --standard=WordPress --extensions=php . || log WARN "WordPress coding standards check found issues"
    else
        log WARN "phpcs not found, skipping WordPress standards check"
    fi
}

# Track build information
track_build_info() {
    local zip_file="$BUILD_DIR/$ZIP_NAME"
    if [ -f "$zip_file" ]; then
        local total_files=$(unzip -l "$zip_file" | tail -n 1 | awk '{print $2}')
        local build_size=$(du -h "$zip_file" | cut -f1)
        local md5_sum=$(md5sum "$zip_file" | cut -d' ' -f1)
        local sha256_sum=$(sha256sum "$zip_file" | cut -d' ' -f1)

        # Save build info to a JSON file
        cat > "$BUILD_DIR/build_info.json" << EOF
{
    "version": "$VERSION",
    "build_date": "$(date -u +"%Y-%m-%dT%H:%M:%SZ")",
    "total_files": $total_files,
    "build_size": "$build_size",
    "requirements": {
        "php": "8.3",
        "wordpress": "6.5"
    },
    "checksums": {
        "md5": "$md5_sum",
        "sha256": "$sha256_sum"
    }
}
EOF
        log INFO "Build information saved to build_info.json"
    fi
}

# Update version in files
update_version() {
    local new_version="$1"
    log INFO "Updating version to $new_version in files..."

    # Update main plugin file
    if [ -f "$MAIN_PLUGIN_FILE" ]; then
        sed -i.bak "s/Version:.*/Version: $new_version/" "$MAIN_PLUGIN_FILE"
        rm -f "${MAIN_PLUGIN_FILE}.bak"
    fi

    # Update composer.json if it exists
    if [ -f "composer.json" ]; then
        sed -i.bak "s/\"version\":.*/\"version\": \"$new_version\",/" composer.json
        rm -f composer.json.bak
    fi
}

# Generate changelog
generate_changelog() {
    local last_tag=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
    local changelog_file="CHANGELOG.md"

    log INFO "Generating changelog since $last_tag..."

    # Create or update CHANGELOG.md
    {
        echo "# Changelog"
        echo
        echo "## [$VERSION] - $(date +%Y-%m-%d)"
        echo
        echo "### Changes"
        git log --pretty=format:"- %s" $last_tag..HEAD
        echo
        [ -f "$changelog_file" ] && cat "$changelog_file"
    } > "${changelog_file}.new"

    mv "${changelog_file}.new" "$changelog_file"
    log INFO "Changelog updated in $changelog_file"
}

# ----------------------------------------
# Main
# ----------------------------------------
main() {
    config
    parse_args "$@"
    init_dirs
    extract_version
    print_env_info
    verify_git_status

    if [[ "$SELF_TEST" -eq 1 ]]; then
        install_dependencies
        check_php_compatibility
        check_wp_standards
        security_scan
        verify_build
        log INFO "Self-test passed."
        exit 0
    fi

    log INFO "Starting build for $PLUGIN_NAME v$VERSION"

    # Run validation if not in dry-run mode
    if [ "$DRY_RUN" -eq 0 ]; then
        check_php_compatibility
        check_wp_standards
        security_scan
    fi

    clean_and_prepare
    install_dependencies
    copy_files
    create_zip
    generate_checksums
    track_build_info
    verify_build

    # Update version and changelog if not in dry-run mode
    if [ "$DRY_RUN" -eq 0 ]; then
        update_version "$VERSION"
        generate_changelog
    fi

    log INFO "Build completed: $BUILD_DIR/$ZIP_NAME"
    log INFO "Build information: $BUILD_DIR/build_info.json"
}

main "$@"
