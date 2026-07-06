#!/bin/bash
set -e

# Generate MSMTP config from environment variables
envsubst < /etc/msmtprc.template > /etc/msmtprc
chmod 600 /etc/msmtprc
chown www-data:www-data /etc/msmtprc

# Configure PHP to use MSMTP as sendmail
echo "sendmail_path = /usr/sbin/msmtp -t" > /usr/local/etc/php/conf.d/msmtp.ini

# Create log file
touch /var/log/msmtp.log
chown www-data:www-data /var/log/msmtp.log

# Execute the default entrypoint (Apache)
exec apache2-foreground
