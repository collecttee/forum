while true;
do
    count=`ps aux | grep 'listen1' | grep -v 'grep' -c`
    if [ $count -eq 0 ]; then
            now=`date +%F\ %T`
            echo "[$now] php1 is offline, try to restart..." >> check_es.log
            nohup php listen1.php > 1.log 2>&1 &
    else
            now=`date +%F\ %T`
            echo "[$now] php1 is online, everything seems to be OK..." >> check_es.log
    fi
    sleep 2
done
