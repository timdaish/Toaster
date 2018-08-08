<?php
header('Content-Type: text/html');
date_default_timezone_set('UTC');
$serverName = 'http://'.$_SERVER['SERVER_NAME'];
$hostname = gethostname();
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $windows = defined('PHP_WINDOWS_VERSION_MAJOR');
//echo 'This is a server using Windows! '. $windows."<br/>";
    $OS = "Windows";
}
else {
//echo 'This is a server not using Windows!'."<br/>";
    $OS = PHP_OS;
}
?>
<html>
<head>
    <title>Show Stats</title>
    <link rel="stylesheet" type="text/css" href="../css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="../css/datatables_customised.css">
    <link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" type="text/css" href="stats.css">
</head>
<body>
    <h1>Toaster Stats.</h1>
<div id="stats">
<ul>
    <li id="alltime"><span id="alltimehdr" class="lihdr"></span><span id="alltimenumber" class="lino"></span></li>
    <li id="alltimecomplete"><span id="alltimecmphdr" class="lihdr"></span><span id="alltimecmpnumber" class="lino"></span></li>
    <li id="running"><span id="runninghdr" class="lihdr"></span><span id="runningnumber" class="lino"></span></li>
</ul>
</div> <!-- end stats -->
<table id="history" class="dataTable table-striped">
    <thead>
        <tr><th>ToastID</th><th>IP</th><th>URL</th><th>Started</th><th>Ended</th><th>Duration</th><th>Engine</th><th>HCH Loc</th><th>User IP</th></tr>
    </thead>
<tbody>
</tbody>
</table>
<script type="text/javascript" charset="utf8" src="../js/jquery.min.js"></script>
<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="moment.js"></script>
<script>

$(document).ready(function() {
 table = $('#history').DataTable(
     {
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "iDisplayLength": 10,
        "processing": true,
        // "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
        //     /* Append the imurl to the col0 */
        //    var imgsrc = aData[7]; // getting the value of the first (invisible) column
        //    var hrefloc = aData[8];
        //    //console.log("img src = " +imgsrc);

        //     $('td:eq(0)', nRow).html( '<a class="history" href="' + hrefloc + '" target="_blank">view screenshot</a>' ); //<img src="' + imgsrc + '" height=100 width=100 class="thumbnail"></img>
        // },
        "fnDrawCallback": function( oSettings ) {
        //     alert("redrawing");
        },
        "aaSorting": [[3, 'desc']], // sorts by a column and direction as set by the table type
        //  "columnDefs": [
        //     {
        //         "targets": [ 8,9 ],
        //         "visible": false,
        //         "searchable": false
        //     }
        // ]
    });

    showStats("full");
    setInterval(function(){ showStats("min"); }, 1000);
}); // end document ready

function isDST(t) { //t is the date object to check, returns true if daylight saving time is in effect.
    var jan = new Date(t.getFullYear(),0,1);
    var jul = new Date(t.getFullYear(),6,1);
    return Math.min(jan.getTimezoneOffset(),jul.getTimezoneOffset()) == t.getTimezoneOffset();  
}

function showStats(mode)
{
    var noofRunning = 0;
    var noofAllTime = 0;
    var noofAllTimeComplete = 0;
    // Assign handlers immediately after making the request,
    // and remember the jqxhr object for this request
    var jqxhr = $.get( "getstats_sql.php", function(data) {
//  alert( "success" );
//console.log("updating stats");
//console.log(data);
        noofAllTime = data.noofTestsAll;
        $("#alltimehdr").text("Total No. of Tests");
        $("#alltimenumber").text(noofAllTime);
        noofAllTimeComplete = data.noofCompleteAll;
        $("#alltimecmphdr").text("Total Complete Tests");
        $("#alltimecmpnumber").text(noofAllTimeComplete);
        noofRunning = data.noofRunning5mins;
        $("#runninghdr").text("No. of Tests Running");
        $("#runningnumber").text(noofRunning);

        if(mode == "full")
        {
            $.each(data.tests, function(tdx, tval) {
                var t1 = Date.parse(tval.sdt);
                var t2 = Date.parse(tval.edt);
//console.log(t1,t2);
                var dif = t1 - t2;
                if(moment(tval.sdt).isDST() == true)
                {
                    tval.sdt = moment(tval.sdt).subtract(1, 'hours');
                }
                if(moment(tval.edt).isDST() == true)
                {
                    tval.edt = moment(tval.edt).subtract(1, 'hours');
                }
                var Seconds_from_T1_to_T2 = dif / 1000;
                var Seconds_Between_Dates = Math.abs(Seconds_from_T1_to_T2);
                if(isNaN(Seconds_Between_Dates))
                    Seconds_Between_Dates = '';
                else
                    Seconds_Between_Dates = Seconds_Between_Dates + " secs";
//console.log(tdx,tval);
//console.log("url",tval.url);
                var a = $('#history').dataTable().fnAddData( [
                    tval.tid,
                    tval.svrip,
                    decodeURIComponent(tval.url),
                    tval.sdt,
                    tval.edt,
                    Seconds_Between_Dates,
                    tval.wbe,
                    decodeURIComponent(tval.hchloc),
                    tval.usrip
                    ]
                )
                var nTr = $('#history').dataTable().fnSettings().aoData[ a[0] ].nTr;
                // and parse the row:
                var nTds = $('td', nTr);
                nTds.eq(2).addClass('wrap')
            }) // end for each test
        } // end if mode full
    })
    .done(function() {
    //   alert( "second success" );
    })
    .fail(function() {
    //   alert( "error" );
    })
    .always(function() {
    //   alert( "finished" );
    });
    // Perform other work here ...
    // Set another completion function for the request above
    jqxhr.always(function() {
 // alert( "second finished" );
    });
} // end function showstats
</script>
</body>
</html>