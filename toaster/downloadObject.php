<?php
$plainsvgid = '';
$plainsvgelement = 'a';
function downloadObject($key,$valuearray)
{
	global $url,$filepath_domainsavedir,$filepath_domainsaverootdir,$filepath_basesavedir,$ua,$host_domain,$arrayOfObjects,$bool_b64,$sourceurlparts,$body,$objcount,$objcountimg,
     $arrayListOfImages,$arrayPageObjects,$debug,$boolHTTPCompressRoot,$compressionlevel,$totfilesize,$embeddedfile_count,$embeddedcount,$objcountcss,$objcountscript,$objcountimg,
     $totbytesdownloaded,$noof404Errors,$pagespeedcount,$objcountfont,$plainsvgid,$plainsvgelement,$OS,$perlbasedir,$basescheme,
     $amplience_dynamic_images_found,$amplience_dynamic_images_strip,$amplience_dynamic_images_stripnone,$amplience_dynamic_images_chroma,$loadContentFromHAR;

		$dlError = '';
		$bool_b64 = false;
        $sc = '';
        $mimetype = '';
        $fontname = '';
        $objtype = '';
        $docwrite_count = 0;

		$value = $valuearray["Object source"];
		$local = $valuearray["Object file"];
        $domain = $valuearray["Domain"];
		$lfn = $local;
		if ($value == '')
		{
			//echo ("DL blank value: key $key<br/>");
			//continue;
		}

		$nooffiles = count($arrayPageObjects);

		$rawurl = $value;
        $finalurl = $rawurl; // will get updated via redirection
		$pageobjectno = $key + 1; // starts at zero
    	debug("<br/>Downloading: $pageobjectno of $nooffiles;  file",$value);


        // begin downloading a file
		$filepathtosaveobject = '';
		//find url in array get id
		$found = false;
		list($id,$lfn) = lookupPageObject($value);
		//echo "object lookup: id = $id, local = $lfn<br/>";
		if(is_numeric($id))
		{
			$found = true;
			$foundkey = $key;
			$id = intval($key);
			//echo ("lookup local filename $id: $lfn<br/>");

			$filenameofsaveobject = $lfn;
			$filepathtosaveobject = dirname($lfn);

//echo ("dirpath from lookup file:'". $filepathtosaveobject."'<br/>");
		}
		else
		{
			return;

		}




//////////////////////////////////////////////

// check for a combo file comprising several files
		$bool_combo = false;
		if(strpos($value,"combo")> 0)
		{
			//echo ("Combo file found..<br/>");
			$bool_combo = true;
			
			$combofiles = explode("&",$value);
			//var_dump($combofiles);
			//echo ("<br/>"."noof files in combo: ".count($combofiles)."<br/>");
		}
		

			$combocount = 0;
			$ext_parts = pathinfo($lfn);
			if (isset($ext_parts['extension']))
			{
				$ext =".".$ext_parts['extension'] ;
				debug("parts extension",$ext);
			}
			else
			{
				$ext = "";
				if ($bool_combo === true)
				{
					// get extension of combo file
					$combocount = count($combofiles);
					$fcs = $combofiles[$combocount-1];
					$ext_parts = pathinfo($fcs);
					$ext = ".".$ext_parts['extension'];
				}
			}


		
		$sourcefile = $rawurl;
		$embeddedfile = $rawurl;
		$qspos = strpos($rawurl,'?');
        $mt = '';
        $pos_comma = 0;
		if ($qspos > 0)
			$checkstr = strtolower(substr($rawurl,0,$qspos));
		else
			$checkstr = $rawurl;
		
		// check for embeeded file
		if(strpos($checkstr,"data:") !== false)
		{
//echo "checking filepath for data uri: ".$embeddedfile."<br/>";
			//error_log("checking filepath for data uri: ".$embeddedfile);
			$pos_di = strpos($value,"data:");
			$pos_b64 = strpos($embeddedfile,";base64,");
            $pos_asc = strpos($embeddedfile,";charset=US-ASCII,");

            if($pos_b64 !== false)
            {
//echo ("embedded file is Base64<br/>");
                $embeddedfile_semicolon_end = strpos($embeddedfile,";");
    			$pos_comma = strpos($embeddedfile,",");

       			$embeddedfile_semicolon_end = strpos($embeddedfile,";");
    			$pos_comma = strpos($embeddedfile,",");

    			$l = $embeddedfile_semicolon_end - $pos_di;
    			$mt = substr($value,5,$pos_comma-5);
    			$possemicolon = strpos($mt,";");
    			if($possemicolon > 0)
    			{
    				$mt = substr($mt,0,$possemicolon);
    			}
            }
            else
                if($pos_asc !== false)
                {
//echo ("embedded file is ASCII text, not Base64<br/>");
                    $embeddedfile_semicolon_end = strpos($embeddedfile,";");
        			$pos_comma = strpos($embeddedfile,",");

        			$l = $embeddedfile_semicolon_end - $pos_di;
        			$mt = substr($value,5,$embeddedfile_semicolon_end);
        			$possemicolon = strpos($mt,";");
        			if($possemicolon > 0)
        			{
        				$mt = substr($mt,0,$possemicolon);
        			}
                } // this is an ascii embedded file

//echo ("embedded file contenttype full ". $mt ."<br/>");

			$posslash = strpos($mt,"/");
			$embeddedfile_basetype = substr($mt,0,$posslash);
			$embeddedfile_type = substr($mt,$posslash + 1);
//echo ("embedded file basetype: " . $embeddedfile_basetype . " - subtype: " . $embeddedfile_type . "<br/>");
//echo ("embedded file mimetype ". $mt ."<br/>");
			$mime = $mt;
            $mimetype = $mt;
            $fontname = '';

			switch($embeddedfile_type)
			{
				case 'svg+xml':
					$embeddedfile_ext = 'svg';
					$embeddedfile_objtype = 'Image';

                    //may be a font
					break;
					
				case 'x-font-woff':
                case 'font-woff':
					$embeddedfile_ext = 'woff';
					$embeddedfile_objtype = 'Font';
					break;
              	case 'woff2':
					$embeddedfile_ext = 'woff2';
					$embeddedfile_objtype = 'Font';
					break;

                case "x-font-ttf":
        		case "x-font-truetype":
                case "truetype":
                	$embeddedfile_ext = 'ttf'; // maybe ttf
					$embeddedfile_objtype = 'Font';

                    break;

				case 'opentype':
					$embeddedfile_ext = 'otf';
					$embeddedfile_objtype = 'Font';
					break;

				default:
					$embeddedfile_ext = $embeddedfile_type;
					$embeddedfile_objtype = ucfirst($embeddedfile_basetype);
			}

			$b64file = substr($embeddedfile,$pos_comma+1);

//echo ("<br/>Embedded File ext: ".$embeddedfile_ext."<br/>");
//echo ("<br/>Embedded File: ".$b64file."<br/>");
	

			// embedded file  - no downloading required
            // may be base64 encoded
            // may be text

			// replace save file path for encoded image
			$filepathtosaveobject = joinFilePaths($filepath_domainsavedir."base64");
			debug("Embedded File save path", $filepathtosaveobject);
//echo("Embedded File image save path: ". $filepathtosaveobject."<br/>");

		}



		if ($filepathtosaveobject == '' or $filepathtosaveobject == '.')
		{
		  //	echo("directory error".$filepathtosaveobject."<br/>");
		  //	break;
		}
		else
		{
			// create a file path for the directory
			$p = urldecode($filepathtosaveobject);
			createDomainSavePath($p);
		
		}
		$resname = is_valid_filename($filenameofsaveobject);


		if ($resname == false or $filenameofsaveobject == '')
		{
			debug("Filename error",$filenameofsaveobject);
		}
		else
		{

			// remove query parameter
			$path=explode("?",$filenameofsaveobject);
			$filenameofsaveobject=basename($path[0]);


			$qspos = strpos($rawurl,'?');
			if ($qspos > 0)
				$checkstr = strtolower(substr($rawurl,0,$qspos));
			else
				$checkstr = $rawurl;

			
			//$query=$path[1];
			//echo "DL: checking for encoded file: ".$checkstr."<br/>";
			// check for Base64 on base url, not querystring
			if(strpos($checkstr,"data:") !== false)
			{
				//decode base64 image and save
//echo "<br/>Extracting Embedded File from data: ".$embeddedfile."<br/>";
//echo "Extracting data rawurl: ".$rawurl."<br/>";

				// base 64 image - no downloading required
				debug("DL Embedded file found", $embeddedfile);
				$embeddedcount = $embeddedcount + 1;

	
				//find url in array get id
				$found = false;
				//list($id,$lfn) = lookupPageObject($rawurl);
				if(is_numeric($id))
				{
					$found = true;
					$foundkey = $id;
					$b64key = $id;
					//echo "B64 object lookup: id = $id, local = $lfn<br/>";
				}
				else
				{
					//echo "B64 object lookup failed: id = $id, local = $lfn<br/>";
				}
				
				$pos_di = strpos($embeddedfile,"data:");
				$pos_b64 = strpos($embeddedfile,";base64,");
				$embeddedfile_semicolon_end = strpos($embeddedfile,";");
				$pos_comma = strpos($embeddedfile,",");


				//echo ("Base 64 decoding pos_di: ".$pos_di."<br/>");
				//echo ("Base 64 decoding pos_b64: ".$pos_b64."<br/>");
				//echo ("Base 64 decoding pos_b64e: ".$embeddedfile_semicolon_end."<br/>");
				//echo ("Base 64 decoding pos_comma: ".$pos_comma."<br/>");


				$embeddedfile_ext = substr($embeddedfile,$pos_di+10,$embeddedfile_semicolon_end -($pos_di+10));
				$b64file = substr($embeddedfile,$pos_comma+1);
				//echo ("embedded file decoding ext: ".$embeddedfile_ext."<br/>");
				if ($pos_b64 === false)
				{
//echo ("embedded file decoding - NOT Base64 <br/>");
					$sp = strpos($embeddedfile,"data:") + 5;
					$posslash = strpos($embeddedfile,"/",$sp);
                    $possemicolon = strpos($embeddedfile,";",$sp);

					$embeddedfile_basetype = substr($embeddedfile,$sp,$posslash - $sp);
					$poscomma = strpos($embeddedfile,",");
					$embeddedfile_type = substr($embeddedfile,$posslash + 1, $possemicolon - $posslash - 1);
//echo "embedded basetype/subtype: " . $embeddedfile_basetype . " - " . $embeddedfile_type . "<br/>";
                    $mimetype = $embeddedfile_basetype . "/" .$embeddedfile_type;
					//echo ("<br/>embedded file (not base64): ".urldecode($b64file)."<br/>");

                    $decoded = substr($embeddedfile,$pos_comma+1);
                    // no decoding
					$embeddedfile_textlen = strlen($b64file);
					$embeddedfile_ext = $embeddedfile_type;
					if($embeddedfile_ext == 'svg+xml')
						$embeddedfile_ext = 'svg';

					$filepathnameofsaveobject = urldecode(joinFilePaths($filepathtosaveobject,"embeddedfile_".$b64key.".".$embeddedfile_ext));
					$filepathtosaveobject = joinFilePaths($filepath_domainsavedir."base64");
					//$filepathnameofsaveobject = urldecode($filepathtosaveobject);
					debug("SAVING embedded Data as FILE PATHNAME", $filepathnameofsaveobject);
				}
				else // this is a base64 encoded file
				{
//echo ("embedded file decoding - Base64 <br/>");
					$sp = strpos($embeddedfile,"data:") + 5;
					$posslash = strpos($embeddedfile,"/",$sp);
					$embeddedfile_ext = substr($embeddedfile,$posslash + 1);
					$possc = strpos($embeddedfile_ext,";");
					$embeddedfile_ext = substr($embeddedfile_ext,0,$possc);
                    $embeddedfile_type = substr($embeddedfile,$posslash + 1, $pos_b64 - $posslash - 1);
					//echo "b64 embedded basetype/subtype: " . $embeddedfile_basetype . " - " . $embeddedfile_type . "; ext = ". $embeddedfile_ext."<br/>";
                    $mimetype = $embeddedfile_basetype . "/" .$embeddedfile_type;
					$b64file = substr($embeddedfile,$pos_comma+1);

                    debug("SAVING B64 as FILE with ext: ", $embeddedfile_ext);
					//echo ("<br/>embedded Base64 file: ".base64_decode($b64file)."<br/>");
					//error_log("embedded Base64 file: ".base64_decode($b64file));
					
					if($embeddedfile_ext == 'svg+xml')
						$embeddedfile_ext = 'svg';
					if($embeddedfile_ext == 'truetype')
						$embeddedfile_ext = 'ttf';
                    if($embeddedfile_ext == 'opentype')
						$embeddedfile_ext = 'otf';
					if($embeddedfile_ext == 'jpeg')
						$embeddedfile_ext = 'jpeg';



					$filepathnameofsaveobject = urldecode(joinFilePaths($filepathtosaveobject,"base64file_".$b64key.".".$embeddedfile_ext));
					$filepathtosaveobject = joinFilePaths($filepath_domainsavedir."base64");
					//$filepathnameofsaveobject = urldecode($filepathtosaveobject);
					debug("SAVING B64 as FILE PATHNAME", $filepathnameofsaveobject);
					//echo("SAVING B64 as FILE PATHNAME: ". $filepathnameofsaveobject."<br/>");
					//error_log("SAVING B64 as FILE PATHNAME: ". $filepathnameofsaveobject."<br/>");

					// replace REQUIRED
					$b64file = str_replace(' ', '+', $b64file);
					$embeddedfile_textlen = strlen($b64file);
					if ($boolHTTPCompressRoot == true)
					{
						$gzdata = gzencode($b64file, $compressionlevel);
						$embeddedfile_textlen = strlen($gzdata);
					}
					
					$decoded = base64_decode($b64file,true);

				}
				//echo("B64 file decoded: ".$filepathnameofsaveobject."<br/>");
				//echo("B64 decoded: ".$decoded."<br/>");
				$rc = file_put_contents($filepathnameofsaveobject, $decoded);
				
				//echo("B64 result code from saving = bytes =".$rc."<br/>");

				$embeddedfile_count = $embeddedfile_count + 1;
				if($rc == false)
				{

					addErrors($filepathnameofsaveobject,"Invalid inline Base64 file");
					
				}
				else
				{
					$body = $decoded; // needed for decoding
					// save as binary file
					//$fp = fopen($filepathnameofsaveobject, 'w');
					//$rc = fwrite($fp, base64_decode($b64file));
					//fclose($fp);

					$fsize = filesize($filepathnameofsaveobject);
					$get    = getimagesize($filepathnameofsaveobject);

					switch($embeddedfile_basetype)
					{

						case 'image':

							$width  = $get[0];
							$height = $get[1];
							$type   = $get[2];
							$attr   = $get[3];
                            if(isset($get['bits']))
    							$bits   = $get['bits'];
                            else
                                $bits   = '';
							$mime   = $get['mime'];
//echo($mimetype." mimetype from get of image data: ". $mime."<br/>");
                            if($mimetype == '' and $mime != '' )
                                $mimetype  = $mime;
                            else
                                $mime = $mimetype;

							if (is_numeric($width) == true)
								$actualsize = $width." x ".$height;
							else
								$actualsize = $width."px x ".$height." px";

							// update file stats
							$arrayOfObjects[$key]['Object name'] = "Embedded:".substr($b64file,0,100);
							$arrayOfObjects[$key]['Object file'] = $filepathnameofsaveobject;
							$arrayOfObjects[$key]['Object type'] = "Image";
							$arrayOfObjects[$key]['Content size'] = $fsize;
							$arrayOfObjects[$key]['HTTP Status'] = "Embedded";
							$arrayOfObjects[$key]['GZIP Status'] =	"-";
							$arrayOfObjects[$key]['Mime Type'] = $mimetype;
							$arrayOfObjects[$key]['Extension'] = $embeddedfile_ext;
							$arrayOfObjects[$key]['Combined files'] = $combocount;

							$imagetype = '';
							$imageencoding = '';
							$exifbytes = 0;
							$iptcbytes = 0;
							$xmpbytes = 0;
							$xmpdata = '';
							$comment = '';
							$commentbytes = 0;
							$metadatabytes = 0;
							$density = '';
							$interlace = '';
							$colourtype = 'Indexed';
							$animation = '';
							$colordepth = $bits . " bit";
							$commenttext = '';
							$iccbytes = 0;
							$duckyquality = 0;
							$savequality = '';
							$estquality = '';
							$commentquality = 0;
							$app12bytes = 0;
							$chromasubsampling = '';
                            $fontname = '';
			                $structure = '';
							$exif = '';
							$iptc = '';
				
							//echo ("<br/>BASE64 data: $decoded<br/>");
							//decode image, loading the file first
							getBytesFromFile($filepathnameofsaveobject,0,12);
							switch($mime)
							{
								case "image/jpeg":
                                case "image/jpg":
							
									//echo ("<br/>Decoding JPEG: BASE64: $filepathnameofsaveobject<br/>");
									
									list($imagetype,$imageencoding,$exifbytes,$iptcbytes,$xmpbytes,$xmpdata,$commenttext,$commentbytes,$metadatabytes,$density,$iccbytes,$structure,$duckyquality,$app12bytes,$duckytext,$commentquality,$chromasubsampling) = decodeJPEG($body,$filepathnameofsaveobject);
									//echo("<br/>".$app12bytes . " - ".$duckytext."<br/>");
									//echo ("<br/>BASE64: $imagetype<br/>");
									
									if(strpos($imageencoding,"Progressive") !== false)
										$interlace = 'Progressive';
									else
										$interlace = 'Non-Interlaced';
										$colourdepth = "24 bit";
										$colourtype = "True Colour";
									break;

									case 'image/png':
										list($imagetype,$imageencoding,$structure,$width,$height,$colordepth,$colourtype,$filtermethod,$interlace,$metadatabytes,$commentbytes,$commenttext,$xmpbytes,$iccbytes) = decodePNG($body,$filepathnameofsaveobject);
										//echo("Base64 file: PNG structure:<br/>".$structure);
										//echo("Base64 file: PNG: comments<br/>".$commenttext);
										break;

									case 'image/gif':
										list($imagetype,$imageencoding,$width,$height,$interlace,$colordepthGIF,$metadatabytes,$commentbytes,$commenttext,$structure,$animation,$gifxmp,$xmpbytes) = decodeGIF($body,$filepathnameofsaveobject);
                                        $colourtype = 'Indexed';
										break;

                                    case 'image/webp':
                                        list($imagetype,$imageencoding,$canvassize,$metadatabytes,$iccbytes,$xmpbytes) = decodeWEBP($body,$lfn);
                                        break;

									case 'image/svg+xml':
										$embeddedfile_ext = '.svg';
										$imagetype = 'SVG';
										$colordepth = '';

                                        $docSVG = new DOMDocument();
										@$docSVG->load(realpath($filepathnameofsaveobject));
										$plainsvgelement = 'b';
                                        setAllId($docSVG);
                                        switch($plainsvgelement)
                                        {
                                            case 'font':
                                              $objtype = "Font";
                                              $boolTextType = true;
                                              $objcountfont +=1;

                                              break;

											default:
											$objtype = "Image";
                                                break;
										}
//echo "image/svg+xml 4";
//echo($lfn . " 1: force setting SVG object type = " . $objtype . "<br/>");
										break;


									default:
									//echo("error decoding Base 64 file of type '". $mime . "' for $filepathnameofsaveobject<br/>");
									adderrors($filepathnameofsaveobject,"error decoding Base 64 file of type ".$mime);
									break;
							} // END SWITCH IMAGE MIME

							debug('Analysing embedded image comment data',$filepathnameofsaveobject);
							if($commentbytes > 0)
							{
								//echo($sourcefile.': Adding image comment data: '.$commenttext);
								addImageData($sourcefile,"Comments",$commenttext);
							}
							
							debug('Analysing embedded image structure',$filepathnameofsaveobject);
							//echo($sourcefile.': Adding image comment data: '.$structure);
							addImageData($sourcefile,"Structure",$structure);





							// add Base 64 image data
							$arr = array(
							"Object type" => 'Image',
							"Object source" => $value,
							"Object file" => $filepathnameofsaveobject,
							"Mime type" => $mime,
							"Domain ref" => 'Embedded',
							"HTTP status" => 'Embedded',
							"File extension" => $embeddedfile_ext,
							"CSS ref" => '',
							"Header size" => 0,
							"Content length transmitted" => $embeddedfile_textlen,
							"Content size downloaded" => 0,
							"Compression" => '',
							"Content size compressed" => '',
							"Content size uncompressed" => '',
							"Content size minified uncompressed" => '',
							"Content size minified compressed" => '',
							"Combined files" => 0,
							"JS defer" => '',
							"JS async" => '',
                            "JS docwrite" => '',
							"Image type" => $imagetype,
							"Image encoding" => $imageencoding,
                            "Image responsive" => '',
							"Image display size" => '',
							"Image actual size" => $actualsize,
							"Metadata bytes" => $metadatabytes,
							"EXIF bytes" => $exifbytes,
							"APP12 bytes" => $app12bytes,
							"IPTC bytes" => $iptcbytes,
							"XMP bytes" => $xmpbytes,
							"Comment" => $commenttext,
							"Comment bytes" => $commentbytes,
							"ICC colour profile bytes" => $iccbytes,
							"Colour type" => $colourtype,
							"Colour depth" => $colordepth . " bit",
							"Interlace" => $interlace,
							"Est. quality" => $estquality,
							"Photoshop quality" => $savequality,
							"Chroma subsampling" => $chromasubsampling,
							"Animation" => $animation,
                            "Font name" => $fontname,
							"hdrs_Server" => '',
							"hdrs_Protocol" => '',
							"hdrs_responsecode" => '',
							"hdrs_age" => '',
							"hdrs_date" => '',
							"hdrs_lastmodifieddate" => '',
							"hdrs_cachecontrol" => '',
							"hdrs_cachecontrolPrivate" => '',
							"hdrs_cachecontrolPublic" => '',
							"hdrs_cachecontrolMaxAge" => '',
							"hdrs_cachecontrolSMaxAge" => '',
							"hdrs_cachecontrolNoCache" => '',
							"hdrs_cachecontrolNoStore" => '',
							"hdrs_cachecontrolNoTransform" => '',
							"hdrs_cachecontrolMustRevalidate" => '',
							"hdrs_cachecontrolProxyRevalidate" => '',
							"hdrs_connection" => '',
							"hdrs_contentencoding" => '',
							"hdrs_contentlength" => '',
							"hdrs_expires" => '',
							"hdrs_etag" => '',
							"hdrs_keepalive" => '',
							"hdrs_pragma" => '',
							"hdrs_setcookie" => '',
							"hdrs_upgrade" => '',
							"hdrs_vary" => '',
							"hdrs_via" => '',
							"hdrs_xservedby" => '',
							"hdrs_xcache" => '',
							"hdrs_xpx" => '',
							"hdrs_xedgelocation" => '',
							"hdrs_cfray" => '',
							"hdrs_xcdngeo" => '',
                            "hdrs_xcdn" => '',
							"response_datetime" => '',
							);
							//echo("updating page object - B64 image<br/>");
							addUpdatePageObject($arr);

							// BASE 64 no headers

							//addPageHeaders($value,'');
							break;


						case 'application':
						
							switch($mime)
							{
								case "application/x-font-woff":
                                case "application/font-woff":
									$embeddedfile_objtype = 'Font';
									$embeddedfile_ext = 'woff';
                                    if(file_exists($filepathnameofsaveobject))
                                        {
                                            $fontinfo = getFontInfo($filepathnameofsaveobject);
                                                //echo("embedded font name = ".$fontinfo.'<br/>');
                                            $fontname =$fontinfo[4];
										
						
										}
										
										

									break;
                                case "font/woff2":
									$embeddedfile_objtype = 'Font';
									$embeddedfile_ext = 'woff2';
									break;

								case "application/x-font-ttf":
								case "application/x-font-truetype":
                                case "font/ttf":
									$objtype = "Font";
									$embeddedfile_ext = 'ttf';
                                         if(file_exists($filepathnameofsaveobject))
                                        {
                                            $fontinfo = getFontInfo($filepathnameofsaveobject);
                                                //echo("embedded font name = ".$fontinfo.'<br/>');
                                            $fontname =$fontinfo[4];
                                        }
									break;
								case "application/vnd.ms-fontobject":
									$objtype = "Font";
									$embeddedfile_ext = 'eot';
									break;
								case "application/x-font-opentype":
									$objtype = "Font";
									$embeddedfile_ext = 'otf';
                                    if(file_exists($filepathnameofsaveobject))
                                        {
                                            $fontinfo = getFontInfo($filepathnameofsaveobject);
                                                //echo("embedded font name = ".$fontinfo.'<br/>');
                                            $fontname =$fontinfo[4];
                                        }
									break;
								case "application/font-sfnt":
									$objtype = "Font";
									$embeddedfile_ext = 'eot';
									break;

                                case 'application/octet-stream':
                                    if( $embeddedfile_ext == 'woff' or $embeddedfile_ext == 'ttf' or $embeddedfile_ext == 'eof')
                                    {
										$objtype = "Font";
										

										
                                    }
                                    break;
		
							}
							
							// Base 64
							$arr = array(
							"Object type" => $embeddedfile_objtype,
							"Object source" => $value,
							"Object file" => $filepathnameofsaveobject,
							"Mime type" => $mime,
							"Domain ref" => 'Embedded',
							"HTTP status" => 'Embedded',
							"File extension" => $embeddedfile_ext,
							"CSS ref" => '',
							"Header size" => 0,
							"Content length transmitted" => $fsize,
							"Content size downloaded" => 0,
							"Compression" => '',
							"Content size compressed" => '',
							"Content size uncompressed" => '',
							"Content size minified uncompressed" => '',
							"Content size minified compressed" => '',
							"Combined files" => 0,
							"JS defer" => '',
							"JS async" => '',
                            "JS docwrite" => '',
							"Image type" => '',
                            "Image encoding" => '',
                            "Image responsive" => '',
							"Image display size" => '',
							"Image actual size" => '',
							"Metadata bytes" => '',
							"EXIF bytes" => '',
							"APP12 bytes" => '',
							"IPTC bytes" => '',
							"XMP bytes" => '',
							"Comment" => '',
							"Comment bytes" => '',
							"ICC colour profile bytes" => '',
							"Colour type" => '',
							"Colour depth" => '',
							"Interlace" => '',
							"hdrs_Server" => '',
							"hdrs_Protocol" => '',
							"hdrs_responsecode" => '',
							"hdrs_age" => '',
							"hdrs_date" => '',
							"hdrs_lastmodifieddate" => '',
							"hdrs_cachecontrol" => '',
							"hdrs_cachecontrolPrivate" => '',
							"hdrs_cachecontrolPublic" => '',
							"hdrs_cachecontrolMaxAge" => '',
							"hdrs_cachecontrolSMaxAge" => '',
							"hdrs_cachecontrolNoCache" => '',
							"hdrs_cachecontrolNoStore" => '',
							"hdrs_cachecontrolNoTransform" => '',
							"hdrs_cachecontrolMustRevalidate" => '',
							"hdrs_cachecontrolProxyRevalidate" => '',
							"hdrs_connection" => '',
							"hdrs_contentencoding" => '',
							"hdrs_contentlength" => '',
							"hdrs_expires" => '',
							"hdrs_etag" => '',
							"hdrs_keepalive" => '',
							"hdrs_pragma" => '',
							"hdrs_setcookie" => '',
							"hdrs_upgrade" => '',
							"hdrs_vary" => '',
							"hdrs_via" => '',
							"hdrs_xservedby" => '',
							"hdrs_xcache" => '',
							"hdrs_xpx" => '',
							"hdrs_xedgelocation" => '',
							"hdrs_cfray" => '',
							"hdrs_xcdngeo" => '',
                            "hdrs_xcdn" => '',
							"response_datetime" => '',
							);
							//echo("updating page object - B64 other<br/>");
							addUpdatePageObject($arr);
							break;

                        case "font":
                            $embeddedfile_objtype = "Font";
                            //echo("embedded font <br/>");
							switch($mime)
							{
									case 'font/opentype':
                                    case "application/x-font-opentype":
										$embeddedfile_ext = '.otf';
                                        //$objFontInfo = new FontInfo( $lfn );
                                        //print $objFontInfo->getFontName();
                                        //$fontname = $objFontInfo->getFontName();

                                        if(file_exists($filepathnameofsaveobject))
                                        {
                                            $fontinfo = getFontInfo($filepathnameofsaveobject);

                                            $fontname =$fontinfo[4];
                                        }
//echo($filepathnameofsaveobject." embedded font name = ".$fontname.'<br/>');
                                        break;

                       				case "application/x-font-ttf":
        							case "application/x-font-truetype":
                                    case "font/ttf":
                                    case "application/font-sfnt":
                                        $embeddedfile_ext = '.ttf';
                                        //$objFontInfo = new FontInfo( $lfn );
                                        //print __LINE__ . $objFontInfo->getFontName();
                                        //$fontname = $objFontInfo->getFontName();
                                         if(file_exists($filepathnameofsaveobject))
                                        {
                                            $fontinfo = getFontInfo($filepathnameofsaveobject);
                                                //echo("embedded font name = ".$fontinfo.'<br/>');
                                            $fontname =$fontinfo[4];
                                        }
                                        break;
                                }
					        // Base 64
							$arr = array(
							"Object type" => $embeddedfile_objtype,
							"Object source" => $value,
							"Object file" => $filepathnameofsaveobject,
							"Mime type" => $mime,
							"Domain ref" => 'Embedded',
							"HTTP status" => 'Embedded',
							"File extension" => $embeddedfile_ext,
							"CSS ref" => '',
							"Header size" => 0,
							"Content length transmitted" => $fsize,
							"Content size downloaded" => 0,
							"Compression" => '',
							"Content size compressed" => '',
							"Content size uncompressed" => '',
							"Content size minified uncompressed" => '',
							"Content size minified compressed" => '',
							"Combined files" => 0,
                            "Font name" => $fontname,
							"JS defer" => '',
							"JS async" => '',
                            "JS docwrite" => '',
							"Image type" => '',
                            "Image encoding" => '',
                            "Image responsive" => '',
							"Image display size" => '',
							"Image actual size" => '',
							"Metadata bytes" => '',
							"EXIF bytes" => '',
							"APP12 bytes" => '',
							"IPTC bytes" => '',
							"XMP bytes" => '',
							"Comment" => '',
							"Comment bytes" => '',
							"ICC colour profile bytes" => '',
							"Colour type" => '',
							"Colour depth" => '',
							"Interlace" => '',
							"hdrs_Server" => '',
							"hdrs_Protocol" => '',
							"hdrs_responsecode" => '',
							"hdrs_age" => '',
							"hdrs_date" => '',
							"hdrs_lastmodifieddate" => '',
							"hdrs_cachecontrol" => '',
							"hdrs_cachecontrolPrivate" => '',
							"hdrs_cachecontrolPublic" => '',
							"hdrs_cachecontrolMaxAge" => '',
							"hdrs_cachecontrolSMaxAge" => '',
							"hdrs_cachecontrolNoCache" => '',
							"hdrs_cachecontrolNoStore" => '',
							"hdrs_cachecontrolNoTransform" => '',
							"hdrs_cachecontrolMustRevalidate" => '',
							"hdrs_cachecontrolProxyRevalidate" => '',
							"hdrs_connection" => '',
							"hdrs_contentencoding" => '',
							"hdrs_contentlength" => '',
							"hdrs_expires" => '',
							"hdrs_etag" => '',
							"hdrs_keepalive" => '',
							"hdrs_pragma" => '',
							"hdrs_setcookie" => '',
							"hdrs_upgrade" => '',
							"hdrs_vary" => '',
							"hdrs_via" => '',
							"hdrs_xservedby" => '',
							"hdrs_xcache" => '',
							"hdrs_xpx" => '',
							"hdrs_xedgelocation" => '',
							"hdrs_cfray" => '',
							"hdrs_xcdngeo" => '',
                            "hdrs_xcdn" => '',
							"response_datetime" => '',
							);
							//echo("updating page object - B64 other<br/>");
							addUpdatePageObject($arr);
                        break;
						
						// other data types
						default:
							// Base 64
							$arr = array(
							"Object type" => $embeddedfile_objtype,
							"Object source" => $value,
							"Object file" => $filepathnameofsaveobject,
							"Mime type" => $mime,
							"Domain ref" => 'Embedded',
							"HTTP status" => 'Embedded',
							"File extension" => $embeddedfile_ext,
							"CSS ref" => '',
							"Header size" => 0,
							"Content length transmitted" => $fsize,
							"Content size downloaded" => 0,
							"Compression" => '',
							"Content size compressed" => '',
							"Content size uncompressed" => '',
							"Content size minified uncompressed" => '',
							"Content size minified compressed" => '',
							"Combined files" => 0,
							"JS defer" => '',
							"JS async" => '',
                            "JS docwrite" => '',
							"Image type" => '',
                            "Image encoding" => '',
                            "Image responsive" => '',
							"Image display size" => '',
							"Image actual size" => '',
							"Metadata bytes" => '',
							"EXIF bytes" => '',
							"APP12 bytes" => '',
							"IPTC bytes" => '',
							"XMP bytes" => '',
							"Comment" => '',
							"Comment bytes" => '',
							"ICC colour profile bytes" => '',
							"Colour type" => '',
							"Colour depth" => '',
							"Interlace" => '',
							"hdrs_Server" => '',
							"hdrs_Protocol" => '',
							"hdrs_responsecode" => '',
							"hdrs_age" => '',
							"hdrs_date" => '',
							"hdrs_lastmodifieddate" => '',
							"hdrs_cachecontrol" => '',
							"hdrs_cachecontrolPrivate" => '',
							"hdrs_cachecontrolPublic" => '',
							"hdrs_cachecontrolMaxAge" => '',
							"hdrs_cachecontrolSMaxAge" => '',
							"hdrs_cachecontrolNoCache" => '',
							"hdrs_cachecontrolNoStore" => '',
							"hdrs_cachecontrolNoTransform" => '',
							"hdrs_cachecontrolMustRevalidate" => '',
							"hdrs_cachecontrolProxyRevalidate" => '',
							"hdrs_connection" => '',
							"hdrs_contentencoding" => '',
							"hdrs_contentlength" => '',
							"hdrs_expires" => '',
							"hdrs_etag" => '',
							"hdrs_keepalive" => '',
							"hdrs_pragma" => '',
							"hdrs_setcookie" => '',
							"hdrs_upgrade" => '',
							"hdrs_vary" => '',
							"hdrs_via" => '',
							"hdrs_xservedby" => '',
							"hdrs_xcache" => '',
							"hdrs_xpx" => '',
							"hdrs_xedgelocation" => '',
							"hdrs_cfray" => '',
							"hdrs_xcdngeo" => '',
                            "hdrs_xcdn" => '',
							"response_datetime" => '',
							);
							//echo("updating page object - B64 other<br/>");
							addUpdatePageObject($arr);
						
						

					} // end switch




				}

			}
			else
			{

				if($loadContentFromHAR == true)
				{
//echo ("<br/><br/>DO: Extracting object from HAR: key: $key  objno: $pageobjectno;  file: ".$value.";<br/>local filename: $local<br/>");
				}
				else
				{
//$harecho("<br/><br/>Downloading object: key: $key  objno: $pageobjectno;  file: ".$value.";<br/>local filename: $local<br/>");
				}

					// a file to be downloaded
					debug("<br><br>DOWNLOADING FILE",$sourcefile);
					list($hd, $hp) = getDomainHostFromURL($sourcefile,true,"DownloadFile");

                    // check if file has already been downloaded, i.e. for a redirected file
                    $thisstatuscode = $arrayPageObjects[$key]['HTTP status'];
                    if($thisstatuscode != '')
                    {
//echo ("object " . $key . ": " . $sourcefile . " already downloaded with status code " . $thisstatuscode)."<br/>";
                      // file just needs to be analysed
                      $alreadydownloaded = true;
                      $sc = strval(intval($thisstatuscode));
                      $mimetype = $arrayPageObjects[$key]['Mime type'];
                      $contenttype = $mimetype;
                      $filepathnameofsaveobject = $arrayPageObjects[$key]['Object file'];
//echo ("object " . $key . ": saved as " . $filepathnameofsaveobject . "; status code= " . $sc. "<br/>");

                      // retrieve headers from array
                      //return true;

                    }
                    else
                        $alreadydownloaded = false;


                    //if($alreadydownloaded == false)
                    //{

    					// check sourcefile name
    					if(strtolower(substr($sourcefile,0,4)) != "http")
    					{
    						if(strtolower(substr($sourcefile,0,2)) == "//")
    						{
    							debug("prefixing ". $basescheme ,$sourcefile);
    							$sourcefile = $basescheme. $sourcefile;
    						}
    						else
    						{


    							$path_parts = pathinfo($value);

    							if(isset($path_parts['dirname']))
    							{
    								$path = trim($path_parts['dirname']);
    								$path = str_replace("..","up",$path);
    								debug("path_parts['dirname']",$path_parts['dirname']."-->".$path);
    							}
    							else
    								$path = '';
    							if ($path != '.' and $path !='')
    							{
    								if(substr($value,0,1) != "/")
    									$fsd = $filepath_domainsavedir;
    								else
    									$fsd = $filepath_domainsaverootdir;
    								$filepathtosaveobject = joinFilePaths($fsd,$path);
    							}
    							else
    							{
    								$filepathtosaveobject = $filepath_domainsavedir;
    							}

    						}
    						debug("new source filename",$sourcefile);

    					}


    					debug("Pre-CURL SAVING FILENAME AS", $filenameofsaveobject);
    					//$filenameofsaveobject = sanitize_file_name($filenameofsaveobject,false,false);


    					$filepathnameofsaveobject = $lfn;
    					//$filepathnameofsaveobject = urldecode($filepathtosaveobject);
    					debug("Pre-CURL SAVE FILEPATH", $filepathnameofsaveobject);
    //echo("SAVE FILE PATHNAME: ". $filepathnameofsaveobject . "<br/>");

    					// replace spaces to retrieve the file
                        debug("removing spaces in source filename: before","'" . $sourcefile . "'");
                        $sourcefileNoSpaces = trim($sourcefile); // remove spaces from start and end of url
    					$sourcefileNoSpaces = str_replace(" ","%20",$sourcefileNoSpaces); // encode spaces in the middle
                        debug("removing spaces in source filename: after","'" . $sourcefileNoSpaces . "'");
						
						if($loadContentFromHAR == true)
						{
							// get content from HAR file
//echo "download object - bypassing - reading from har file: " . $sourcefileNoSpaces . "<br/>";
							list($curl_info,$curlresponseheaders) = readFromHARandSaveToFilePath($value,$sourcefileNoSpaces,$filepathnameofsaveobject);
						}
						else
						{
							// download afresh
//echo "download object from server: " . $sourcefileNoSpaces . "<br/>";
							list($curl_info,$curlresponseheaders) = readURLandSaveToFilePath($sourcefileNoSpaces,$filepathnameofsaveobject);
							if(!$curl_info)
							{
								//echo("DO: download of file failed: ". $sourcefile."<br/>");
								return;
							}
						}

    					$url_page = getURLFromCURL(); // after following any	 redirections
    					//list($ttime,$rdtime,$contime,$dnstime,$dstime,$dsstime) = get_timings();

    					// NEW HEADER ANALYSIS FOR ADDING TO OBJECT TABLE
    					$TimeOfResponse = get_Datetime_Now();
    					//echo($sourcefile.": curl request time: " . $TimeOfResponse."<br/>");
    					if(count($curlresponseheaders) == 0)
    					{
    						addErrors($sourcefile,"no headers");
    						return;
						}
						// extractHeadersFromCurlResponse
    					list($protocol,$responsecode,$age,$cachecontrol,$cachecontrolPrivate,$cachecontrolPublic,$cachecontrolNoCache,$cachecontrolNoStore,$cachecontrolMaxAge,$cachecontrolSMaxAge,$cachecontrolNoTransform,$cachecontrolMustRevalidate,$cachecontrolProxyRevalidate,$connection,$contentencoding,$contentlength,$contenttype,$date,$etag,$expires,$keepalive,$lastmodifieddate,$pragma,$server,$setcookie,$upgrade,$vary,$via,$xcache,$xservedby,$xpx,$xedgelocation,$cfray,$xcdngeo,$xcdn) = extractHeadersFromCurlResponse($curlresponseheaders); //curlresponseheaders
//echo ("protocol and age: ".$protocol . " contenttype: " .$contenttype."<br/>");
    					$mimetype = strtolower(trim($contenttype));

    					//if($mimetype == '')
    						//echo("dl missing mimetype for : " . $lfn."<br/>");

						// examine headers
//echo "EXAMINE HEADERS " . $sourcefile. "<br/>";
						list($sc,$hdrs,$hdrlength,$contentlength,$contentsizedownloaded,$redir_count) = examine_headers($filepathnameofsaveobject,$curlresponseheaders,$curl_info);
						
						if($loadContentFromHAR == true)
						{
							$hdrs = implode("\r\n",$curlresponseheaders);
						}

    					$totbytesdownloaded += $contentsizedownloaded;
                        $sc = strval(intval($sc));
//echo ("code and length: ".$sc . " " .$contentlength."<br/>");
   //echo (__FILE__ . " " .__FUNCTION__ . " " . __LINE__ . "; " . $filepathnameofsaveobject . ": response code: " .$responsecode. "; sc: " . $sc . "<br/>");
                        if($sc >= 300 and $sc < 400)
                        {
      //                      echo ("REDIRECT response : " .$responsecode."<br/>");
      //                      echo ("REDIRECT sc : " .$sc."<br/><pre>");
      //                      print_r($curlresponseheaders);
      //                      echo("</pre>");
                        }
    					//echo("saved as: ".$filepathnameofsaveobject."<br/>");

    					// get file extension, and add if missing
    					$ext = pathinfo($filenameofsaveobject, PATHINFO_EXTENSION);
    					$extlen = strlen($ext);
    					//echo ("Post-CURL SAVING FILENAME EXT: ". $ext."<br/>");
    					//limit ext to 4chars
    					if( $extlen > 5)
    					{
    						// limit the length of the extension
    						//echo('dl limiting extension ('.$extlen.'): '.$namewithoutext . ' -- '.$ext.'<br/>');
    						$ext = 'ext';
    					}

    					// POST CURL CHECKS
    					// always check the mime type and determine the extension for the file regardless of what is provided
    					debug("Post-CURL SAVING FILEPATH with EXT", $filepathnameofsaveobject);
  
// echo ("Post-CURL SAVING FILENAME with EXT: ". $filenameofsaveobject."; extension = " . $ext."<br/>");
    					//echo ("Post-CURL SAVING WITH EXT: ". $ext."<br/>");


                        // retry after curl retrieval
                        if(!file_exists($filepathnameofsaveobject) or filesize($filepathnameofsaveobject) == 0)
                            backupDownloadObject($sourcefileNoSpaces,$filepathnameofsaveobject);

                    //} // end if file was downloaded for first time

  					$newlocal = $filenameofsaveobject;
  					$newlocalfp = $filepathnameofsaveobject;


                    // EXAMINE Object extension, see if it needs to be updated based upon the mime type received
						$locfn = pathinfo($newlocal);
						$locfp = pathinfo($newlocalfp);
//echo(__FUNCTION__. " ". __LINE__ . ": extensions: locfn/locfp: ". $locfn['extension']. " ". $locfp['extension'] ."<br/>");
                        if(isset($locfn['extension']))
						    $currext = $locfn['extension'];
                        else
                            $currext = $ext;

//echo(__FUNCTION__. " ". __LINE__ . ": mimetype: " . $mimetype . "<br/>");
						switch($mimetype)
						{
							case 'image/bmp':
                            case 'image/x-ms-bmp':
								$ext = 'bmp';
								break;
							case 'image/x-icon':
								$ext = 'bmp';
								break;
							case 'image/gif':
								$ext = 'gif';
								break;
							case 'image/png':
								$ext = 'png';
								break;
							case 'png': // IN ERROR
								$ext = 'png';
								$mimetype = 'image/png';
								addErrors($sourcefile,"Invalid Content Type 'png'; should be 'image/png'");
								break;
                            case 'image/bgp':
                            case 'image/x-bpg':
                                $ext = 'bpg';
                                break;
							case 'image/jpeg':
								$ext = 'jpg';
								break;
							case 'jpg': // IN ERROR HERE
                            case 'image/jpg':
								$ext = 'jpg';
								$mimetype = 'image/jpeg';
								addErrors($sourcefile,"Invalid Content Type 'jpg'; should be 'image/jpeg'");
								break;
							case 'image/webp':
								$ext = 'webp';
								break;
							case 'image/jp2':
								$ext = 'jp2';
								break;
							case "application/javascript":
							case "application/x-javascript":
							case "text/javascript":
							case "text/x-js":
								$ext = 'js';
								break;
							case "text/js": // IN ERROR HERE
								$ext = 'js';
								$mimetype = "application/javascript";
								addErrors($sourcefile,"Invalid Content Type 'text/js'; should be 'application/javascript'");
								break;
							case 'text/css':
								$ext = 'css';
								break;
							case "text/plain":

                                // check if image is adaptive - Barclays
                                $adappos = strpos(strtolower($finalurl),"textimage.adaptive");
                                if($adappos !== false)
                                {
                                    $objtype = "Image";
                                    $boolTextType = false;
                                    //echo("text adaptive image found: " . $finalurl. "<br/>");
                                    $ext = "jpg";
                                }
                                //else
									//$ext = 'txt';
									


									  if(file_exists($lfn))
									  {
										//   $fontinfo = getFontInfo($lfn);
										//   //echo("font name = ".$fontinfo.'<br/>');
										//   if(count($fontinfo) > 4)
										//       $fontname =$fontinfo[4];
										//   else
										//   {
										// 	$fontname = '';
											
											$fntsig = getFontSignature($lfn);
											if($fntsig == "wOFF" or $fntsig == "wOF2")
											{
												$objcountfont +=1;
												$boolTextType = true;
												$objtype = "Font";
				
												list($fontname,$cmap) = readWOFFFont($lfn);
											}
										//}
									  }

								break;
							case "text/html":
                                if ($ext != 'htm' and $ext != 'html')
                                {
                                    debug("adding extension","htm");
                                    $ext = 'htm';
                                }
								break;
							case "application/json":
								$objtype = 'Data';
								$ext = 'json';
								break;
							case "text/xml":
							case "application/xml":
								$ext = 'xml';
								break;
							case 'application/x-font-woff':
							case 'application/font-woff':
							case 'font/x-woff':
								$ext = 'woff';
								$objtype = "Font";
								//getFontSignature($filepathnameofsaveobject);
								break;
                            case 'font/woff2':
								$ext = 'woff2';
								$objtype = "Font";
								//getFontSignature($filepathnameofsaveobject);
								break;
                            case 'font/opentype':
                            case 'application/opentype':
                            case "application/x-font-opentype":
								$ext = 'otf';
								break;
							case "image/svg+xml":
								$ext = 'svg';
								$docSVG = new DOMDocument();
								$newlocalfps = str_replace('\\','\\\\',$newlocalfp);
								$docSVG->load(realpath($newlocalfps));
								//echo ("new local f = " .$newlocalfps . "<br/>");
								$plainsvgelement = 'c';
								//echo "calling setAllid...";
								setAllId($docSVG);
								//echo "plainsvgelement = '". $plainsvgelement . "'<br/>";
								switch($plainsvgelement)
								{
									case 'font':
										$objtype = "Font";
										$boolTextType = true;
										$objcountfont +=1;

									break;

									default:
										$objtype = "Image";
									break;
								}
								//echo "image/svg+xml setting 5 = " . $objtype . "<br/>";
								break;
                            case "application/octet-stream":
//error_log("application/octet-stream: extension: " . $ext . ": for file:" . $sourcefile);
                                if( $ext == 'woff' or $ext == 'woff2'  or $ext == 'ttf' or $ext == 'eof')
                                    {
										$objtype = "Font";
										
										$fntsig = getFontSignature($lfn);
										if($fntsig == "wOFF" or $fntsig == "wOF2")
										{
											$objcountfont +=1;
											$boolTextType = true;
											$objtype = "Font";
											list($fontname,$cmap) = readWOFFFont($lfn);
										}

										if($ext == 'ttf' or $ext == 'eof')
										{
											$fontinfo = getFontInfo($lfn);
											//echo("font name = ".$fontinfo.'<br/>');
											if(count($fontinfo) > 4)
												$fontname =$fontinfo[4];
											else
											{
												$fontname = '';
											}

										}

                                    }

                                // potential to raise error
								break;
            				case "application/x-font-ttf":
            				case "application/x-font-truetype":
                            case "font/ttf":
                                $ext = 'ttf';
                                break;
            				case "application/vnd.ms-fontobject":
                            case "application/font-sfnt":
            					//$ext = 'eot';
            					break;
							default:
								if($mimetype != '' and ($currext == '' or $currext == 'ext'))
								{

									$slashpos = strpos($mimetype,'/');

									$subtype = strtolower(substr($mimetype,$slashpos+1));
									$type = strtolower(substr($mimetype,0,$slashpos));

									if($subtype == 'jpeg')
										$ext = "jpg";
									else
									{
										$ext = $subtype;
									}
									$ext = strtolower($ext);
								}
								else
								{
									// ERROR = MISSING CONTENT TYPE
									$ext = $locfn['extension'];
									addErrors($sourcefile,"Missing Content Type");
									if (strlen($ext) > 5)
										$ext = 'ext';
								}
						}
                        //echo($newlocal . "; current extension = ".$currext. "; new extension = ".$ext."<br/>");
                        if($currext == 'ext')
                        {
                             if(substr($newlocal,-4) == ".ext")
                                 $newlocal =substr($newlocal,0,-4);
                             if(substr($newlocalfp,-4) == ".ext")
                                 $newlocalfp =substr($newlocalfp,0,-4);
                        }
                        //echo($newlocal . "; wo extension = ".$currext."<br/>");


						if(strtolower($currext) != strtolower($ext) and $ext !='')
						{
							//echo($newlocal.": swapping file extensions: from '" . $locfn['extension']. "' to '" . $ext."'<br/>");
							// remove current extension - especially if it is '.ext'
							//$lenext = strlen($locfn);
							//$newlocal = substr($newlocal,0,$lenext);
							//$newlocalfp = substr($newlocalfp,0,$lenext);
                            // if(substr($newlocal,-4) == ".ext")
                            //     $newlocal =substr($newlocal,-4);
                            // if(substr($newlocalfp,-4) == ".ext")
                            //     $newlocalfp =substr($newlocalfp,-4);

							// update extension
							$newlocal = $newlocal . '.'.$ext;
							$newlocalfp = $newlocalfp . '.'.$ext;
						}



						//echo($filenameofsaveobject.": new ext ".$newlocalfp." - contenttype: ". $contenttype."<br/>");
						debug("new file extension: ".$newlocalfp." - contenttype", $contenttype);
						//echo("new file extension:".$newlocalfp." - contenttype: ". $contenttype."<br/>");
						// update page object array
						$arr = array(
                        "Object type" => $objtype,
						"Object source" => $sourcefile,
						"Object file" => $newlocalfp,
						"File extension" => $ext,
						);
						//echo("Post-CURL UPDATING FILENAME with correct EXT: ". $newlocalfp."<br/>");
						addUpdatePageObject($arr);

						// update file
						rename($filepathnameofsaveobject,$newlocalfp);
						$filepathnameofsaveobject = $newlocalfp;
						$lfn = $newlocalfp;






					//echo($sourcefile." headers...");
					//echo('HTTP protocol: '. $protocol."<br/>");
					//echo('response code: '. $responsecode."<br/>");
					//echo('status sc: '. $sc."<br/>");
					$sc = strval(intval($sc));
					//echo('server: '. $server."<br/>");
					//echo('last modified: '. $lastmodifieddate."<br/>");
					//echo('date: '. $date."<br/>");
					//echo("<br/>");


					//echo("DL key: $key: $filepathnameofsaveobject Headers:"."<pre>".$hdrs."</pre>");

					$bodylen = extract_headersandbody($filepathnameofsaveobject,$filepathnameofsaveobject,$hdrs);
					$uncompressedLen = 0;
					$minifiedLen = 0;

					// extract redirects
					if($redir_count > 0 or ($sc >= 300 and $sc < 400))
					{
//echo ("(download): REDIRS TO PROCESS - headers saved by redirs for: $sourcefile<br/>");
						list($redirs,$newurlpath,$hdrs) = extract_redirects($redir_count,$curlresponseheaders, $sourcefile,false);

					}
					else
					{
						$redirs = array ();

                    	if($redir_count == 0){
//echo ("(download): saving the headers against the object: no redirs: $sourcefile<br/>");
    						addPageHeaders($sourcefile,$hdrs);
					    }

                    }


					//var_dump($hdrs);
					//debug("DOWNLOAD: headers" ,"<pre>".$hdrs."</pre>");
					//if($hdrs == '')
						//debug("Error retrieving headers", $sourcefile);






						if(intval($sc) >= 400)
						{
							//echo 'HTTP Error retrieving file: ' .$sourcefile.' HTTP Status: '.$sc.'<br/>';
							addErrors($sourcefile,"HTTP Status Code: ".$sc);

                            if($sc == 404)
                            {
                                //echo 'HTTP 404 Error code: '.$sc.'<br/>';
                                $noof404Errors++;
                            }
						}

//echo __FUNCTION__ . " line " . __LINE__ . 'HTTP Success retrieving file: ' .$sourcefile.";<br/> CURL Redir count: " . $redir_count . "; FINAL HTTP Status: ". $sc."; Hdr size: ". $hdrlength."; Content length transmitted: ".$contentlength."<br/>";


						// get files= stats
						debug("GETTING FILESTATS after download for",$filepathnameofsaveobject);
						// get file stats
						$fsize = filesize($filepathnameofsaveobject);
						$totfilesize += $fsize;
						debug("Header size",$hdrlength);
						debug("Content length transmitted",$contentlength);
						debug("Content type",$mimetype);

						if($contentlength == '')
							$contentlength = $contentsizedownloaded;

						//download header for file
						debug("GETTING HEADERS for",$sourcefile);
						//$sourcefile = str_replace(" ","%20",$sourcefile);

						if ($redir_count == 0 or isset($redir_count) == false )
						{
							// update file stats
							//echo("Update dl stats for item: ".$key."<br/>");
							debug("Update dl stats for item",$key);

  							$arrayOfObjects[$key]['Header size'] = $hdrlength;
  							$arrayOfObjects[$key]['Content size'] = $contentlength;
  							$arrayOfObjects[$key]['HTTP Status'] = $sc;
  							$arrayOfObjects[$key]['GZIP Status'] =	$contentencoding;
  							$arrayOfObjects[$key]['Mime Type'] = $mimetype;
  							$arrayOfObjects[$key]['Extension'] = $ext;
  							$arrayOfObjects[$key]['Combined files'] = $combocount;


                            // check for updated domain network and update object if its a shard
                            //if($arrayOfObjects[$key]['Domain ref'] == 'Shard' and isShardonCDN($arrayOfObjects[$key]['Domain']))
                            //    $arrayOfObjects[$key]['Domain ref'] = 'CDN';


							switch ($mimetype)
							{
								case "text/html":
									$objtype = "HTML";
                                    $boolTextType = true;
									break;


								case "text/css":
									$objtype = "StyleSheet";
                                    $boolTextType = true;
									break;

								case "application/javascript":
								case "application/x-javascript":
								case "text/javascript":
								case "text/x-js":
									$objtype = "JavaScript";
                                    $boolTextType = true;
									break;

								case "text/plain":
								case "text/xml":
								case "application/xml":
								case "application/json":
                                    $objtype = 'Data';
                                    $boolTextType = true;

									if($ext == 'js')
									{
										$objtype = 'JavaScript';
										$boolTextType = true;
									}

									if($ext == 'woff' or $ext == 'woff2' or $ext == 'ttf'or $ext == 'otf' or $ext == 'eot'  or $ext == 'gtf')
                                    {
										$objtype = 'Font';
                                        //override mime type
                                        switch($ext)
                                        {
                                            case 'ttf':
                                            case 'otf':
                                             // override mimetype
                                                addErrors($sourcefile,"Content Type '".$mimetype."'; should be 'application/font-sfnt'");
                                                //$mimetype = "application/font-sfnt";
                                                break;
                                             case 'eot':
                                             // override mimetype
                                                addErrors($sourcefile,"Content Type '".$mimetype."'; should be 'application/vnd.ms-fontobject'");
                                                //$mimetype = "application/vnd.ms-fontobject";
                                                break;
                                            case 'svg':
                                             // override mimetype
                                                addErrors($sourcefile,"Content Type '".$mimetype."'; should be 'image/svg+xml'");
                                                //$mimetype = "image/svg+xml";


												$docSVG = new DOMDocument();
												$newlocalfps = str_replace('\\','\\\\',$lfn);
												$docSVG->load(realpath($newlocalfps));
												$plainsvgelement = 'd';
												setAllId($docSVG);
												switch($plainsvgelement)
												{
													case 'font':
														$objtype = "Font";
														$boolTextType = true;
														$objcountfont +=1;
														addErrors($sourcefile,"Generic Content Type ". $mimetype ."; should be a valid 'image/svg+xml' type");
														//echo($lfn . ": force setting object type = " . $objtype . "<br/>");
														break;

													case 'path':
														$objtype = "Image";
														break;
													default:
														$objtype = "Data";
														break;
												}
//echo($lfn . " 2: force setting SVG object type = " . $objtype . "<br/>");

												break;
											case 'woff':
												$objtype = "Font";
                                             // override mimetype
                                                addErrors($sourcefile,"Content Type '".$mimetype."'; should be 'application/font-woff'");
                                                $boolTextType = false;
												//$mimetype = "application/font-woff";
												if(file_exists($lfn))
												{
												  //   $fontinfo = getFontInfo($lfn);
												  //   //echo("font name = ".$fontinfo.'<br/>');
												  //   if(count($fontinfo) > 4)
												  //       $fontname =$fontinfo[4];
												  //   else
												  //   {
												  // 	$fontname = '';
													  
													  $fntsig = getFontSignature($filepathnameofsaveobject);
													  if($fntsig == "wOFF")
													  {
														  $objcountfont +=1;
														  $boolTextType = true;
														  $objtype = "Font";
						  
														  list($fontname,$cmap) = readWOFFFont($filepathnameofsaveobject);
														// echo "<pre>";
														// print_r($cmap);
														// echo "</pre>";
													  }
												  //}
												}
                                                break;
											case 'woff2':
												$objtype = "Font";
                                             // override mimetype
                                                addErrors($sourcefile,"Content Type '".$mimetype."'; should be 'font/woff2'");
                                                $boolTextType = false;
												//$mimetype = "font/woff2";
												if(file_exists($lfn))
												{
												  //   $fontinfo = getFontInfo($lfn);
												  //   //echo("font name = ".$fontinfo.'<br/>');
												  //   if(count($fontinfo) > 4)
												  //       $fontname =$fontinfo[4];
												  //   else
												  //   {
												  // 	$fontname = '';
													  
													  $fntsig = getFontSignature($lfn);
													  if($fntsig == "wOF2")
													  {
														  $objcountfont +=1;
														  $boolTextType = true;
														  $objtype = "Font";
						  
														  list($fontname,$cmap) = readWOFFFont($lfn);
													  }
												  //}
												}
                                                break;

                                            default:
                                        }
                                    }
									else
                                    {
                                        // check if image is adaptive - Barclays
                                        $adappos = strpos(strtolower($sourcefile),"textimage.adaptive");
                                        if($adappos !== false)
                                        {
                                            $objtype = "Image";
                                            $boolTextType = false;
			  
											echo("text adaptive image found: " . $sourcefile. "<br/>");
                                        }

                                    }
									break;

								case "image/jpeg":
                                case "image/jpg":
                                case "image/bpg":
                                case "image/x-bpg":
								case "image/png":
								case "image/gif":
								case "image/webp":
                                case "image/x-ms-bmp":
                                case "image/bmp":
									$objtype = "Image";
                                    $boolTextType = false;
									break;

								case 'font/x-woff':
                                case 'font/woff2':
								case "application/x-font-woff":
                                case "application/font-woff":
								case "application/x-font-ttf":
								case "application/x-font-truetype":
								case "application/x-font-opentype":
								case "font/opentype":
								case "font/ttf":
								case "application/vnd.ms-fontobject":
								case "application/font-sfnt":
									$objtype = "Font";
									$boolTextType = true;
									$objcountfont +=1;
									break;

								case "image/svg+xml":
									$docSVG = new DOMDocument();
									$newlocalfps = str_replace('\\','\\\\',$lfn);
									$docSVG->load(realpath($newlocalfps));
									$plainsvgelement = 'e';
									setAllId($docSVG);
									switch($plainsvgelement)
									{
										case 'font':
											$objtype = "Font";
											$boolTextType = true;
											$objcountfont +=1;

										break;

										default:
											$objtype = "Image";
										break;
									}
									//echo "image/svg+xml 1";
									break;
                                case 'application/octet-stream':
                                    //echo('application/octet-stream ' . $ext);
                                    if($ext == 'webp')
                                    {
                                        $objtype = 'Image';
                                        $mimetype = "image/webp"; // override

                                        addErrors($sourcefile,"Generic Content Type 'application/octet-stream'; should be 'image/webp'");


                                    }
                                    else
										$objtype = 'Font';
										if(file_exists($lfn))
										{
										  //   $fontinfo = getFontInfo($lfn);
										  //   //echo("font name = ".$fontinfo.'<br/>');
										  //   if(count($fontinfo) > 4)
										  //       $fontname =$fontinfo[4];
										  //   else
										  //   {
										  // 	$fontname = '';
											  
											  $fntsig = getFontSignature($lfn);
											  if($fntsig == "wOFF" or $fntsig == "wOF2")
											  {
												  $boolTextType = true;
												  $objtype = "Font";
				  
												  list($fontname,$cmap) = readWOFFFont($lfn);
											  }
										  //}
										}

										
                                    break;

								default:
									$qspos = strpos($sourcefile,"?");
									if($qspos > 0)
										$ppath = pathinfo(substr($sourcefile,0,$qspos));
									else
										$ppath = pathinfo($sourcefile);
									//echo($ppath['extension']." - missing content type: ".$sourcefile."<br/>");

									//echo $ppath['extension'], "\n";
                                    if(isset($ppath['extension']))
                                    {
    									switch($ppath['extension'])
    									{
    	                                    case 'otf':
    											$objtype = 'Font';
    											$mimetype = 'font/opentype';
    											break;
                                            case 'gtf':
    											$objtype = 'Font';
    											$mimetype = 'font/truetype';
    											break;
    										case 'woff':
    											$objtype = 'Font';
    											$mimetype = 'application/font-woff';
    											break;
                                            case 'woff2':
    											$objtype = 'Font';
    											$mimetype = 'font/woff2';
    											break;
    										default:
    											$objtype = "Data";
    									}
                                    }
							}


//echo("DO Update page object array: ".$key . "; contentlength = " . $contentlength."<br/>");
							// update page object array
							$arr = array(
							"Object type" => $objtype,
							"Object source" => $rawurl,
							"HTTP status" => $sc,
							"Mime type" => $mimetype,
							"File extension" => $ext,
							"Header size" => $hdrlength,
							"Content length transmitted" => $contentlength,
							"Content size downloaded " => $contentsizedownloaded,
							"Compression" => $contentencoding,
							"Combined files" => $combocount,
                            "Font name" => $fontname,
							"hdrs_Server" => $server,
							"hdrs_Protocol" => $protocol,
							"hdrs_responsecode" => $responsecode,
							"hdrs_age" => strval($age),
							"hdrs_date" => $date,
							"hdrs_lastmodifieddate" => $lastmodifieddate,
							"hdrs_cachecontrol" => $cachecontrol,
							"hdrs_cachecontrolPrivate" => $cachecontrolPrivate,
							"hdrs_cachecontrolPublic" => $cachecontrolPublic,
							"hdrs_cachecontrolNoCache" => $cachecontrolNoCache,
							"hdrs_cachecontrolMaxAge" => $cachecontrolMaxAge,
							"hdrs_cachecontrolSMaxAge" => $cachecontrolSMaxAge,
							"hdrs_cachecontrolNoStore" => $cachecontrolNoStore,
							"hdrs_cachecontrolNoTransform" => $cachecontrolNoTransform,
							"hdrs_cachecontrolMustRevalidate" => $cachecontrolMustRevalidate,
							"hdrs_cachecontrolProxyRevalidate" => $cachecontrolProxyRevalidate,
							"hdrs_connection" => $connection,
							"hdrs_contentencoding" => $contentencoding,
							"hdrs_contentlength" => $contentlength,
							"hdrs_expires" => $expires,
							"hdrs_etag" => $etag,
							"hdrs_keepalive" => $keepalive,
							"hdrs_pragma" => $pragma,
							"hdrs_setcookie" => $setcookie,
							"hdrs_upgrade" => $upgrade,
							"hdrs_vary" => $vary,
							"hdrs_via" => $via,
							"hdrs_xservedby" => $xservedby,
							"hdrs_xcache" => $xcache,
							"hdrs_xpx" => $xpx,
							"hdrs_xedgelocation" => $xedgelocation,
							"hdrs_cfray" => $cfray,
							"hdrs_xcdngeo" => $xcdngeo,
                            "hdrs_xcdn" => $xcdn,
							"response_datetime" => $TimeOfResponse,
							);
							//echo("updating page object no redirs: ".$TimeOfResponse."<br/>");


						    addUpdatePageObject($arr);
							$finalurl = $rawurl;

							//update page's domain data
							UpdateDomainLocationFromHeader($rawurl,$xservedby,$xpx,$xedgelocation,$server,$cfray,$xcdngeo,$xcdn,$via,$xcache,"downloadobject: ".$rawurl);
		                    $finalurl = $rawurl;



							//get object timings
							list($objid, $offset) = lookupPageObjectValue($rawurl, "offsetDuration");
//echo("downloadobject get object timings: id: " . $objid . "; timing: " . $offset . "<br/>");


                            // update domain bytes
                            updateDomainBytes($rawurl,$contentlength,$offset);
						}
						else
						{
							if($redir_count >= 1)
							{

								// the redirection may not have been followed, so check here and add if required
								$finalurl = $redirs[$redir_count -1];
//echo("final url from redirection: ". $finalurl."<br/>");



								list($id,$lfn) = lookupPageObject($finalurl);
                                list($sid,$sfn) = lookupPageObject($rawurl);
								if (!is_numeric($id))
								{
									//echo("final url from redirection: ". $finalurl. " was not found; add object"."<br/>");
								//	addErrors($finalurl,"No headers returned for Redirected Location");

									//echo("redirs: " . $finalurl . " at redirection count " . $redir_count. "; lookup id: ".$id."<br/>");


									//update page object array  for redirected object
									$arr = array(
									"Object type" => '',
									"Object source" => $finalurl,
									"Object file" => $sfn,
									"Object parent" => '',
									"Mime type" => $mimetype,
									"Domain" => $host_domain,
									"Domain ref" => '',
									"Mime type" => $mimetype,
									"File extension" => $ext,
									"Header size" => $hdrlength,
									"Content length transmitted" => $contentlength,
									"Content size downloaded " => $contentsizedownloaded,
									"Compression" => $contentencoding,
									"Combined files" => $combocount,
									"hdrs_Server" => $server,
									"hdrs_Protocol" => $protocol,
									"hdrs_responsecode" => $responsecode,
									"hdrs_age" => $age,
									"hdrs_date" => $date,
									"hdrs_lastmodifieddate" => $lastmodifieddate,
									"hdrs_cachecontrol" => $cachecontrol,
									"hdrs_cachecontrolPrivate" => $cachecontrolPrivate,
									"hdrs_cachecontrolPublic" => $cachecontrolPublic,
									"hdrs_cachecontrolNoCache" => $cachecontrolNoCache,
									"hdrs_cachecontrolNoStore" => $cachecontrolNoStore,
									"hdrs_cachecontrolNoTransform" => $cachecontrolNoTransform,
									"hdrs_cachecontrolMustRevalidate" => $cachecontrolMustRevalidate,
									"hdrs_cachecontrolProxyRevalidate" => $cachecontrolProxyRevalidate,
									"hdrs_connection" => $connection,
									"hdrs_contentencoding" => $contentencoding,
									"hdrs_contentlength" => $contentlength,
									"hdrs_expires" => $expires,
									"hdrs_etag" => $etag,
									"hdrs_keepalive" => $keepalive,
									"hdrs_pragma" => $pragma,
									"hdrs_setcookie" => $setcookie,
									"hdrs_upgrade" => $upgrade,
									"hdrs_vary" => $vary,
									"hdrs_via" => $via,
									"hdrs_xservedby" => $xservedby,
									"hdrs_xcache" => $xcache,
									"hdrs_xpx" => $xpx,
									"hdrs_xedgelocation" => $xedgelocation,
									"hdrs_cfray" => $cfray,
									"hdrs_xcdngeo" => $xcdngeo,
                                    "hdrs_xcdn" => $xcdn,
									"response_datetime" => $TimeOfResponse,
									);
									addUpdatePageObject($arr);


									//echo("redir headers".$hdrs);
									//addPageHeaders($finalurl, $finalhdrs);

								} // a redirection was found



							} // end if there were redirections
						}

						//if($bool_b64 == false)
						//{
						//	$arrayOfObjects[$key]['Notes'] = '';
						//}
						//else
						//{
						//	$arrayOfObjects[$key]['Notes'] = "Base64";
						//}


						debug('processing file after download');


                        // check here for files of different allegiences or types
                        // check for pagespeed enabled
                        if(strpos(strtolower($rawurl),'pagespeed') !== false)
                            $pagespeedcount += 1;


						$boolTextType = false;
						// continue processing the file if it was downloaded
						if(intval($sc) == 200)
						{
							//echo("checking file 200<br/>");
							switch (trim($mimetype))
							{
								
							case "text/html":
								$objtype = "HTML";
								$boolTextType = true;
								break;

							case "text/xml":
							case "application/xml":
							case "application/json":
								$objtype = 'Data';
								$boolTextType = true;
                                break;

							case "text/html":
							case "text/plain":
                                //$objtype = "HTML";#
                                $boolTextType = true;

                                // check if image is adaptive - Barclays
                                $tipos = strpos(strtolower($finalurl),"image");
                                $adappos = strpos(strtolower($finalurl),"adaptive");
                                if($tipos!== false and $adappos !== false)
                                {
                                    $objtype = "Image";
                                    $boolTextType = false;
                                    addErrors($sourcefile,"Generic Content Type 'text/plain'; should be a valid 'image/****' type");
                                    //echo("text adaptive image found: " . $finalurl. "<br>");
                                    break;
                                }

                                if($ext == 'ttf')
                                {
                                  //$objFontInfo = new FontInfo( $lfn );
                                  //print $objFontInfo->getFontName();
                                  //$fontname = $objFontInfo->getFontName();

                                  if(file_exists($lfn))
                                  {
                                      $fontinfo = getFontInfo($lfn);
                                      //echo("font name = ".$fontinfo.'<br/>');
                                      $fontname =$fontinfo[4];
                                  }
                                  $objcountfont +=1;
                                  $boolTextType = true;
                                  $objtype = "Font";
                                  break;
                                }



								if($ext == "svg")
								{
									$docSVG = new DOMDocument();
									$newlocalfps = str_replace('\\','\\\\',$lfn);
									$docSVG->load(realpath($newlocalfps));
									$plainsvgelement = 'f';
									setAllId($docSVG);
									switch($plainsvgelement)
									{
										case 'font':
										$objtype = "Font";
										//echo($lfn . ": setting object type = " . $objtype . "<br/>");
										$boolTextType = true;
										$objcountfont +=1;
										addErrors($sourcefile,"Generic Content Type ". $mimetype ."; should be a valid 'image/svg+xml' type");
										break;
										case 'path':
											$objtype = "Image";
											break;
										default:
											$objtype = "Data";
											break;
									}
								//	echo "image/svg+xml 2";
								//	echo($lfn . " 3: force setting SVG object type = " . $objtype . "<br/>");
								}

                                //echo("$lfn<xml>");
                                //print_r(file_get_contents($lfn));
                                //echo("<xml>");


								break;

							case "image/jpeg":
                            case "image/jpg":
                            case "image/x-bpg":
                            case "image/bpg":
							case "image/png":
							case "image/gif":
							case "image/webp":
                            case "image/x-ms-bmp":
                            case "image/bmp":
								$objtype = "Image";


                                // check for query string parameters on images
                                $qs = parse_url($rawurl, PHP_URL_QUERY);
                                if(isset($qs))
                                {
                                    if(strpos(strtolower($qs),'?w=') !== false or strpos(strtolower($qs),'?h=') !== false or strpos(strtolower($qs),'?qlt=') !== false)
                                    {
                                        // Amplience found
                                        $amplience_dynamic_images_found = true;
                                    }
                                    if(strpos(strtolower($qs),'strip=true') !== false)
                                    {
                                        $amplience_dynamic_images_strip += 1;
                                    }
                                    else
                                        $amplience_dynamic_images_stripnone += 1;
                                    if(strpos(strtolower($qs),'chroma=') !== false)
                                    {
                                        $amplience_dynamic_images_chroma += 1;
                                    }
                                }




								break;
								
							case "text/css":
								$objtype = "StyleSheet";
								$boolTextType = true;
								break;
							
							case "application/javascript":
							case "application/x-javascript":
							case "text/javascript":
							case "text/x-js":
								$objtype = "JavaScript";
								$boolTextType = true;
								break;
							
							case "text/xml":
							case "application/xml":
							case "application/json":
								$objtype = 'Data';
								$boolTextType = true;
								break;


							case "application/x-font-woff":
                            case "application/font-woff":
                                //$objcountfont +=1;
                                $boolTextType = true;
                                $objtype = "Font";

                                  if(file_exists($lfn))
                                  {
                                    //   $fontinfo = getFontInfo($lfn);
                                    //   //echo("font name = ".$fontinfo.'<br/>');
                                    //   if(count($fontinfo) > 4)
                                    //       $fontname =$fontinfo[4];
									//   else
									//   {
									// 	$fontname = '';
										
										$fntsig = getFontSignature($lfn);
										if($fntsig == "wOFF")
										{
											list($fontname,$cmap) = readWOFFFont($lfn);
											// echo "downloadfontWOFF<pre>";
											// print_r($cmap);
											// echo "</pre>";
										}
									//}
                                  }

								break;
                           case "font/woff2":
                                //$objcountfont +=1;
                                $boolTextType = false;
                                $objtype = "Font";

                                  if(file_exists($lfn))
                                  {
                                    //   $fontinfo = getFontInfo($lfn);
                                    //   //echo("font name = ".$fontinfo.'<br/>');
                                    //   if(count($fontinfo) > 4)
                                    //       $fontname =$fontinfo[4];
                                    //   else
									// 	$fontname = '';
										
										$fntsig = getFontSignature($lfn);
										if($fntsig == "wOF2")
										{
											list($fontname,$cmap) = readWOFFFont($lfn);
										}
                                  }

								break;
							case "application/x-font-ttf":
							case "application/x-font-truetype":
							case "application/x-font-opentype":
							case "font/opentype":
                            case "font/x-woff":
                            case "font/ttf":
                                $objcountfont +=1;
                                $boolTextType = true;
                                $objtype = "Font";

                                if(file_exists($lfn))
                                {
                                    $fontinfo = getFontInfo($lfn);
                                    //echo("font name = ".$fontinfo.'<br/>');
                                    if(count($fontinfo) > 4)
                                          $fontname =$fontinfo[4];
                                      else
                                        $fontname = '';
                                }
                                //$objFontInfo = new FontInfo( $lfn );
                                //print $objFontInfo->getFontName();
                                //$fontname = $objFontInfo->getFontName();
								break;

							case "application/vnd.ms-fontobject":
							case "application/font-sfnt":
							case "image/svg+xml":
							$docSVG = new DOMDocument();
							$newlocalfps = str_replace('\\','\\\\',$newlocalfp);
							$docSVG->load(realpath($newlocalfps));
							$plainsvgelement = 'g';
							setAllId($docSVG);
							switch($plainsvgelement)
							{
								case 'font':
								  $objtype = "Font";
								  $boolTextType = true;
								  $objcountfont +=1;

								  break;

								default:
								  	$objtype = "Image";
									break;
							}
							//echo $lfn . " image/svg+xml 3 for object of type " . $objtype . "</br>";
                                if($objtype == "Font")
                                {
                                //$objFontInfo = new FontInfo( $lfn );
                                //print $objFontInfo->getFontName();
                                //$fontname = $objFontInfo->getFontName();

                                    if(file_exists($lfn))
                                    {
                                        $fontinfo = getFontInfo($lfn);
//echo("font name = ".$fontinfo.'<br/>');
                                        if(count($fontinfo) > 4)
                                              $fontname =$fontinfo[4];
                                          else
                                            $fontname = '';
                                    }
                                }

								break;


                            case "audio/basic":
                            case "audio/mid":
                            case "audio/mpeg":
                            case "audio/aiff":
                            case "audio/mpegurl":
                            case "audio/ogg":
                            case "audio/x-pn-realaudio":
                            case "audio/vnd-rn-realaudio":
                            case "audio/x-wav":
                            case "audio/vnf-wave":
                            case "audio/L24":
                            case "audio/mp4":
                            case "audio/flac":
                            case "audio/opus":
                            case "audio/vorbis":
                            case "audio/webm":
                                $boolTextType = false;
                                $objtype = "Audio";
                                break;

                            case "video/mp4":
                            case "video/webm":
                            case "video/ogg":
                            case "video/avi":
                            case "video/quicktime":
                            case "video/x-matroska":
                            case "video/x-ms-wmv":
                            case "video/x-flv":
                                $boolTextType = false;
                                $objtype = "Video";
                                break;
                            case "application/ogg":
                                $boolTextType = false;
                                $objtype = "Video";
                                break;

                            case "application/x-shockwave-flash":
                                $boolTextType = false;
                                $objtype = "Object";
                                break;

							default:
                                $objtype = "Unknown";

                                addErrors($rawurl,"Missing Content Type");
                                // check extension to try to detect the object type
                                switch (strtolower($ext))
                                {
                                    case 'woff':
                                    case 'woff2':
                                    case 'ttf':
                                    case 'eot':
                                    case 'otf':
                                        $objcountfont +=1;
                                        $boolTextType = true;
                                        $objtype = "Font";

                                        if($ext == 'ttf')
                                        {
                                        //$objFontInfo = new FontInfo( $lfn );
                                        //print $objFontInfo->getFontName();
                                        //$fontname = $objFontInfo->getFontName();

                                            if(file_exists($lfn))
                                            {
                                                $fontinfo = getFontInfo($lfn);
                                                //echo("font name = ".$fontinfo.'<br/>');
                                                if(count($fontinfo) > 4)
                                                      $fontname =$fontinfo[4];
                                                  else
                                                    $fontname = '';
										
													$fntsig = getFontSignature($lfn);
													if($fntsig == "wOFF" or $fntsig == "wOF2")
													{
														list($fontname,$cmap) = readWOFFFont($lfn);
													}
				
												
										
												}
                                        }



                                        break;
                                    case 'jpg':
                                    case 'jpeg':
                                    case 'gif':
                                    case 'png':
                                    case 'bmp':
                                    case 'tiff':
                                    case 'webp':
                                        $objtype = "Image";
                                       // $arrayListOfImages[] = $iitem;
                                        $objcountimg = $objcountimg + 1;

                                        break;
                                    case 'css':
                                        $objtype = "StyleSheet";
                                        // CSS IMPORT
                                        $objtype = "Data";
                                        break;
                                    case 'json':

                                        break;
                                    default:
                                        $objtype = "Unknown";
        								$boolTextType = false;
                                        $slpos = strpos($mimetype,"/");
                                        if($slpos !== false)
        								    $objtype = ucwords(substr($mimetype,0,$slpos));


                                        } // end switch for unknown mime type


							} // end switch for mime type to check object after download

//echo ("end switch for mime type after download " . $lfn . " " . $objtype . " text type: " . $boolTextType .  "<br/>");

							if ($boolTextType == true)
                            {
                              // identify file as a tag management file
                              detectTagManager($lfn,$domain,$finalurl);
                              detect3PJSFile($lfn,$domain,$finalurl);
                            }

							if ($boolTextType == true and $redir_count == 0)
							{
								$gzipsize = 0;
								//echo("<br/>Gzip status: ".$contentencoding."<br/>");
								debug("Gzip status",$contentencoding);
								if(strpos($contentencoding, 'gzip') > 0)
								{
									debug("This file is already compressed when served with Gzip compression","");
								}
								if(strpos($contentencoding, 'br') > 0)
								{
									debug("This file is already compressed when served with Brotli compression","");
								}

                                // compress a text file
								{
									// lookup the URL to get the key
									list($id,$lfn) = lookupPageObject($finalurl);

									if(is_numeric($id))
									{
										//echo("Checking compression before: $lfn $objtype<br/>");
										$objtype = $arrayPageObjects[$id]["Object type"];
										//echo("Checking compression after: $lfn $objtype<br/>");
										//echo "GZIP Compressing $id - $filepathnameofsaveobject: obj type: ".$objtype." " .$sc ."<br/>";
										//echo "GZIP Compressing $id - $filepathnameofsaveobject: obj type: ".$objtype." " .$sc ."<br/>";
										list($dt,$ot,$zippedfilename,$origsize,$gzipsize,$savingbytes,$savingpct) = CompressFile($objtype,$id,$filepathnameofsaveobject,true);
									}
								}

//echo("Checking minification: $lfn $objtype<br/>");
								// get content of downloaded file if it has been downloaded

								if(file_exists($lfn))
								{
									$filecontent = file_get_contents($lfn);
									$uncompressedLen = strlen($filecontent);

									$minfile = '';
									$minorigsize = 0;
									$mingzipsize =0;
									$minsavingbytes = 0;
									$minsavingpct = 0;
									switch (trim($mimetype))
									{
										case "text/html":
										case "text/plain":

											// minify

                                            // set up optimisation folder for minifying the CSS
                                            $folder = '_Optimised_HTML'.DIRECTORY_SEPARATOR;
                                            $baseTextfolder =  $filepath_domainsavedir.$folder;
                                            if (!file_exists($baseTextfolder))
                                                mkdir($baseTextfolder, 0777, true);
                                            $path_parts = pathinfo($lfn);
                                            $optfilename =  $baseTextfolder.$path_parts['filename'].".min.css";
                                            //echo"saving as : ".$optfilename."<br/>";

            								$minifiedcss = compress_CSS($filecontent);
            								$minfile = $optfilename;
            								//echo("<br/>saving minified css to file: ".$minfile.": ");
            								file_put_contents("$minfile", $minifiedcss);
            								$minifiedLen = strlen($minifiedcss);
            								//echo(" minification: length: $uncompressedLen --> compressed length: $minifiedLen<br/>");
            								// now gzip the minified file
            								list($dt,$ot,$minfile,$minorigsize,$mingzipsize,$minsavingbytes,$minsavingpct) = CompressFile($objtype,$id,$minfile,false);
											break;

										case "text/css":
											$objcountcss = $objcountcss + 1;
											// minify

                                            // set up optimisation folder for minifying the CSS
                                            $folder = '_Optimised_CSS'.DIRECTORY_SEPARATOR;
                                            $baseTextfolder =  $filepath_domainsavedir.$folder;
                                            if (!file_exists($baseTextfolder))
                                                mkdir($baseTextfolder, 0777, true);
                                            $path_parts = pathinfo($lfn);
                                            $optfilename =  $baseTextfolder.$path_parts['filename'].".min.css";
                                            //echo"saving as : ".$optfilename."<br/>";

            								$minifiedcss = compress_CSS($filecontent);
            								$minfile = $optfilename;
            								//echo("<br/>saving minified css to file: ".$minfile.": ");
            								file_put_contents("$minfile", $minifiedcss);
            								$minifiedLen = strlen($minifiedcss);
            								//echo(" minification: length: $uncompressedLen --> compressed length: $minifiedLen<br/>");
            								// now gzip the minified file
            								list($dt,$ot,$minfile,$minorigsize,$mingzipsize,$minsavingbytes,$minsavingpct) = CompressFile($objtype,$id,$minfile,false);

                                            processCSSSelectors($filecontent,$id,$path_parts['filename'].'.'.$path_parts['extension'],$finalurl);

                                            break;

										case "application/javascript":
										case "application/x-javascript":
										case "text/javascript":
										case "text/x-js":
												$objcountscript = $objcountscript + 1;
												// minify
												//echo("<br/>minifiying js file: ".$lfn.": ");

                                                $folder = '_Optimised_JS'.DIRECTORY_SEPARATOR;
                                                $baseTextfolder =  $filepath_domainsavedir.$folder;
                                                if (!file_exists($baseTextfolder))
                                                    mkdir($baseTextfolder, 0777, true);
                                                $path_parts = pathinfo($lfn);
                                                $optfilename =  $baseTextfolder.$path_parts['filename'].".min.js";
                                                //echo"saving as : ".$optfilename."<br/>";



												// adapt these 2 paths to your files.
												$src = $lfn;
												$out = $optfilename;

												// or uncomment these lines to use the argc and argv passed by CLI :
												/*
												if ($argc >= 3) {
													$src = $argv[1];
													$out = $argv[2];
												} else {
													echo 'you must specify  a source file and a result filename',"\n";
													echo 'example :', "\n", 'php example-file.php myScript-src.js myPackedScript.js',"\n";
													return;
												}
												*/


												$script = file_get_contents($src);

												$t1 = microtime(true);

												$packer = new JavaScriptPacker($script, 'None', true, false);
												$packed = $packer->pack();

												$t2 = microtime(true);
												$time = sprintf('%.4f', ($t2 - $t1) );
												//echo 'script ', $src, ' packed in ' , $out, ', in ', $time, ' s.', "\n";

												file_put_contents($out, $packed);

												$minifiedLen = strlen($packed);
												//echo($lfn. " minification: length: $uncompressedLen --> compressed JS: $minifiedLen <br/>");
												// now gzip the minified file
												list($dt,$ot,$minfile,$minorigsize,$mingzipsize,$minsavingbytes,$minsavingpct) = CompressFile($objtype,$id,$out,false);

                                                // look for occurences of document.write
                                                $docwrite_count = substr_count($script,"document.write");
                                                //if($docwrite_count > 0)
                                                //{
                                                //    echo($docwrite_count . " document.write statements found in JavaSccript file: ". $src."<br/>");
                                                //}

												break;
										default:
									} // end switch


									// update page object array with minification stats
									$arr = array(
									"Object source" => $finalurl,
									"Object type" => $objtype,
                                    "Mime type" => $mimetype,
                                    "JS docwrite" => $docwrite_count,
									"Content size uncompressed" => $uncompressedLen,
									"Content size compressed" => $gzipsize,
									"Content size minified uncompressed" => $minifiedLen,
									"Content size minified compressed" => $mingzipsize,
                                    "Font name" => $fontname
									);
//echo("updating page object - min stats ". $objtype."<br/>");
                                    //error_log(" font returned: " . $fontname . "<br/>");
									addUpdatePageObject($arr);

								} // if file exists
								else
								{
									addErrors($finalurl,"Zero filesize");



								}

							}
							else
							{

                                // save any change to object type for non text file
                                $arr = array(
            					"Object source" => $finalurl,
            					"Object type" => $objtype,
                                "Font name" => $fontname
            					);
//echo("updating page object - min stats<br/>");
            					addUpdatePageObject($arr);
								// invalid file

								//$arrayOfObjects[$key]['Header size'] = '';
								//$arrayOfObjects[$key]['Notes'] = '';
							}

							//addImageData($sourcefile,"EXIF","IPTC");

						} // end if a 200 status code
					} // valid file

			} // valid file to be downloaded

            $truemimetype = ''; // set for non images
			if(intval($sc) == 200 or strpos($embeddedfile,"data:") !== false)
			{
				if(strpos($embeddedfile,"data:") !== false)
				{
//echo "processing embedded data file: ".$embeddedfile. "; mimetype =  " . $mimetype . "<br/>";

        		}



					// IMAGE STUFF
					$truemimetype = '';
					$exifbytes = 0;
					$iptcbytes = 0;
					$xmpbytes = 0;
					$comment = '';
					$commentbytes = 0;
					$metadatabytes = 0;
					$density = '';
					$structure = '';
					$animation = '';
					$iccbytes = 0;
					$savequality = '';
					$estquality = '';
					$app12bytes = 0;
					$duckytext = '';
					$fsize = 0;
                    if(!isset($mimetype))
					    error_log($lfn . ": image mimetype given ". $mimetype . "<br/>");





					// check file signature irrespective of the stated mimetype - it may be wrong - get true mmime type for file
					if($mimetype == 'image/gif' or $mimetype == 'image/png' or $mimetype == 'image/jpeg' or $mimetype == 'image/jpg' or $mimetype == 'image/webp' or $mimetype == 'image/jp2' or $mimetype == 'image/svg+xml' or $mimetype == "image/x-ms-bmp" or $mimetype == "image/bmp" or $mimetype = '' or $mimetype = 'application/octet-stream' or $mimetype = 'text/plain')
					{
						debug("Analysing Image", $lfn);
//echo("Analysing Image: ". $lfn."</br>");


						if(file_exists($lfn))
						{
							$fsize = filesize($lfn);
//echo("Analysing Image: ". $lfn."; size: ".$fsize."</br>");
							if($fsize == 0)
							{
								addErrors($sourcefile,"Zero filesize");
                                $truemimetype = '';
							}
							else
							{
								//addImageData($sourcefile,"DUMMY",'');
								$truemimetype = getMimeTypeFromImageSignature($lfn);

								@$get    = getimagesize($lfn);
								if(isset($get))
								{
									$width  = $get[0];
									$height = $get[1];
									$type   = $get[2];
									$attr   = $get[3];
									@$bits   = $get['bits'];
									$colordepth = $bits;
									$mime   = $get['mime'];
								}
							}
						}
						else
						{
//echo ("image file was not saved properly: ". $lfn . "<br/>");
							$truemimetype = $mimetype;
							$dlError = "status code = " . $sc;
							addErrors($sourcefile, $dlError . "; not downloaded successfully as ". $lfn);
						}
					}
					debug("Returned image true mime-type",$truemimetype);
					//echo("image filesize". $fsize."<br/>");
					if($truemimetype == 'image/gif') //	and $fsize > 45
					{
						list($imagetype,$imageencoding,$width,$height,$interlace,$gcolordepth,$metadatabytes,$commentbytes,$commenttext,$structure,$animation,$xmpinfo,$xmpbytes) = decodeGIF($body,$lfn);


						if($imagetype !='')
						{
//echo("trying to update page object - GIF<br/>");
//echo("GIF structure: " . $structure ."<br/>");
							// update image file with Metadata stats
							// update page object array
							$arr = array(
							"Object source" => $finalurl,
							"Image type" => $imagetype,
							"Image encoding" => $imageencoding,
							"Image actual size" => $width." x ".$height." px",
							"Content size uncompressed" => '',
							"Content size compressed" => '',
							"Content size minified uncompressed" => '',
							"Content size minified compressed" => '',
							"Colour type" => 'Indexed',
							"Colour depth" => $colordepth . " bit",
							"Interlace" => $interlace,
							"Animation" => $animation,
							"Metadata bytes" => $metadatabytes,
							"Comment" => $commenttext,
							"Comment bytes" => $commentbytes,
							"ICC colour profile bytes" => 0,
							"EXIF bytes" => 0,
							"APP12 bytes" => 0,
							"IPTC bytes" => 0,
							"XMP bytes" => $xmpbytes,

							);
							//echo("updating page object - GIF<br/>");
							//echo("Object source ".$finalurl."<br/>");
							//echo("Image type ". $imagetype."<br/>");
							//echo("Image encoding " .$imageencoding."<br/>");
							//echo("Image actual size " . $width." x ".$height." px"."<br/>");
							//echo("Content size uncompressed" .$fsize."<br/>");
							//echo("Colour type ". 'Indexed'."<br/>");
							//echo("Colour depth ". $colordepth . " bit"."<br/>");
							//echo("Interlace " . $interlace."<br/>");
							//echo("Animation " .$animation."<br/>");
							//echo("Metadata bytes " . $metadatabytes."<br/>");
							//echo("Comment " . $commenttext."<br/>");
							//echo("Comment bytes ". $commentbytes."<br/>");

							
							addUpdatePageObject($arr);

                            if($xmpbytes > 0)
    						{
    						   //$res = array();
    							//exec('win_tools\webpmux -get xmp '. $lfn . ' -o '.$filepath_basesavedir.'gifxmp.xml',$res);
    							//$resstr = implode("\r\n",$res);
    							//echo("webpmux xmp result: ".$resstr."<br/>");

    							//@$xmpstr = file_get_contents_utf8($filepath_basesavedir.'gifxmp.xml');

    							$res = array();
                                if($OS == "Windows")
    							    exec($perlbasedir . 'perl win_tools\ExifToolPerl\exiftool.pl -xmp -b ' . $lfn,$res);
                                else
                                        exec('exiftool -xmp -b ' . $lfn,$res); // or path in lnx_tools/ExifTool
    							$xmpinfo = $res;

    							//echo("webpmux xmp data: ".$xmpstr."<br/>");
    							addImageData($finalurl,"XMP",$xmpinfo); // add xmp
    						}

						}
					}


					if($truemimetype == 'image/webp' and $fsize > 0)
					{
						list($imagetype,$imageencoding,$canvassize,$metadatabytes,$iccbytes,$xmpbytes) = decodeWEBP($body,$lfn);
					
						// update image file with Metadata stats
						// update page object array
						$arr = array(
						"Object source" => $finalurl,
						"Image type" => $imagetype,
						"Image encoding" => $imageencoding,
						"Image actual size" => $canvassize." px",
						"Content size uncompressed" => '',
						"Content size compressed" => '',
						"Content size minified uncompressed" => '',
						"Content size minified compressed" => '',
						"Colour type" => 'True Colour',
						"Colour depth" => "24 bit",
						"Interlace" => '',
						"Animation" => $animation,
						"Metadata bytes" => $metadatabytes,
						"ICC colour profile bytes" => $iccbytes,
						"EXIF bytes" => $exifbytes,
						"APP12 bytes" => 0,
						"IPTC bytes" => $iptcbytes,
						"XMP bytes" => $xmpbytes,
						);
						//echo("updating page object - WEBP<br/>");
						addUpdatePageObject($arr);
					
						if($xmpbytes > 0)
						{
							$res = array();
                            if($OS == "Windows")
							    exec('win_tools\webpmux -get xmp '. $lfn . ' -o '.$filepath_basesavedir.'webpxmp.xml',$res);
                            else
                                exec('webpmux -get xmp '. $lfn . ' -o '.$filepath_basesavedir.'webpxmp.xml',$res);
							$resstr = implode("\r\n",$res);
							//echo("webpmux xmp result: ".$resstr."<br/>");

							$xmpstr = file_get_contents($filepath_basesavedir.'webpxmp.xml');

							$res = array();
                            if($OS == "Windows")
							    exec($perlbasedir . 'perl win_tools\ExifToolPerl\exiftool.pl -xmp -b ' . $lfn,$res);
                            else
                                exec('exiftool -xmp -b ' . $lfn,$res);
							$xmpinfo = $res;

							//echo("webpmux xmp data: ".$xmpstr."<br/>");
							addImageData($finalurl,"XMP",$xmpinfo); // add xmp
						}
					}

					if($truemimetype == 'image/png')
					{
						    //list($imagetype,$imageencoding,$structure,$width,$height,$colourdepth,$colourtype,$filtermethod,$interlace,$metadatabytes,$commentbytes,$commenttext,$xmpbytes) = decodePNGoold($body,$lfn);
                        	list($imagetype,$imageencoding,$structure,$width,$height,$colourdepth,$colourtype,$filtermethod,$interlace,$metadatabytes,$commentbytes,$commenttext,$xmpbytes,$iccbytes) = decodePNG($body,$lfn);

						// update image file with Metadata stats
						// update page object array 
						$arr = array(
						"Object source" => $finalurl,
						"Image type" => $imagetype,
						"Image encoding" => $imageencoding,
						"Image actual size" => $width." x ".$height." px",
						"Content size uncompressed" => '',
						"Content size compressed" => '',
						"Content size minified uncompressed" => '',
						"Content size minified compressed" => '',
						"Colour type" => $colourtype,
						"Colour depth" => $colourdepth." bit",
						"Interlace" => $interlace,
						"Metadata bytes" => $metadatabytes,
						"Comment" => $commenttext,
						"Comment bytes" => $commentbytes,
						"ICC colour profile bytes" => $iccbytes,
						"Animation" => $animation,
						"EXIF bytes" => $exifbytes,
						"APP12 bytes" => 0,
						"IPTC bytes" => $iptcbytes,
						"XMP bytes" => $xmpbytes,

						);
						//echo("updating page object - PNG<br/>");
						addUpdatePageObject($arr);


                        //echo("png xmp bytes: ".$xmpbytes."<br/>");
						if($xmpbytes > 0)
						{

							$res = array();
                            if($OS == "Windows")
							    exec('win_tools\exiv2 -feX extract  '. $lfn,$res);
                            else
                                exec('linux/bin/exiv2 -feX extract  '. $lfn,$res);
							$resstr = implode("\r\n",$res);
							//echo("png exiv2 xmp result: ".$resstr."<br/>");
                            $modlfn = str_replace('.png','.xmp',$lfn);
                            //echo("modlfn = " . $modlfn."<br/>");



							// get contents of a file into a string
                            $handle = fopen($modlfn, "r");
                            $xmpstr = fread($handle, filesize($modlfn));
                            fclose($handle);
                            unlink($modlfn);

                            //echo("<xmp>");
                            //print_r($xmpstr);
                            //echo("</xmp>");

                            addImageData($sourcefile,"XMPstr",$xmpstr);


						}
						//echo($sourcefile." PNG:<br/>".$structure);


					}
					
					if($truemimetype == 'image/jp2' and $fsize > 0)
					{
						list($imagetype,$imageencoding) = decodeJPG2000($body,$lfn);
					
						// update image file with Metadata stats
						// update page object array
						$arr = array(
						"Object source" => $finalurl,
						"Image type" => $imagetype,
						"Image encoding" => $imageencoding,
						"Image actual size" => $width."x".$height." px",
						"Content size uncompressed" => '',
						"Content size compressed" => '',
						"Content size minified uncompressed" => '',
						"Content size minified compressed" => '',
						"Colour type" => 'True Colour',
						"Colour depth" => '24 bit',
						"Interlace" => '',
						"Animation" => $animation,
						"EXIF bytes" => $exifbytes,
						"APP12 bytes" => 0,
						"IPTC bytes" => $iptcbytes,
						"XMP bytes" => $xmpbytes,
						);
						addUpdatePageObject($arr);

					}


					if($truemimetype == 'image/svg+xml' || $mimetype == 'image/svg+xml')
					{
						$imagetype = 'SVG';

						// update image file with Metadata stats
						// update page object array
						$arr = array(
						"Object source" => $finalurl,
						"Image type" => $imagetype,
						"Image encoding" => 'UTF-8',
						"Image actual size" => '',
						"Metadata bytes" => 0,
						"Comment" => '',
						"Comment bytes" => 0,
						"ICC colour profile bytes" => 0,
						"Animation" => '',
						"EXIF bytes" => 0,
						"APP12 bytes" => 0,
						"IPTC bytes" => 0,
						"XMP bytes" => 0,
						);
						//echo("updating page object - SVG<br/>");
						addUpdatePageObject($arr);


					}

					if($truemimetype == 'image/bmp' || $truemimetype == 'image/x-ms-bmp')
					{

                        list($imagetype,$structure,$width,$height,$encoding,$colourdepth) = decodeBMP($body,$lfn);
                        if($colourdepth < 16)
                            $colourtype="Indexed";
                        else
                            $colourtype="True-Colour";

						// update image file with Metadata stats
						// update page object array
						$arr = array(
						"Object source" => $finalurl,
						"Image type" => $imagetype,
						"Image encoding" => $encoding,
						"Image actual size" => $width . ' x '. $height.'px',
                        "Colour depth" => $colourdepth." bit",
                        "Colour type" => $colourtype
						);
						//echo("updating page object - BMP<br/>");
						addUpdatePageObject($arr);


					}



					if($truemimetype == 'image/jpeg' and $fsize > 0)
					{


//echo ("<br/>Decoding JPEG: $sourcefile<br/>");
						$exif = '';
						$iptc = '';

						//decode jpeg image
						list($imagetype,$imageencoding,$exifbytes,$iptcbytes,$xmpbytes,$xmpdata,$commenttext,$commentbytes,$metadatabytes,$density,$iccbytes,$structure,$duckyquality,$app12bytes,$duckytext,$commentquality,$chromasubsampling) = decodeJPEG($body,$lfn);
						if(strpos($imageencoding,"Progressive") !== false)
							$interlace = 'Progressive';
						else
							$interlace = 'Non-Interlaced';


						// estimate quality
						$res = array();
                        if($OS == "Windows")
						    exec('win_tools\jpegquality '. $lfn,$res);
                        else
                            exec('./lnx_tools/jpegq '. $lfn,$res);

						$resstr = implode($res);
						if(strpos($resstr,'%'))
						{
							if(intval($resstr>0))
								$estquality = $resstr;
							else
								$estquality = 'Low';
						}
						else
						{
							$estquality = 'N/A';
						}
//echo ("Estimated quality ". $estquality . "($resstr) for ". $lfn."<br/>");

						$savequality = max($duckyquality,$commentquality);

						// update image file with Metadata stats
						// update page object array
						$arr = array(
						"Object source" => $finalurl,
                        "Content size uncompressed" => '',
						"Content size compressed" => '',
						"Content size minified uncompressed" => '',
						"Content size minified compressed" => '',
						"Metadata bytes" => $metadatabytes,
						"EXIF bytes" =>  $exifbytes,
						"APP12 bytes" => $app12bytes,
						"IPTC bytes" => $iptcbytes,
						"XMP bytes" => $xmpbytes,
						"Comment" => $commenttext,
						"Comment bytes" => $commentbytes,
						"ICC colour profile bytes" => $iccbytes,
						"Image type" => $imagetype,
						"Image encoding" => $imageencoding,
						"Image actual size" => $width." x ".$height." px",
						"Colour type" => 'True Colour',
						"Colour depth" => '24 bit',
						"Interlace" => $interlace,
						"Est. quality" => $estquality,
						"Photoshop quality" => $savequality,
						"Chroma subsampling" => $chromasubsampling,
						"Animation" => $animation,
						);
//echo("updating page object - JPG: subsampling: " . $chromasubsampling. "<br/>");
						addUpdatePageObject($arr);

						if($iccbytes > 0)
						{
							$res = array();
                            if($OS == "Windows")
							    exec($perlbasedir . 'perl win_tools\ExifToolPerl\exiftool.pl -icc_profile -b ' . $lfn,$res);
                            else
                                exec('exiftool -icc_profile -b ' . $lfn,$res);
							$iccinfo = $res;
                            //if($iccinfo !== null)
							  //  addImageData($sourcefile,"ICC",$iccinfo);
						}

						if($exifbytes > 0)
						{


							// get exif data


							$exifstr = '';
							$exif = @exif_read_data($filepathnameofsaveobject, 0, true);
							if($exif!==false)
							{
								//echo "EXIF<pre>";
								//var_dump($exif);
								//echo "</pre>";
								//echo implode($exif);


								foreach ($exif as $key => $section) {
									foreach ($section as $name => $val) {
										if($name != "MakerNote" and !is_null($val) )
										{
											$str = preg_replace('/[^\P{C}\s]+/u', '',$val);
											@$exifstr .= "$key.$name: $str<br/>";
										}
										//else
											//$exifstr .= "$key.$name: [not shown]<br/>";
									}
								}

								//echo($exifstr);
								$el = strlen($exifstr);
								//echo( "length of exifstr = $el<br/>");
								addImageData($sourcefile,"EXIF",$exifstr); // add $exif if Computed and file keyword values are wanted
							}



							// EXIFTOOL - exif
							//$res = array();
							//exec($perlbasedir . 'perl win_tools\ExifToolPerl\exiftool.pl -E ' . $filepathnameofsaveobject,$res);
							//$exifinfo = $res;
							//addImageData($sourcefile,"EXIF",$exifinfo);


							// Thumbnail extraction
							$image = $lfn;
							$thumbnail = @exif_thumbnail($image, $width, $height, $type);
							if($thumbnail != false)
							{
								$path_parts = pathinfo($lfn);

								//echo $path_parts['dirname'], "\n";
								//echo $path_parts['basename'], "\n";
								//echo $path_parts['extension'], "\n";
								//echo $path_parts['filename'], "\n"; // since PHP 5.2.0
								if( isset($path_parts['extension']))
								{
									$ext = $path_parts['extension'];

									$tnfn = joinFilePaths($path_parts['dirname'],$path_parts['filename'].'_tn.'.$ext);
									file_put_contents($tnfn,$thumbnail);
									//echo "<img  width='$width' height='$height' src='data:image;base64,".base64_encode($thumbnail)."'>";
								}
								else
								{
									//echo $path_parts['dirname'], "\n";
									//echo $path_parts['basename'], "\n";
									//echo $path_parts['extension'], "\n";
									//echo $path_parts['filename'], "\n"; // since PHP 5.2.0
								}
							}
						}

						debug('Analysing image IPTC data',$filepathnameofsaveobject);
						$size = getimagesize($filepathnameofsaveobject, $info);
						if(isset($info['APP13']) and $iptcbytes > 0)
						{
							$iptcstr = '';
							$iptc = iptcparse($info["APP13"]);
//error_log($filepathnameofsaveobject . ": IPTC: " . $iptc);
                            if(isset($iptc) and is_array($iptc))
                            {

    							foreach (array_keys($iptc) as $s) {
    								$c = count ($iptc[$s]);
    								for ($i=0; $i <$c; $i++)
    								{
    									//echo $s.' = '.$iptc[$s][$i].'<br>';
    									$str = preg_replace('/[^\P{C}\s]+/u', '',$iptc[$s][$i]);
    									$iptcstr .= $s.": ". $str."\r\n";
    								}
    							}
    							//echo("res before IPTC: <pre>");
    							//print_r($res);
    							//echo("</pre><br/>");
    							$res = array(); // clear array;
    							//echo('Analysing image IPTC data via EXIV2: '.$filepathnameofsaveobject."<br/>");
    							//echo($iptcstr."<br/>");
    							//echo('Analysing image IPTC data via EXIV2: '.$filepathnameofsaveobject."<br/>");
                                if($OS == "Windows")
    							    exec('win_tools\exiv2 -PInt ' . $lfn,$res);
                                else
                                    exec('linux/bin/exiv2 -PInt ' . $lfn,$res);
    							//echo("IPTC: <pre>");
    							//print_r($res);
    							//echo("</pre><br/>");
    							$iptcstr = implode("\r\n",$res);
    							//$iptcstr = htmlspecialchars($iptcstr);
    							addImageData($sourcefile,"IPTC",$iptcstr);
    							//echo $iptcstr;
                            }
                            else
                            {
//error_log($filepathnameofsaveobject . ": IPTC parsing error: " . $iptc);
                                //echo($filepathnameofsaveobject . " - IPTC: <pre>");
    							//print_r($res);
    							//echo("</pre><br/>");
                            }
						}


						if($xmpbytes > 0)
						{
							debug('Analysing image XMP data',$filepathnameofsaveobject);
							addImageData($sourcefile,"XMP",$xmpdata);
						}


						if($app12bytes > 0)
						{
							addImageData($sourcefile,"APP12",$duckytext); // add $exif if Computed and file keyword values are wanted
							//echo("<br/>".$app12bytes . " - ".$duckytext."<br/>");
						}

					} // end if a JPEG
			}

			if ($truemimetype !='')
			{
				debug('Analysing image comment data',$filepathnameofsaveobject);
				if($commentbytes > 0)
				{
					//echo($sourcefile.': Adding image comment data: '.$commenttext);
					addImageData($sourcefile,"Comments",$commenttext);
				}

				debug('Analysing image structure',$filepathnameofsaveobject);
//echo($sourcefile.': Adding image structure: '.$structure);
				addImageData($sourcefile,"Structure",$structure);
			}






}; // function DownloadObject




