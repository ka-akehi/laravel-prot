#!/bin/sh
set -eu

if [ -z "${SLACK_WEBHOOK_URL:-}" ]; then
  echo "SLACK_WEBHOOK_URL is not set" >&2
  exit 1
fi

ENVIRONMENT=${PROMETHEUS_ALERT_ENVIRONMENT:-dev}

sed \
  -e "s|__SLACK_WEBHOOK_URL__|$SLACK_WEBHOOK_URL|g" \
  -e "s|__ENVIRONMENT__|$ENVIRONMENT|g" \
  /etc/alertmanager/alertmanager.tmpl.yml > /tmp/alertmanager.yml

exec /bin/alertmanager --config.file=/tmp/alertmanager.yml "$@"
