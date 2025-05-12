#!/bin/bash

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Print with color
print_status() {
    echo -e "${GREEN}==>${NC} $1"
}

print_error() {
    echo -e "${RED}Error:${NC} $1" >&2
}

print_warning() {
    echo -e "${YELLOW}Warning:${NC} $1"
}

# Pre-release checks
pre_release_checks() {
    print_status "Running pre-release checks..."

    # Check for uncommitted changes
    if ! git diff --quiet; then
        print_error "You have uncommitted changes. Please commit or stash them first."
        exit 1
    fi

    # Check if we're on main branch
    if [ "$(git branch --show-current)" != "main" ]; then
        print_error "You must be on the main branch to release."
        exit 1
    fi

    # Check if remote is up to date
    git fetch origin
    if [ "$(git rev-parse HEAD)" != "$(git rev-parse origin/main)" ]; then
        print_error "Your local main branch is not up to date with origin/main."
        exit 1
    fi
}

# Validate version format
validate_version() {
    local version="$1"
    if [[ ! "$version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        print_error "Invalid version format. Use x.y.z"
        exit 1
    fi

    # Check if version already exists
    if git rev-parse "v$version" >/dev/null 2>&1; then
        print_error "Version v$version already exists!"
        exit 1
    fi

    # Check if version is higher than last tag
    local last_tag=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
    local last_version=${last_tag#v}
    if [ "$(printf '%s\n' "$last_version" "$version" | sort -V | head -n1)" = "$version" ]; then
        print_error "New version ($version) must be higher than last version ($last_version)"
        exit 1
    fi
}

# Generate release notes
generate_release_notes() {
    local version="$1"
    local last_tag=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")

    print_status "Generating release notes..."

    # Get commit messages since last tag
    local changes=$(git log --pretty=format:"- %s" $last_tag..HEAD)

    # Create release notes
    cat > "release_notes.md" << EOF
# Release v$version

## Changes since $last_tag

$changes

## Build Information
- Release Date: $(date +%Y-%m-%d)
- PHP Version: 7.4
EOF

    print_status "Release notes generated in release_notes.md"
}

# Main release process
main() {
echo "--------------------------------"
echo "🛠  WordPress Plugin Release Tool"
echo "--------------------------------"

    # Run pre-release checks
    pre_release_checks

    # Get version
read -p "Enter new version (x.y.z): " VERSION
    validate_version "$VERSION"

    # Generate release notes
    generate_release_notes "$VERSION"

    # Show release notes for review
    echo
    echo "Release notes:"
    echo "--------------------------------"
    cat release_notes.md
    echo "--------------------------------"

    # Confirm release
    read -p "Proceed with release? [y/N] " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_warning "Release cancelled."
  exit 1
fi

    # Build plugin
    print_status "Building plugin v$VERSION..."
./build_plugin.sh --version "$VERSION"

    # Commit changes
    print_status "Committing changes..."
git add .
git commit -m "🔖 Release v$VERSION"

    # Create and push tag
    print_status "Creating and pushing tag..."
    git tag -a "v$VERSION" -m "Release v$VERSION"
git push origin main --tags

    print_status "🚀 Release v$VERSION completed successfully!"
    print_status "Release notes: release_notes.md"
    print_status "Build package: build/wp-oauth-debugger-v$VERSION.zip"
}

# Run the release process
main
