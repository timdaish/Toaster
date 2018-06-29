<?php

    $os_cmd = '"c:\Program Files (x86)\ImageMagick\convert" ' . 'c:\temp\q.jpg' . ' '. 'c:\temp\q.png' ;

    $res = array();
	exec($os_cmd,$res);
	echo implode($res);
	?>