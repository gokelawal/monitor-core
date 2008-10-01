<?php

/* Pass in by reference! */
function graph_cpu_report ( &$rrdtool_graph ) {

    global $context,
           $cpu_idle_color,
           $cpu_nice_color,
           $cpu_system_color,
           $cpu_user_color,
           $cpu_wio_color,
           $hostname,
           $range,
           $rrd_dir,
           $size,
           $strip_domainname;

    if ($strip_domainname) {
       $hostname = strip_domainname($hostname);
    }

    $rrdtool_graph['height'] += ($size == 'medium') ? 14 : 0;
    $title = 'CPU';
    if ($context != 'host') {
       $rrdtool_graph['title'] = $title;
    } else {
       $rrdtool_graph['title'] = "$hostname $title last $range";
    }
    $rrdtool_graph['upper-limit']    = '100';
    $rrdtool_graph['lower-limit']    = '0';
    $rrdtool_graph['vertical-label'] = 'Percent';
    $rrdtool_graph['extras']         = '--rigid';

    if($context != "host" ) {

        /*
         * If we are not in a host context, then we need to calculate
         * the average
         */
        $series =
              "DEF:'num_nodes'='${rrd_dir}/cpu_user.rrd':'num':AVERAGE "
            . "DEF:'cpu_user'='${rrd_dir}/cpu_user.rrd':'sum':AVERAGE "
            . "CDEF:'ccpu_user'=cpu_user,num_nodes,/ "
            . "DEF:'cpu_nice'='${rrd_dir}/cpu_nice.rrd':'sum':AVERAGE "
            . "CDEF:'ccpu_nice'=cpu_nice,num_nodes,/ "
            . "DEF:'cpu_system'='${rrd_dir}/cpu_system.rrd':'sum':AVERAGE "
            . "CDEF:'ccpu_system'=cpu_system,num_nodes,/ "
            . "DEF:'cpu_idle'='${rrd_dir}/cpu_idle.rrd':'sum':AVERAGE "
            . "CDEF:'ccpu_idle'=cpu_idle,num_nodes,/ "
            . "AREA:'ccpu_user'#$cpu_user_color:'User' "
            . "'GPRINT:ccpu_user:AVERAGE:%.1lf%%' "
            . "STACK:'ccpu_nice'#$cpu_nice_color:'Nice' "
            . "'GPRINT:ccpu_nice:AVERAGE:%.1lf%%' "
            . "STACK:'ccpu_system'#$cpu_system_color:'System' "
            . "'GPRINT:ccpu_system:AVERAGE:%.1lf%%' ";

        if (file_exists("$rrd_dir/cpu_wio.rrd")) {
            $series .= "DEF:'cpu_wio'='${rrd_dir}/cpu_wio.rrd':'sum':AVERAGE "
                ."CDEF:'ccpu_wio'=cpu_wio,num_nodes,/ "
                ."STACK:'ccpu_wio'#$cpu_wio_color:'WAIT' "
                ."'GPRINT:ccpu_wio:AVERAGE:%.1lf%%' ";
        }

        $series .= "STACK:'ccpu_idle'#$cpu_idle_color:'Idle' ";
        $series .= "'GPRINT:ccpu_idle:AVERAGE:%.1lf%%' ";
        $series .= "CDEF:util=100,ccpu_idle,- ";

    } else {

        /* Context is not "host" */

        $series ="DEF:'cpu_user'='${rrd_dir}/cpu_user.rrd':'sum':AVERAGE "
        ."DEF:'cpu_nice'='${rrd_dir}/cpu_nice.rrd':'sum':AVERAGE "
        ."DEF:'cpu_system'='${rrd_dir}/cpu_system.rrd':'sum':AVERAGE "
        ."DEF:'cpu_idle'='${rrd_dir}/cpu_idle.rrd':'sum':AVERAGE "
        ."AREA:'cpu_user'#$cpu_user_color:'User' "
        ."'GPRINT:cpu_user:AVERAGE:%.1lf%%' "
        ."STACK:'cpu_nice'#$cpu_nice_color:'Nice' "
        ."'GPRINT:cpu_nice:AVERAGE:%.1lf%%' "
        ."STACK:'cpu_system'#$cpu_system_color:'System' "
        ."'GPRINT:cpu_system:AVERAGE:%.1lf%%' ";

        if (file_exists("$rrd_dir/cpu_wio.rrd")) {
            $series .= "DEF:'cpu_wio'='${rrd_dir}/cpu_wio.rrd':'sum':AVERAGE ";
            $series .= "STACK:'cpu_wio'#$cpu_wio_color:'WAIT' ";
            $series .= "'GPRINT:cpu_wio:AVERAGE:%.1lf%%' ";
        }

        $series .= "STACK:'cpu_idle'#$cpu_idle_color:'Idle' ";
        $series .= "'GPRINT:cpu_idle:AVERAGE:%.1lf%%' ";
        $series .= "CDEF:util=100,cpu_idle,- ";
    }
    $series .= "'GPRINT:util:AVERAGE:(%.1lf%% Avg Usage)' ";

    $rrdtool_graph['series'] = $series;

    return $rrdtool_graph;
}

?>