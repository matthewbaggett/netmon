#!/usr/bin/env bash
speedtest-cli --json > speedtest-report.json
/app/push-to-redis.php
rm speedtest-report.json