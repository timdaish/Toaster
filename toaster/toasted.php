<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Toasted Webpages</title>
<link rel="stylesheet" type="text/css" href="/toaster/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="/toaster/css/datatables_customised.css">
<link rel="stylesheet" type="text/css" href="/toaster/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="/toaster/bootstrap/css/bootstrap-theme.min.css">
<link rel="stylesheet" type="text/css" href="/toaster/css/toasterpage.css">
</head>
<body>
<div id="hwrapper">
<h2><a href="/toaster/toaster.htm" target="_blank"><img class="toaster" src="/toaster/images/toaster_tn.png" width="64" height="38" alt="Webpage Toaster"></a>
The Webpage Toaster's History of Optimisation and Analysis Reports</h2>
<input type="date" id="toastDate" value="2014-02-09">
<table id="toastedtab" class="dataTable table-striped history">
<thead>
    <th><span class="glyphicon glyphicon-picture">&nbsp;</span></th><th><span class="glyphicon glyphicon-link">&nbsp;</span>Page URL</th><th><span class="glyphicon glyphicon-header">&nbsp;</span>Page Title</th><th><span class="glyphicon glyphicon-upload">&nbsp;</span>HAR File</th><th><span class="glyphicon glyphicon-fire">&nbsp;</span>Browser Engine</th><th><span class="glyphicon glyphicon-phone">&nbsp;</span>Device</th><th><span class="glyphicon glyphicon-time">&nbsp;</span>Toasted at</th><th><span class="glyphicon glyphicon-pencil">&nbsp;</span>Notes</th>
</thead>
<tbody>
</tbody></table>
<?php
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $windows = defined('PHP_WINDOWS_VERSION_MAJOR');
//echo 'This is a server using Windows! '. $windows."<br/>";
    $OS = "Windows";
}
else {
//echo 'This is a server not using Windows!'."<br/>";
    $OS = PHP_OS;
}
$hostname = gethostname();
ini_set("auto_detect_line_endings", false);
?>
<div style="clear: both;"></div>
<button id="rescore" style="color: black;">Rescore</button>
<script type="text/javascript" charset="utf8" src="/toaster/js/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
var table;
function dtpad(n){
    return n > 9 ? "" + n: "0" + n;
}
$(document).ready(function() {
    // populate date
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!
    var yyyy = today.getFullYear();
   
    if(dd<10) {
        dd = '0'+dd
    } 

    if(mm<10) {
        mm = '0'+mm
    } 

    today = yyyy + '-' + mm + '-' + dd;

     $('#toastDate').val(today);
     
    

    $( "#rescore" ).click(function() {
        console.log("rescoring");
        
        table.rows().iterator( 'row', function ( context, index ) {
            // ... do something with data(), or this.node(), etc
           // console.log(this.row( index ).node(1));
        } );

// iterate through array, extract URLS and reload those for the current table page
var arrayURLS = table.column( 1, {order:'current',page: 'current' } ).data();
$.each( arrayURLS , function( key, value ) {
    value = value.substr(25);
    if(key < 25) // put limit
    {
        var pos = value.indexOf("\"");
        var svalue = value.substr(1,pos - 1);
//console.log( "reloading " + ": " +  window.location.hostname + "/" + svalue );

        var win=window.open(  "/" + svalue, '_blank');
        win.focus();
        win.addEventListener('load', function() { win.close(); } , false);
    }
});

    });


    table = $('#toastedtab').DataTable(
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
        "aaSorting": [[6, 'desc']], // sorts by a column and direction as set by the table type
        //  "columnDefs": [
        //     {
        //         "targets": [ 8,9 ],
        //         "visible": false,
        //         "searchable": false
        //     }
        // ]
    });


$("#toastDate").trigger( "change");
} ); // document ready



