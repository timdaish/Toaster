<?php


                // set port
                $port = "9221";
				// launch headless chrome on a given port
				$res = exec("google-chrome --remote-debugging-port=" . $port . " --headless > /dev/null 2>&1 & echo $!", $output);
                // capture pid
				$pid = (int)$output[0];
				echo "Chrome process id = " . $pid . "<br/>";
				print_r($output);

                //get har
                $harname = '/usr/share/toast/test.har';
                $height = 800;
                $width = 1200;
                $urlforbrowserengine = 'http://www.trashy.com';
                $imgname = '/usr/share/toast/desktop.png';
                $uar = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36";

				echo "generating HAR file to " . $harname . "<br/>";
				exec("node node_modules/chrome-har-capturer/bin/cli.js " . $urlforbrowserengine . " --port " . $port . " --output " . $harname . " 2>&1", $output2, $rv);
				echo implode("\n", $output2);
				echo "rv = " . $rv. "<br/>";

				// // get output
				// $outpath = $browserengineoutput;
				// echo "dumping HTML after page load to " . $outpath . "<br/>";
				// exec('google-chrome-stable --remote-debugging-port=9222 --headless --dump-dom ' . $urlforbrowserengine . " --pathname " . $outpath . " 2>&1", $output2, $rv); //responses & sniff
				// echo implode("\n", $output2);
				// echo "rv = " . $rv. "<br/>";

                sleep (1);
				// get screenshot
				echo "saving screenshot to ". $imgname  . "<br/>";
				exec('node node_modules/cri-toaster.js --url ' . $urlforbrowserengine . " --fullPage true --width " . $width . " --height " . $height . " --imgpath " . $imgname . " --port " . $port . " 2>&1", $output, $rv);
				echo implode("\n", $output);
				echo $imgname.  " - rv = " . $rv . "<br/>";

                sleep (1);
				// // kill chrome headless
				$command = 'kill -9 ' . $pid ;
				$res = exec($command . " 2>&1", $output);
                print_r($output);
                


                echo PHP_EOL . " all done";


?>
