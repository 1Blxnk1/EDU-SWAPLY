#!/usr/bin/env bash
set -e

# --- Apache MPM hygiene -----------------------------------------------------
# We've been hitting AH00534 "More than one MPM loaded" on Railway. Do the
# cleanup at runtime so nothing the build did can be undone by image quirks.
echo "=== BEFORE cleanup: mods-enabled/mpm_* ==="
ls -la /etc/apache2/mods-enabled/ 2>/dev/null | grep -i mpm || echo "(none)"
echo "=== LoadModule mpm anywhere under /etc/apache2 ==="
grep -RIn "LoadModule.*mpm" /etc/apache2/ || echo "(none)"

rm -f /etc/apache2/mods-enabled/mpm_event.load \
      /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load \
      /etc/apache2/mods-enabled/mpm_worker.conf
a2enmod mpm_prefork >/dev/null

echo "=== AFTER cleanup: mods-enabled/mpm_* ==="
ls -la /etc/apache2/mods-enabled/ | grep -i mpm || echo "(none)"

# Railway injects $PORT. Apache defaults to 80; swap in $PORT so the platform's
# health checks can reach us.
PORT="${PORT:-8080}"
sed -ri "s!Listen 80!Listen ${PORT}!g" /etc/apache2/ports.conf
sed -ri "s!:80!:${PORT}!g" /etc/apache2/sites-available/*.conf

# Wait for MySQL to accept connections (Railway service may start in parallel).
if [ -n "${MYSQLHOST}" ]; then
  # Railway's MySQL uses a self-signed cert on its internal network. We're
  # connecting over the private network, so plain TCP is fine — disable TLS to
  # avoid "self-signed certificate in certificate chain" from the mysql client.
  # default-mysql-client on Debian is MariaDB's client; use --ssl=0 (Oracle's
  # --ssl-mode=DISABLED is unknown here). Railway's internal network is
  # already private so we don't need TLS.
  MYSQL_OPTS="--ssl=0 -h ${MYSQLHOST} -P ${MYSQLPORT:-3306} -u ${MYSQLUSER} -p${MYSQLPASSWORD}"

  echo "Waiting for MySQL at ${MYSQLHOST}:${MYSQLPORT:-3306}..."
  for i in $(seq 1 30); do
    if mysqladmin ping ${MYSQL_OPTS} --silent 2>/dev/null; then
      echo "MySQL is up."
      break
    fi
    sleep 2
  done

  # Seed the database the first time only. We detect "first time" by checking
  # whether the users table already exists in the target schema.
  EXISTS=$(mysql ${MYSQL_OPTS} -N -B -e \
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${MYSQLDATABASE}' AND table_name='users';" 2>/dev/null || echo 0)

  if [ "${EXISTS}" = "0" ]; then
    echo "Seeding ${MYSQLDATABASE} from database/swaply.sql..."
    # Strip CREATE DATABASE / USE lines so we import into Railway's provided db.
    grep -vE '^(CREATE DATABASE|USE )' /var/www/html/database/swaply.sql \
      | mysql ${MYSQL_OPTS} "${MYSQLDATABASE}"
    echo "Seed complete."
  else
    echo "Database already seeded; skipping import."
  fi

  # Apply idempotent migrations every boot (ADD COLUMN/CREATE TABLE IF NOT EXISTS)
  if [ -f /var/www/html/database/migrations.sql ]; then
    echo "Applying migrations..."
    mysql ${MYSQL_OPTS} --force "${MYSQLDATABASE}" < /var/www/html/database/migrations.sql 2>&1 || true
    echo "Migrations done."
  fi
fi

# Ensure the seller-upload directory exists and is writable. A Railway
# Volume mounted here starts empty and owned by root; chown so PHP
# (running as www-data) can write to it.
mkdir -p /var/www/html/assets/images/products
chown -R www-data:www-data /var/www/html/assets/images/products

exec "$@"
