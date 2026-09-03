#!/bin/sh
set -e

# Disable conflicting MPMs safely (no-op if already disabled)
a2dismod mpm_event mpm_worker 2>/dev/null || true

# Ensure any leftover mpm load files are removed from mods-enabled (robustness)
for m in mpm_event mpm_worker; do
  rm -f /etc/apache2/mods-enabled/${m}.load /etc/apache2/mods-enabled/${m}.conf || true
done

# Ensure prefork MPM is enabled
a2enmod mpm_prefork 2>/dev/null || true

port=${PORT:-80}

# Only modify ports.conf if it exists
if [ -f /etc/apache2/ports.conf ]; then
  sed -ri "s/Listen [0-9]+/Listen ${port}/" /etc/apache2/ports.conf
fi

# Only update default vhost if it exists
if [ -f /etc/apache2/sites-available/000-default.conf ]; then
  sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf
fi

# Exec the original entrypoint to start apache
exec docker-php-entrypoint apache2-foreground
