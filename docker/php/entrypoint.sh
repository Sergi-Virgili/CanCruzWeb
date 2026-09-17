#!/bin/sh

# If vendor directory doesn't have dev dependencies (volume mount from host), copy from image
if [ ! -d /var/www/html/vendor/laravel/boost ]; then
    echo "Initializing vendor directory from image..."
    cp -r /var/www/html/vendor-image/* /var/www/html/vendor/ 2>/dev/null || true
fi

# If node_modules is empty (volume mount), copy from image
if [ ! -d /app/node_modules ] || [ -z "$(ls -A /app/node_modules 2>/dev/null)" ]; then
    echo "Initializing node_modules directory from image..."
    cp -r /app/node_modules-image/* /app/node_modules/ 2>/dev/null || true
fi

exec "$@"