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

# Function to check if directory exists (case-insensitive)
dir_exists() {
    local dir=$1
    find "$(dirname "$dir")" -maxdepth 1 -type d -iname "$(basename "$dir")" | grep -q .
}

# Function to rename directory and update git if needed
rename_dir() {
    local from=$1
    local to=$2

    if dir_exists "$from"; then
        # If the directory already exists with correct case, skip
        if [ -d "$to" ]; then
            print_status "Directory $to already exists with correct case"
            return
        fi

        # Get the actual directory name (case-sensitive)
        actual_dir=$(find "$(dirname "$from")" -maxdepth 1 -type d -iname "$(basename "$from")" | head -n 1)

        if [ "$actual_dir" != "$to" ]; then
            print_status "Renaming $actual_dir to $to"
            mv "$actual_dir" "$to"

            # Update git if it's a git repository
            if [ -d ".git" ]; then
                git add "$to"
                git rm -r --cached "$actual_dir" 2>/dev/null || true
            fi
        fi
    else
        print_warning "Directory $from not found"
    fi
}

# Main process
main() {
    print_status "Fixing PSR-4 directory structure..."

    # Rename directories to match PSR-4
    rename_dir "includes/core" "includes/Core"
    rename_dir "includes/admin" "includes/Admin"
    rename_dir "includes/debug" "includes/Debug"
    rename_dir "includes/templates" "includes/Templates"

    print_status "Directory structure fixed. Running composer dump-autoload..."
    composer dump-autoload

    print_status "PSR-4 structure verification complete"
}

# Run the fix
main
