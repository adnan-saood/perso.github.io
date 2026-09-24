#!/bin/bash
set -e

echo "Entry point script running"

CONFIG_FILE=_config.yml
DEV_CONFIG=_config_dev.yml

# Build config args
CONFIG_ARGS="$CONFIG_FILE"
if [ -f "$DEV_CONFIG" ]; then
    echo "Development config detected: $DEV_CONFIG"
    CONFIG_ARGS="$CONFIG_FILE,$DEV_CONFIG"
fi

# Run Jekyll with stable flags for container development
# --watch: Monitor files for changes and regenerate
# --force_polling: Better file watching in containers (reliable for Docker mounts)
# --disable-disk-cache: Prevents stale cache issues during regeneration
echo "Starting Jekyll on 0.0.0.0:8080 with config: $CONFIG_ARGS"
bundle exec jekyll serve \
    --watch \
    --port=8080 \
    --host=0.0.0.0 \
    --force_polling \
    --disable-disk-cache \
    --config "$CONFIG_ARGS"