$("#toastDate").change(function() {
    // get date
    var td = $("#toastDate").val();
//console.log("td",td);
    var dt = new Date(td);
//console.log("date dt",dt);
    var dtstr = dt.getFullYear() + "/" + dtpad(dt.getMonth()+1) + "/" + dtpad(dt.getDate());
//console.log("str",dtstr);

     var fp = "<?php 
     if($OS == "Windows")
        $fp = "../../toast/";
     else
        {
            //set path for webpagetoaster server and others
            if( strpos($hostname,"gridhost.co.uk") != false)
            {
                $fp = 'https://www.webpagetoaster.com/toast/';
            }
            else
            {
                $fp = '/usr/share/toast/';
            }
        }
    echo $fp; ?>";
//console.log("fp",fp);
    var  toastedfileUrl = fp + dtstr + "/" + "toasted.json";
//console.log("reading json from ",toastedfileUrl);
$('#toastedtab').DataTable().clear();
        $('#toastedtab tbody').empty();
        
    $.ajax({
        url: toastedfileUrl ,
        type: "get",
        dataType: "text",
        data: {
        },
        success: function(data, textStatus, jqXHR) {
            // since we are using jQuery, you don't need to parse response
            console.log("success");
//console.log(data);



        var lines = data.split("\n");
        for (var i = 0, len = lines.length -1; i < len; i++) {
//console.log(lines[i]);
            var item= JSON.parse(lines[i]);
            if(item !== [])
            {

                var x = location.hostname;
                var svr = fp ;
                console.log(x);
//console.log(item.datetime,item.url, item.ua);
                // $.each(j, function(i, item) {
                //     console.log(i,item);
                    
                //    });

                // if($OS == "Windows")
                //     {
                //         $harfile= substr(item.$harfile,2);
                //     }
                //     else // linux
                //         if(strpos($hostname,"gridhost.co.uk") != false)
                //         {
                // //echo("linux screenshot path: " . $ss ."<br/>" );
                //             if(substr($ss,0,6) == "/toast")
                //                 $ss = substr($ss,6);
                //                 $ss = "https://www.webpagetoaster.com" . $ss;

                //             // harfile
                //             $harfile= str_replace("/var/sites/w/webpagetoaster.com/public_html/toast","",item.harfile);
                //             $harfile = "https://www.webpagetoaster.com" . harfile;

                //         }
                //         else
                //             $harfile= str_replace("/usr/share","",$harfile);
// console.log(item.harfile);
                
                    var hp = item.harfile;
                    var harfile = '';
                    if(hp.indexOf("/var/sites/w/webpagetoaster.com") !== -1)
                    {
                        hp = hp.replace("/var/sites/w/webpagetoaster.com/public_html/toast","");
                        harfile = svr + hp;
//console.log("www harfile");
                    }
                    else
                    {
//console.log("hp",hp);        
                        harfile = hp.substr(2);
//console.log("harfile",harfile);
                    }

                    var deviceUA = '';
                    if(item.ua.indexOf('Desktop') !=false)
                    {
                        switch(item.ua)
                        {
                            case "Chrome Desktop":
                                deviceUA = '<img src="/toaster/images/chrome.png"></img><img src="/toaster/images/desktop.png"></img><br/>'+item.ua;
                                break;
                            case "Firefox Desktop":
                                deviceUA = '<img src="/toaster/images/mozilla_firefox.png"></img><img src="/toaster/images/desktop.png"></img><br/>'+item.ua;
                                break;
                            case "IE Desktop":
                                deviceUA = '<img src="/toaster/images/internet_explorer.png"></img><img src="/toaster/images/desktop.png"></img><br/>'+item.ua;
                                break;
                            default:
                                deviceUA = $ua;
                                break;
                        }
                    }
                    else
                    {
                        if(item.ua.indexOf('iOS') !==false)
                        {
                            if(item.ua.indexOf('iPad') !==false)
                            deviceUA = '<img src="/toaster/images/safari.png"></img><img src="/toaster/images/ipad.png"></img><br/>'+item.ua;
                            else
                            deviceUA = '<img src="/toaster/images/safari.png"></img><img src="/toaster/images/iphone.png"></img><br/>'+item.ua;
                        }

                        if(item.ua.indexOf('Android') !==false)
                        {
                            if(item.ua.indexOf('M') !==false)
                            deviceUA = '<img src="/toaster/images/chrome.png"></img><img src="/toaster/images/android_mobile.png"></img><br/>'+item.ua;
                            else
                            deviceUA = '<img src="/toaster/images/chrome.png"></img><img src="/toaster/images/android_tablet.png"></img><br/>'+item.ua;
                        }

                        if(item.ua.indexOf('Googlebot') !==false)
                            deviceUA = '<img src="/toaster/images/googlebot.png"></img><br/>'+item.ua;
                    }
                    var pt = '';
                    console.log(item.toastedwebname);
                    try{
                        pt = item.pagetitle;
                    }
                    catch(err) {
                //document.getElementById("demo").innerHTML = err.message;
                    }
                //    var $tr = $('<tr>').append(
                //         $('<td>').html('<a class="history" href="' + item.imgname + '" target="_blank">view screenshot</a>'),
                //         $('<td>').html('<a href="'+item.toastedwebname+'" target="_blank">'+item.url+"</a>"),
                //         $('<td>').html(encodeURIComponent(pt)),
                //         $('<td>').html('<a href="'+harfile+'" download>Download HAR</a>'),
                //         $('<td>').text(item.browserengine),
                //         $('<td>').html(deviceUA),
                //         $('<td>').text(item.datetime),
                //         $('<td>').text(item.notes),
                //         // $('<td>').text(item.imgname),
                //         // $('<td>').text(item.toastedwebname),
                //     ); 
            }
            $('#toastedtab').dataTable().fnAddData( [
                '<a class="history" href="' + item.imgname + '" target="_blank">view screenshot</a>',
                '<a href="'+item.toastedwebname+'" target="_blank">'+item.url+"</a>",
                pt,
                '<a href="'+harfile+'" download>Download HAR</a>',
                item.browserengine,
                deviceUA,
                item.datetime,
                item.notes
                 ]
  )
         //   $tr.appendTo('#toastedtab tbody');
        } // end for loop for each line in the file

        } // success
    }); // end ajax to get toasted data

});
</script>
</div>
</body>
</html>