// function curlOptimiseImage_JPG_PNG
// send jpeg or png image to TinyPNG/TinyJPG for optimisation
// https://tinypng.com/developers/reference
function curlOptimiseImage_JPG_PNG($key, $input, $output)
{
	$funcresult = false;


	$request = curl_init();
	curl_setopt_array($request, array(
	  CURLOPT_URL => "https://api.tinypng.com/shrink",
	  CURLOPT_USERPWD => "api:" . $key,
	  CURLOPT_POSTFIELDS => file_get_contents($input),
	  CURLOPT_BINARYTRANSFER => true,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_HEADER => true,
	  /* Uncomment below if you have trouble validating our SSL certificate.
		 Download cacert.pem from: http://curl.haxx.se/ca/cacert.pem */
	  // CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
	  CURLOPT_SSL_VERIFYPEER => true
	));

	$response = curl_exec($request);
	if (curl_getinfo($request, CURLINFO_HTTP_CODE) === 201) {
	  /* Compression was successful, retrieve output from Location header. */
	  $headers = substr($response, 0, curl_getinfo($request, CURLINFO_HEADER_SIZE));
	  foreach (explode("\r\n", $headers) as $header) {
		if (substr($header, 0, 10) === "Location: ") {
		  $request = curl_init();
		  curl_setopt_array($request, array(
			CURLOPT_URL => substr($header, 10),
			CURLOPT_RETURNTRANSFER => true,
			/* Uncomment below if you have trouble validating our SSL certificate. */
			// CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
			CURLOPT_SSL_VERIFYPEER => true
		  ));
		  file_put_contents($output, curl_exec($request));
		}
		$funcresult = true;
	  }
	} else
		{
			print(curl_error($request));
			/* Something went wrong! */
			print("Compression failed");
			$funcresult = false;
		}

	return $funcresult;
}

function setAllId($DOMNode){
  global $plainsvgid, $plainsvgelement;
//echo "setallid called with domnode<>br/";
  if($DOMNode->hasChildNodes()){
    foreach ($DOMNode->childNodes as $DOMElement) {
//print_r($DOMElement);
      if($DOMElement->hasAttributes()){
        $id=$DOMElement->getAttribute("id");
        if($id){
          $DOMElement->setIdAttribute("id",$id);
//echo $DOMElement->tagName . " " . $id . $DOMElement->setIdAttribute("id",$id) . "<br/>";
            $plainsvgid = $id;
			$plainsvgelement = $DOMElement->tagName;
        }
      }
      setAllId($DOMElement);
    }
  }
}


function  backupDownloadObject($url, $lfn)
{
    // backup fileget use file get contents as the CURL failed
//echo("<br/>backup file retrieval invoked for: ". $url. "<br>");
//echo("<br/>backup file saved as: ". $lfn. "<br>");

$context = stream_context_create(
    array(
        'http' => array(
            'follow_location' => false
        )
    )
);

$url = str_replace(".tmp","",$url);

$backupfile = file_get_contents($url, false, $context);
file_put_contents($lfn,$backupfile);

}
?>
