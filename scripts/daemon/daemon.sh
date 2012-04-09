#!/bin/bash
### BEGIN INIT INFO
# Provides:          sapDaemon
# Required-Start:    $local_fs $network
# Required-Stop:     $local_fs $network
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: Start/stop sapDaemon Call-Log
### END INIT INFO

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ] ; do SOURCE="$(readlink "$SOURCE")"; done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

PIDFILE="/tmp/sapDaemon.pid"
sapDaemon_CLI="$DIR/init.php"
cd $DIR

do_start() {
        if [ -f "$PIDFILE" ] ; then
            STATUS=$(ps -p `cat $PIDFILE` --no-heading)
            if [ ! -z "$STATUS" ] ; then
                echo "sapDaemon ist bereits gestartet" >&2
                exit 2
            fi
        fi
        php -f $sapDaemon_CLI &> sapDaemon.log &
        echo "sapDaemon erfolgreich gestartet"
}

do_stop() {
        get_status
        if [ $? -eq 1 ] ; then
            echo -n "sapDaemon beenden"
            kill -15 `cat $PIDFILE`
            while [ -f "$PIDFILE" ]
            do
               echo -n "."
               sleep 5
            done
            echo " erfolgreich beendet"
        else
            echo "sapDaemon ist nicht gestartet"
        fi
}

get_status() {
    if [ -f "$PIDFILE" ] ; then
        STATUS=$(ps -p `cat $PIDFILE` --no-heading)
        if [ ! -z "$STATUS" ] ; then
            return 1
        fi
    fi
    return 0
}


if [ ! -x $sapDaemon_CLI ] ; then
    echo "sapDaemon ist nicht vorhanden oder nicht ausführbar" >&2
    exit 2
fi

case "$1" in
  restart)
        do_stop
        do_start
        ;;
  start)
        do_start
        ;;
  status)
        get_status
        if [ $? -eq 1 ] ; then
            echo "sapDaemon ist aktiv mit PID `cat $PIDFILE`"
        else
            echo "sapDaemon ist nicht aktiv"
        fi
        ;;
  stop)
        do_stop
        ;;
  *)
        echo "Usage: $0 start|stop|restart" >&2
        exit 3
        ;;
esac
