#!/bin/sh

PATH="/usr/bin:/bin"
PID_FILE=run/postalCodeSearch.pid

start() {
    nohup php -c conf/php.ini bin/server.php >log/postalCodeSearch.log 2>&1 &
}

stop() {
    if [ -f "$PID_FILE" ]; then
        kill $(cat $PID_FILE)
        rm $PID_FILE
    fi
}

restart() {
    stop
    start
}

case "$1" in
    "start") start ;;
    "stop") stop ;;
    "restart") restart ;;
esac
