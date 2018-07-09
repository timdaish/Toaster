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
ini_set("auto_detect_line_endings", false);
if($OS == "Windows")
$fn = "/toast/toasted.csv";
else
    $fn = '/usr/share/toast/toasted.csv';
$arrToasted = array ();

function array_sort($array, $on, $order=SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

$f = fopen($fn, "r");
//echo "fopen result: ".$f." for $fn<br/>";
if($f == false)
   die("can't open file");
$runnotes = '';

while (($line = fgetcsv($f)) !== false) {
                //echo implode($line)."<br/>";
                $ua = '';
                $pagetitle = '';
                $harfile = '';
                $screenshot = '';
        foreach ($line as $key => $cell) {
				switch ($key)
				{
				case 0: // page URL
					$page = html_entity_decode($cell);
					break;
				case 1:
					$link = $cell;
                    $chk = substr($link,1,1);
                    if($chk==':')
                    {
    					//$link = substr($link,3);
    					$link = str_replace('/\/', '/', $link);
    					$link = "/".$link;
                    }

					break;
				case 2:
					$dt = htmlspecialchars($cell);
					break;
                case 3:
					$ua = htmlspecialchars($cell);
                    //echo $ua.'<br/>';
					break;
                case 4:
					$pagetitle = html_entity_decode($cell);
                    //echo $pagetitle.'<br/>';
					break;
                case 5:
					$harfile = htmlspecialchars($cell);
                    //echo $harfile.'<br/>';
					break;
                case 6:
					$screenshot = $cell;
//echo("before: " . $screenshot."<br/>");
                    $thumbnail = str_replace( "png", "gif",$screenshot);
//echo("after: " . $screenshot."<br/>");

                    if(file_exists($thumbnail) == false)
                    {
                      // create a thumbnail
                        $file = str_replace("\\\\", "\\",$screenshot);
        //                echo("file: " . $file."<br/>");
        //                echo("path: " . dirname($thumbnail)."<br/>");

                        if($OS == 'Windows')
                            $os_cmd = 'c:\ImageMagick\mogrify -format gif -path ' . dirname($thumbnail) . ' -thumbnail 100x100 ' . escapeshellarg($file);
                        else
                            $os_cmd = 'mogrify -format gif -path ' . dirname($thumbnail) . ' -thumbnail 100x100 ' . escapeshellarg($file);
                        $res = array();
                    	exec($os_cmd,$res);
                    }
					break;
                case 7:
					$runnotes = htmlspecialchars($cell);
                    break;
//echo $runnotes.'<br/>';
				}

        } // end for

		// add to array
		$arr = array(
			"link" => $link,
			"page" => $page,
			"date" => $dt,
            "ua" =>$ua,
            "pagetitle" =>$pagetitle,
            "harfile" =>$harfile,
            "screenshot" =>$thumbnail,
            "notes" =>$runnotes
			);
			
		$found = false;	
		$c = count($arrToasted);
		for($i=0; $i<$c; $i++)
		{
			$line = $arrToasted[$i];
			//print_r($line);

			$alink = $line["link"];
			$apage = $line["page"];
			$adt = $line["date"];
            $aua = $line["ua"];
            $apagetitle = $line["pagetitle"];
            $ahar = $line["harfile"];
            $ss = $line["screenshot"];
            $arunnotes = $line["notes"];


			if($alink == $link and $aua == $ua and $ahar == $harfile)
			{
				$found = true;
				//echo("found<br/>");
				break;
			}
		}
		if($found  == false)
		{
		 // add
			$arrToasted[] = $arr;
			//echo($link." adding<br/>");
		}
		else // update
		{
			$arrToasted[$i]["date"] = $dt;
            $arrToasted[$i]["notes"] = $runnotes;
			//echo($alink." updating $dt<br/>");
		}
}
fclose($f);


$arrToastedByDate = array ();

$arrToastedByDate = array_sort($arrToasted, 'date', SORT_ASC); // Sort by newest first
//echo("<pre>");
//print_r($arrToasted); // Sort by newest first
//echo("</pre>");

// renumber the keys
$arrToastedByDate = array_values($arrToastedByDate);



echo "<table id=\"toasted\" class=\"dataTable table-striped history\">";
echo "<thead><th><span class=\"glyphicon glyphicon-picture\">&nbsp;</span></th><th><span class=\"glyphicon glyphicon-link\">&nbsp;</span>Page URL</th><th><span class=\"glyphicon glyphicon-header\">&nbsp;</span>Page Title</th><th><span class=\"glyphicon glyphicon-upload\">&nbsp;</span>HAR File</th><th><span class=\"glyphicon glyphicon-phone\">&nbsp;</span>Device</th><th><span class=\"glyphicon glyphicon-time\">&nbsp;</span>Toasted at</th><th>ssurl</th><th>tsturl</th><th>Notes</th></thead>";
echo "<tbody>";
$c = count($arrToastedByDate);
$line = array();
for($i=$c-1; $i>=0; $i--)
{
	$line = $arrToastedByDate[$i];
	//print_r($line);
		 
		$link = $line["link"];
		$page = urldecode($line["page"]);
		$dt = $line["date"];
        $ua = $line["ua"];
        $pagetitle = urldecode($line["pagetitle"]);
        $harfile = $line["harfile"];
        $ss = $line["screenshot"];
        $notes = $line["notes"];

		$l = str_replace('\\','/',$link);

		//echo("Link:".$l."<br/>");
		//echo("page:".$page."<br/>");
		echo ("<tr><td><a class=\"history\" href=\"". ''. "\" target=\"_blank\"><img src=\"".$ss."\" height=100 width=100 class=\"thumbnail\" \"></img></a></td>");
        echo "<td><a class=\"history\" href=\"". $l. "\" target=\"_blank\">" . $page . "</a></td>";
        echo "<td>". $pagetitle. "</td>";
        echo "<td>". $harfile. "</td>";


        $deviceUA = '';
        if(strpos($ua,'Desktop') !=false)
        {
          switch(trim($ua))
          {
              case "Chrome Desktop":
                  $deviceUA = '<img src="/toaster/images/chrome.png"></img><img src="/toaster/images/desktop.png"></img><br/>'.$ua;
                  break;
              case "Firefox Desktop":
                  $deviceUA = '<img src="/toaster/images/mozilla_firefox.png"></img><img src="/toaster/images/desktop.png"></img><br/>'.$ua;
                  break;
              case "IE Desktop":
                  $deviceUA = '<img src="/toaster/images/internet_explorer.png"></img><img src="/toaster/images/desktop.png"></img><br/>'.$ua;
                  break;
              default:
                  $deviceUA = $ua;
                  break;
          }
        }
        else
        {
          if(strpos($ua,'iOS') !==false)
          {
            if(strpos($ua,'iPad') !==false)
              $deviceUA = '<img src="/toaster/images/safari.png"></img><img src="/toaster/images/ipad.png"></img><br/>'.$ua;
            else
              $deviceUA = '<img src="/toaster/images/safari.png"></img><img src="/toaster/images/iphone.png"></img><br/>'.$ua;
          }

          if(strpos($ua,'Android') !==false)
          {
            if(strpos($ua,'M') !==false)
              $deviceUA = '<img src="/toaster/images/chrome.png"></img><img src="/toaster/images/android_mobile.png"></img><br/>'.$ua;
            else
              $deviceUA = '<img src="/toaster/images/chrome.png"></img><img src="/toaster/images/android_tablet.png"></img><br/>'.$ua;
          }

          if(strpos($ua,'Googlebot') !==false)
              $deviceUA = '<img src="/toaster/images/googlebot.png"></img><br/>'.$ua;
        }

        echo "<td class=\"device\">". $deviceUA."</td>";
        echo "<td>". $dt. "</td>";
        echo "<td>". $ss."</td>";
        echo "<td>". $l."</td>";
        echo "<td>". $notes."</td>";
		echo "</tr>";

} // end while

echo "</tbody></table>";

?>
<div style="clear: both;"></div>
<button id="rescore" style="color: black;">Rescore</button>
<script type="text/javascript" charset="utf8" src="/toaster/js/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
<!-- DataTables -->
<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
 $(document).ready(function() {
    var table = $('#toasted').DataTable(
     {
        "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "iDisplayLength": 10,
        "processing": true,
        "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
            /* Append the imurl to the col0 */
           var imgsrc = aData[6]; // getting the value of the first (invisible) column
           var hrefloc = aData[7];
           //console.log("img src = " +imgsrc);

            $('td:eq(0)', nRow).html( '<a class="history" href="' + hrefloc + '" target="_blank"><img src="' + imgsrc + '" height=100 width=100 class="thumbnail"></img></a>' );
        },
        "aaSorting": [[5, 'desc']], // sorts by a column and direction as set by the table type
         "columnDefs": [
            {
                "targets": [ 6,7 ],
                "visible": false,
                "searchable": false
            }
        ]
    });

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
    console.log( "reloading " + ": " +  window.location.hostname + "/" + svalue );

        var win=window.open(  "/" + svalue, '_blank');
        win.focus();
        win.addEventListener('load', function() { win.close(); } , false);
    }
});

    });



} );
</script>
</div>
</body>
</html>