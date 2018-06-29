<?php
$serverName = 'http://'.$_SERVER['SERVER_NAME'];
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
  $windows = defined('PHP_WINDOWS_VERSION_MAJOR');
    //echo 'This is a server using Windows! '. $windows."<br/>";
    $OS = "Windows";
}
else {
    //echo 'This is a server not using Windows!'."<br/>";
    $OS = PHP_OS;
}
session_write_close();
date_default_timezone_set('UTC');
set_time_limit(0);
ini_set("auto_detect_line_endings", true);
ini_set('display_errors', 0); // change to 1 for displaying errors on main scree // 0 to disable
error_reporting(E_ALL | E_STRICT);
ini_set('exif.encode_unicode', 'UTF-8');

echo "current working directory = " . getcwd() . "<br/>";

//$voutput = exec("node -v");
//echo $voutput;

$url = "http://www.bbc.co.uk";

     //echo exec('whoami');

				// use psexec to start in background, pipe stderr to stdout to capture pid
				$command = '"c:\program files (x86)\google\chrome\application\chrome.exe" --headless --disable-gpu --enable-logging --remote-debugging-port=9222';
				exec("toaster_tools\pstools\PsExec -d $command 2>&1", $output);
				// capture pid on the 6th line
				preg_match('/ID (\d+)/', $output[5], $matches);
				$pid = $matches[1];

				// launch chrome headless
				//exec('start chrome --headless --disable-gpu --enable-logging --remote-debugging-port=9222',$output,$rv);

echo "Google Chrome launched with PID "  . $pid . "<br/>";
				// get screenshot
				//echo "getting screenshot<br/>";
//				exec("node toaster_tools/chromeremote/take_screenshot.js --url " . $urlforbrowserengine . " --pathname " . $imgname . " --viewportHeight " . $height . " --viewportWidth " . $width. " 2>&1", $output, $rv);
				//echo implode("\n", $output);
				//echo $imgname.  " - rv = " . $rv . "<br/>";


				// get har
				//echo "generating HAR file to " . $harname . "<br/>";
//				exec("node toaster_tools/chromeremote/node_modules/chrome-har-capturer/bin/cli.js " . $urlforbrowserengine . " --output " . $harname . " --height " . $height . " --width " . $width . " --agent \"" . $uar . "\" 2>&1", $output2, $rv);
				//echo implode("\n", $output2);
				//echo "rv = " . $rv. "<br/>";


				// get HTML DOM, after age end with injections
				//echo "dumping HTML after page load to " . $browserengineoutput. "<br/>";
//				exec("node toaster_tools/chromeremote/dump.js --url " . $urlforbrowserengine. " --pathname tmp/" . $browserengineoutput. " 2>&1", $output2, $rv);
				//echo implode("\n", $output2);
				//echo "rv = " . $rv. "<br/>";

				// get testresults as HAR
				$uploadedHARFileName = $harname;
				$wptHAR = false;
				$uploadedHAR = true;

				// kill remote chrome headless instance
				//exec("toaster_tools\pstools\PsKill -t $pid", $output);

?>