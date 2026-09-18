#!/bin/sh

APP_DIR=/var/www/html

# Recreate directories that bind mounts may have masked
mkdir -p \
    "$APP_DIR/storage/framework/cache" \
    "$APP_DIR/storage/framework/sessions" \
    "$APP_DIR/storage/framework/views" \
    "$APP_DIR/storage/framework/testing" \
    "$APP_DIR/bootstrap/cache"

# Make bind-mounted directories writable by the runtime user. As www-data this
# is a no-op; on hosts that run the container as root it fixes Linux bind mounts.
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true

# Seed the vendor volume from the image on first run (host bind mount is empty)
if [ ! -d "$APP_DIR/vendor/laravel/boost" ]; then
    echo "Initializing vendor directory from image..."
    cp -r "$APP_DIR/vendor-image/." "$APP_DIR/vendor/" 2>/dev/null || true
fi

# Seed the node_modules volume from the image on first run
if [ ! -d /app/node_modules ] || [ -z "$(ls -A /app/node_modules 2>/dev/null)" ]; then
    echo "Initializing node_modules directory from image..."
    cp -r /app/node_modules-image/. /app/node_modules/ 2>/dev/null || true
fi

exec "$@"