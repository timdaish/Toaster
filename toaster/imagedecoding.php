<?php


function hexDump($data, $newline)
{
  static $from = '';
  static $to = '';

  static $width = 16; # number of bytes per line

  static $pad = '.'; # padding for non-visible characters

  if ($from==='')
  {
    for ($i=0; $i<=0xFF; $i++)
    {
      $from .= chr($i);
      $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
    }
  }

  $hex = str_split(bin2hex($data), $width*2);
  $chars = str_split(strtr($data, $from, $to), $width);

  $offset = 0;
  foreach ($hex as $i => $line)
  {
    echo sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']' . $newline;
    $offset += $width;
  }
}


function getBytesFromFile($lf,$pos,$noofBytes)
{
	global $byteArray;
		
	$file = new SplFileObject($lf);
	
	// Move back to the beginning of the file
	// Same as $file->rewind();
	$file->fseek($pos,SEEK_SET );

	$bytecount = 0;
	while (($char = $file->fgetc()) !== false and $bytecount < $noofBytes) {
    	//echo "$char\n";
		$byteArray[$bytecount] = bin2hex($char);
		//echo (bin2hex($byteArray[$bytecount]));
		$bytecount =  $bytecount + 1;
	}
}



function getStringOfNoofBytes($pos,$noofBytes)
{
	global $byteArray;
	$str = '';
	$hexstr = '';
	$endbyte = $pos + $noofBytes - 1;
	for ($i = $pos; $i <= $endbyte; $i++)
	 {
    	@$hexstr .= $byteArray[$i];
	
	}	
	$str = hex2str($hexstr);
//echo("Hex: $hexstr = string: $str<br/>");
	return array($hexstr,$str);
}
function getStringOfNoofBytesLittleEndian($pos,$noofBytes)
{
	global $byteArray;
	$str = '';
	$hexstr = '';
	$endbyte = $pos + $noofBytes - 1;
	for ($i = $endbyte; $i >= $pos; $i--)
	 {
    	$hexstr .= $byteArray[$i];

	}	
	$str = hex2str($hexstr);
	//echo("Hex: $hexstr = string: $str<br/>");
	return array($hexstr,$str);
}
function getDecimalOfNoofBytes($pos,$noofBytes)
{
	global $byteArray;
	$str = '';
	$hexstr = '';
	$endbyte = $pos + $noofBytes - 1;
	for ($i = $pos; $i <= $endbyte; $i++)
	 {
    	$hexstr .= $byteArray[$i];
	
	}	
	$str = hexdec($hexstr);
	//echo("Hex: $hexstr = Dec: $str<br/>");
	return array($hexstr,$str);
}
function getDecimalOfNoofBytesLittleEndian($pos,$noofBytes)
{
	global $byteArray;
	$str = '';
	$hexstr = '';
	$endbyte = $pos + $noofBytes - 1;
	for ($i = $endbyte; $i >= $pos; $i--)
	 {
    	$hexstr .= $byteArray[$i];
	
	}	
	$str = hexdec($hexstr);
	//echo("Hex: $hexstr = Dec: $str<br/>");
	return array($hexstr,$str);
}

function getXMLfromFile()
{
	global $byteArray;
	$str = '';
	$hexstr = '';
	$sz = count($byteArray);
	for ($i = 0; $i <$sz; $i++)
	 {
    	$hexstr .= $byteArray[$i];
	}	
	$str = hex2str($hexstr);
	
	//echo("Hex: $hexstr = string: $str<br/>");
	$xml = simplexml_load_string($str);
	return $xml;
}


function getMimeTypeFromImageSignature($lf)
{
	global $objcountimg;
	$truemimetype = '';
	debug("checking true mime type",$lf);
//echo(__FUNCTION__ . " checking true mime type: ".$lf."<br/>");
	
	// load byte array with 16 bytes from file
	getBytesFromFile($lf,0,16);
	
	// Check for GIF Header
	list($bytesHDR,$bytestr) = getStringOfNoofBytes(0,3);
	if (strtoupper($bytesHDR) == '474946')
	{
		// a valid GIF file
		list($bytesSig,$file_signature) = getStringOfNoofBytes(3,3);
		$truemimetype = 'image/gif';
		$objcountimg = $objcountimg + 1;
		//echo("$lf: GIF file signature found: "."$file_signature<br/>");
	}
	else
	{
		//Check for PNG Header
		//echo("PNG file header: ".$file_hdr."<br/>");
		list($bytesHDR,$bytestr) = getStringOfNoofBytes(0,8);
		if (strtoupper($bytesHDR) == strtoupper('89504e470d0a1a0a'))
		{
			// a valid PNG file
			list($bytesSig,$file_signature) = getStringOfNoofBytes(3,3);
			$truemimetype = 'image/png';
			$objcountimg = $objcountimg + 1;
			//echo("$lf: PNG file signature found"."<br/>");
		}
		else
		{
			//Check for JPEG Header
			list($bytesHDR,$bytestr) = getStringOfNoofBytes(0,2);
			list($bytesSig,$file_signature) = getStringOfNoofBytes(2,2);
			if (strtoupper($bytesHDR) == strtoupper('ffd8'))
			{
				// a valid JPEG file
				$truemimetype = 'image/jpeg';
				$objcountimg = $objcountimg + 1;
				//echo("$lf: JPEG file signature found<br/>");
			}
			else
			{
				// WEBP Header
				list($file_hdr1,$bytestr) = getStringOfNoofBytes(0,4); // 4bytes
				list($file_hdr2,$bytestr) = getStringOfNoofBytes(8,4); // 4bytes
				list($file_hdr3	,$bytestr) = getStringOfNoofBytes(12,3); // 3bytes
				
				if (strtoupper($file_hdr1) == strtoupper('52494646') and strtoupper($file_hdr2) == strtoupper('57454250'))
				{
					//  a valid WEBP file
					$file_signature = 'RIFF-WEBP';
					$truemimetype = 'image/webp';
					$objcountimg = $objcountimg + 1;
					//echo("$lf: WEBP file signature found<br/>");
				}
				else
				{
					// JPG2000 Header
					list($file_hdr1,$bytestr) = getStringOfNoofBytes(0,8); // 8bytes	
					list($file_hdr2,$bytestr) = getStringOfNoofBytes(8,4); // 4bytes
					//echo("checking $lf for JPG2000 file signature with: ".$file_hdr1.$file_hdr2."<br/>");
					
					//89504e470d0a1a0a0000000d
					if (strtoupper($file_hdr1) == strtoupper('0000000c6a502020') and strtoupper($file_hdr2) == strtoupper('0d0a870a'))
					{
						//  a valid JP2 file
						$file_signature = 'JPEG2000';
						$truemimetype = 'image/jp2';
						$objcountimg = $objcountimg + 1;
						//echo("$lf: JPG2000 file signature found<br/>");
					}
					else
					{
						//check for SVG XML file
						//list($file_hdr1,$bytestr) = getStringOfNoofBytes(0,32); // 8bytes
						//list($file_hdr1,$bytestr) = getStringOfNoofBytes(0,128); // 8bytes

						//echo("$lf: Image's true Mime Type: INVALID IMAGE<br/>");
						//echo($file_hdr1."<br/>");
						//echo("svg file?<pre>");
						//print_r($array);
						//echo("</pre>");

                        // check for BMP
                        list($file_hdr1,$bytestr) = getStringOfNoofBytes(0,2); // 2bytes
                        if (strtoupper($file_hdr1) == strtoupper('424D'))
    					{
    						//  a valid Windows BMP file
    						$file_signature = 'BMP';
    						$truemimetype = 'image/x-ms-bmp';
    						$objcountimg = $objcountimg + 1;
    						//echo("$lf: Windows or OS/2 BMP file signature found<br/>");
    					}

						
						
					} //not a JPEG2000
				} //not a WEBP 
			} // not a JPEG
		} // not a PNG
	} // end not a GIF
	
		

	//echo("$lf: Image's true Mime Type: ".$truemimetype."<br/>");
	return($truemimetype);
}





function decodeJPG2000($content,$lf)
{

	$hexstring = unpack("H*", $content);
	$content = implode($hexstring);

	// JPG2000 Header
	list($file_hdr1,$bytestr) = getStringOfNoofBytes(0,8); // 8bytes
	list($file_hdr2,$bytestr) = getStringOfNoofBytes(8,4); // 4bytes

	//echo("checking for JPG2000 file signature with: ".$file_hdr1.$file_hdr2."<br/>");
	
	if (strtolower($file_hdr1) == '0000000c6a502020' and strtolower($file_hdr2) == '0d0a870a')
	{
		//  a valid JPG2000 file
		$file_signature = 'JPEG2000';
		$truemimetype = 'image/jp2';
		//echo("JPG2000 file signature found: ".$file_hdr1.$file_hdr2."<br/>");
	}


	//The type of the JPEG 2000 Signature box shall be 'jP\040\040' (0x6A50 2020). The length of this box shall be 12 bytes. 
	//The contents of this box shall be the 4-byte character string '<CR><LF><0x87><LF>' (0x0D0A 870A). For file 
	//verification purposes, this box can be considered a fixed-length 12-byte string which shall have the value: 
	//0x0000 000C 6A50 2020 0D0A 870A. 

	
	
	$jp2_signature = hex2str($file_hdr2); //ok
	
	//echo("JPG2000: ".$jp2_signature."<br/>");
	
	$imageencoding = '7';
	$arr = array($file_signature,$imageencoding);
	
	return($arr);
	
}



function decodeWEBP($content,$lf)
{
	global $OS;
	//echo ("Decoding RIFF WEBP:". $lf ."<br/>");
	
	$hexstring = unpack("H*", $content);	
	$content = implode($hexstring);

	// WEBP Header
	list($file_hdr1,$bytestr1) = getStringOfNoofBytes(0,4); // 4bytes
	list($file_hdr2,$bytestr2) = getStringOfNoofBytes(8,4); // 4bytes
	list($file_hdr3	,$bytestr3) = getStringOfNoofBytes(12,4); // 3bytes
				
	if ($file_hdr1 == '52494646' and $file_hdr2 == '57454250')
	{
		//  a valid WEBP file
		
		$metadatabytes = 0;
		$mdbytesICC = 0;
		$mdbytesXMP = 0;
		
		$truemimetype = 'image/webp';
		//echo("WEBP file signature found: ".$file_hdr1.$file_hdr2.$file_hdr3."<br/>");
		
		$file_signature = $bytestr2; //ok
		$webp_encoding = $bytestr3; //ok

		// get basic info using Googles webpmux
		$res = array();
        if($OS == "Windows")
		    exec('win_tools\webpmux -info '. escapeshellarg($lf),$res);
        else
            exec('./lnx_tools/webpmux -info '. escapeshellarg($lf),$res);
		$resstr = implode($res);
		$rescnt = count($res);
		//echo ("RIFF WEBP ($rescnt): $lf<br/><pre>");
		//print_r($res);
		//echo("</pre>");
		
		if($bytestr3 == 'VP8 ' || $bytestr3 == 'VP8L')
		{
			// simple format
			$file_signature .= ' Simple';
			
			//echo("webp $lf Simple format: $webp_encoding <br/>");
			$canvassize = substr($res[0],13);

			
		}
		else
		{
			if($bytestr3 == 'VP8X')
			{
			// VP8X - extended format	
			$file_signature .= ' Extended';
			
			//echo ("RIFF WEBP: extended ". $resstr." with metadata found <br/>");

			$fp = strpos($resstr,'Features');
			$canvassize = substr($res[0],13);
			
			for($i = 1; $i < $rescnt; $i++)
			{
				//echo("checking metadata $i: ". $res[$i]."<br/.");
				
				$md = explode(':',$res[$i]);
				$mdtype = $md[0];
				$mdsize = $md[1];
				
				//echo("checking metadata: ". $mdtype. " - " .$mdsize."<br/>");
				switch($mdtype)
				{
					case 'Size of the XMP metadata':
						$mdbytesXMP= $mdsize;
						$metadatabytes += $mdsize;
						break;

						
					case 'Size of the ICC profile data':
						$mdbytesICC += $mdsize;
						$metadatabytes += $mdsize;
						break;
						
					default:
						//echo("checking metadata: ". $mdtype. " - " .$mdsize."<br/>");
				}
			}
			
			
			
			}
			else
			{
				// invalid
				
			}
				
		} // end for each of simple and extended formats
		
		

	// check format	
		

	}

	
	$imageencoding = $webp_encoding;
	$arr = array($file_signature,$imageencoding,$canvassize,$metadatabytes,$mdbytesICC,$mdbytesXMP);
	
	return($arr);
	
}

function decodeGIFInfo($content,$lf)
{
	$gif_structure = '';
    $lengthofcontent = strlen($content);
	$hexstring = unpack("H*", $content);
	$content = implode($hexstring);
	//print_r("<br/>".$lf.": ".$content);

	//echo("<br/><br/>"."Analysing GIF file: ".$lf."<br/>");
    debug("Analysing GIF file: ",$lf);
	// work through segments in GIF file
	// 2 bytes segment header
	// 2 bytes length of segment
	// rest of data


	//GIF Header
	list($bytesHDR,$bytestr) = getStringOfNoofBytes(0,3);
	if ($bytesHDR == '474946')
	{
		// a valid GIF file
		list($bytesSig,$file_version) = getStringOfNoofBytes(3,3);
		$gif_signature = $bytestr.$file_version; //ok
	}
	$gif_structure = $gif_structure . "Header: $gif_signature<br/>";
	//echo($gif_signature."<br/>");
    debug('GIF signature',$gif_signature);

	$imageencoding = "Lempel-Ziv-Welch";
	$mdbytesize = 0;
	$noofFrames = 0;
	$comment_text = '';
	$comment_bytes = 0;
	$animation = '';
	$lswdec = 0;
	$lshdec = 0;
	$colresdec = '';
	$animation = '';
    $gifxmp = '';
    $gifxmpbytes = 0;
	/*
	GIF Header

	Offset   Length   Contents
	  0      3 bytes  "GIF"
	  3      3 bytes  "87a" or "89a"
	  6      2 bytes  <Logical Screen Width>
	  8      2 bytes  <Logical Screen Height>
	 10      1 byte   bit 0:    Global Color Table Flag (GCTF)
					  bit 1..3: Color Resolution
					  bit 4:    Sort Flag to Global Color Table
					  bit 5..7: Size of Global Color Table: 2^(1+n)
	 11      1 byte   <Background Color Index>
	 12      1 byte   <Pixel Aspect Ratio>
	 13      ? bytes  <Global Color Table(0..255 x 3 bytes) if GCTF is one>
			 ? bytes  <Blocks>
			 1 bytes  <Trailer> (0x3b)
	*/


	// Logical Screen Descriptor
	$offset = 6;
	$blocklength = 7;
	getBytesFromFile($lf,$offset,$blocklength);
	list($lsdbytes,$lsdbytestr) = getStringOfNoofBytesLittleEndian(0,7);
	$gif_structure = $gif_structure . "Logical Screen Descriptor: $blocklength bytes<br/>";

	list($lsw,$lswdec) = getDecimalOfNoofBytesLittleEndian(0,2);
	list($lsh,$lshdec) = getDecimalOfNoofBytesLittleEndian(2,2);
	list($bitflag,$bitflagdec) = getDecimalOfNoofBytesLittleEndian(4,1);
	$bits = decbin($bitflagdec);

	//echo("_LSD width: ".$lswdec."<br/>");
	//echo("_LSD height: ".$lshdec."<br/>");


    $bitspadded = str_pad($bits, 8, '0', STR_PAD_LEFT);
    debug("bits of lsd: ". $bitspadded,"");
    //echo("_LSD bits: ".$bits."<br/>");

	$globalColorTableFlag = substr($bitspadded,0,1);
	$colorResolution = substr($bitspadded,1,3);
	$colresdec = bindec($colorResolution);
	$colres = pow(2,$colresdec + 1);
	$sortFlagGCT = substr($bitspadded,4,1);
	$sizeGCT = substr($bitspadded,5,3);
	$sizeGCTdec = bindec($sizeGCT);
	$bytesGCT = 3 * pow(2,$sizeGCTdec + 1);

	list($backgroundColorIndex,$bci) = getDecimalOfNoofBytesLittleEndian(5,1);
	list($pixelAspectRatio,$par) = getDecimalOfNoofBytesLittleEndian(6,1);

	// calculate aspect ratio
	$aspectRatio = ($par + 15) / 64;

	//echo("Pixel Aspect ratio: $par => $aspectRatio <br/>");
	//echo("GCT Flag: ".$globalColorTableFlag ."<br/>");
    debug("GCT Flag: ",$globalColorTableFlag,"");
	if ($globalColorTableFlag == 1)
	{
		$gctFlag = true;
		//echo("GCT Background Colour Index: ".$bci ."<br/>");
		//echo("GCT Colour Resolution: $colorResolution => $colres bytes<br/>");
		//echo("Global Colour Table: $sizeGCT => $bytesGCT bytes<br/>");
        debug("Global Colour Table:", $sizeGCT ." => ". $bytesGCT ." bytes");
		$gif_structure = $gif_structure."Global Colour Table: $sizeGCT => $bytesGCT bytes<br/>";

		// get GCT of $bytesGCT bytes
		$blocklength = $bytesGCT;
		getBytesFromFile($lf,$offset,$blocklength);
		list($gct,$gctstr) = getStringOfNoofBytes(0,$bytesGCT);
		//echo("_LSD: Global Colour Table size: $blocklength<br/>");

	}
	else
	{
		$gctflag = false;
		$blocklength = 0;
        debug("No Global Colour Table:", "");
        //echo("No Global Colour Table:<br/>");
	}

	$offset = 13;
	$offset = $offset + $blocklength ;
	getBytesFromFile($lf,$offset,3);
	// debug
   	//echo "offset first block=$offset<br/>";
	list($bytes,$bytesstr) =getStringOfNoofBytes(0,3);
	//echo "$bytes <br/>"; // debug


	// loop through image descriptor and other blocks if this is not a 1x1 image
	if($lshdec == 1 and $lswdec == 1)
	{
		$blockfound = false;
		//echo("1x1 px image - skipping detailed analysis<br/>");
	}
	else
		$blockfound = true;

	$noofimgdescriptors = 0;
	$interlace = 'Non-Interlaced';

    $appextdatastr = '';




    //echo("GIF Structure (before detailed analysis loop): <br/>".$gif_structure);
    //echo("starting loop: <br/>");
    debug("starting data loop at offset",$offset,"");

	while($blockfound == true):

        // header
		list($blockheader,$blockhdrstr) = getStringOfNoofBytes(0,1);
		list($extheader,$exthdrstr) = getStringOfNoofBytes(1,1);
		list($extsize,$extsizedec) = getDecimalOfNoofBytes(2,1);

		//if($extsizedec == 0)
		//{
		//	debug($lf,'zero block size');
		//	$arr = array('','','','','','','','','','','');
		//	return($arr);
		//}

		// debug
		//debug("Offset $offset: Block header", $blockheader ." ($extheader)"); // debug
        debug("Offset", $offset,"");
        debug("Block header", $blockheader,$extheader);

		switch(strtolower($blockheader))
		{
			case '2c':
				// image descriptor
				$noofimgdescriptors = $noofimgdescriptors + 1;
				//echo("Image Descriptor ($noofimgdescriptors): <br/>");
                debug("Image Descriptor frame number: ", $noofimgdescriptors,"");
				$gif_structure = $gif_structure."Image Descriptor ($noofimgdescriptors): 10 bytes<br/>";

                $blocklength = 10;

				getBytesFromFile($lf,$offset,$blocklength);
				// debug
				list($bytes,$bytesstr) =getStringOfNoofBytes(0,10);
				//echo "_ID: $bytes <br/>"; // debug
				// get local color table if it is present

                list($il,$isdec) = getDecimalOfNoofBytesLittleEndian(0,1);
				list($il,$ildec) = getDecimalOfNoofBytesLittleEndian(1,2);
				list($it,$itdec) = getDecimalOfNoofBytesLittleEndian(3,2);
				list($iw,$iwdec) = getDecimalOfNoofBytesLittleEndian(5,2);
				list($ih,$ihdec) = getDecimalOfNoofBytesLittleEndian(7,2);

				list($bitflag,$bitflagdec) = getDecimalOfNoofBytesLittleEndian(9,1);
				$bits = decbin($bitflagdec);
                $bitspadded = str_pad($bits, 8, '0', STR_PAD_LEFT);

                debug ("imgdata sep: 2c hex = dec 44 ; found dec: ",$isdec);
				debug ("imgdata width: ",$iwdec);
				debug ("imgdata height: ",$ihdec);
				debug ("imgdata bits: ",$bitspadded);

				$localColorTableFlag = substr($bitspadded,0,1);
				$interlaceflag = substr($bitspadded,1,1);
				$sortFlagLCT = substr($bitspadded,2,1);
				$sizeLCT = substr($bitspadded,5,3);
				$sizeLCTdec = bindec($sizeLCT);
				$bytesLCT = 3 * pow(2,$sizeLCTdec + 1);

                $offset = $offset + $blocklength - 1;

				debug ("LCT Flag: ",$localColorTableFlag);
				if ($localColorTableFlag == 1)
				{
					$lctFlag = true;
                    debug ("imgdata lct bytes ",$bytesLCT);
					//echo("Local Colour Table: $sizeGCT => $bytesLCT bytes<br/>");
					$gif_structure = $gif_structure."Local Colour Table: $sizeLCT => $bytesLCT bytes<br/>";

					// get LCT of $bytesLCT bytes

					$blocklength = $bytesLCT;
					//echo "_lct offset=$offset<br/>";
					getBytesFromFile($lf,$offset,$blocklength);
					list($lct,$lctstr) = getStringOfNoofBytes(0,$blocklength);
                    debug ("imgdata lct offset ",$offset);
					//echo("LCT: $lct<br/>");
                    $offset = $offset + $blocklength;
				}
				else
				{
					$lctflag = false;
                    $blocklength = 0;
					$offset = $offset + $blocklength;
                    debug ("imgdata no lct , offset ",$offset);
				}


				// now looking at imagedata at offset
				// loop for an unknown number of blocks


                $noofimgdatablocks = 1;
                $offset = $offset + 1;

                debug ("imgdata offset ",$offset);
				getBytesFromFile($lf,$offset,2);
				list($lzwmincodesize,$lzwmincodesizedec) = getDecimalOfNoofBytes(0,1);
				list($datasubblocksize,$datasubblocksizedec) = getDecimalOfNoofBytes(1,1);
                debug ("start of data block - lzw code",$lzwmincodesizedec ." at offset ".$offset);
                $offset = $offset + 1;

                debug ("start of data block - size",$datasubblocksizedec ." at offset ".$offset);

                $lzwmincodesize = $lzwmincodesizedec;
                $lzwclearcode = $lzwmincodesize + 1;

                //echo "_imgdata<br/>";
				$imgdatablockfound = true;
				while(  $imgdatablockfound == true):
                    $blocklength = $datasubblocksizedec;
                    debug ("imgdata block - lzw code",$lzwmincodesizedec ." at offset ".$offset);
    				debug ("imgdata block ". $noofimgdatablocks. " at offset ".$offset, "of length: ".$blocklength);
					getBytesFromFile($lf,$offset,$blocklength);

                    // no data to actually get here
 					$offset = $offset + $blocklength;


                     // get block and check for a trailer
                    getBytesFromFile($lf,$offset,1);

                    list($ext,$extstr) = getStringOfNoofBytes(0,1);
                    debug("hex at offset ", $offset, $extstr);
                    if($ext == '00') // 00
                    {
                        debug("image 2C TRAILER at offset ", $offset);

						$imgdatablockfound = false;
						echo "imgdata block terminator 00 found: $offset <br/>";
						$offset = $offset + 1;
					}
					else
					{
						getBytesFromFile($lf,$offset,1);
						list($lzwcodesize,$lzwcodesizedec) = getDecimalOfNoofBytes(0,1);

                        if($lzwcodesizedec == $lzwclearcode)
                        {

                        }
                        else
                        {

                        }

                        //$offset = $offset + 1;
				        list($datasubblocksize,$datasubblocksizedec) = getDecimalOfNoofBytes(1,1);
                        $offset = $offset + 1;



					}

					$noofimgdatablocks = $noofimgdatablocks + 1;
				endwhile;



				// if this is the first imgdescriptor, set the interlace mode
				if($noofimgdescriptors == 1)
				{
					if($interlaceflag == true)
						$interlace = "Progressive";
					else
						$interlace = "Non-Interlaced";
				}

 ////////////////////////////////////////////////////// END HERE /////////////////////////////

	$arr = array($interlace,$gif_structure);

	return($arr);
 ////////////////////////////////////////////////////// END HERE /////////////////////////////
				$noofFrames = $noofFrames + 1;
				break;

			case '21': // extension
                $offset = $offset + 3;
				switch(strtolower($extheader))
				{
					case 'f9';
						// graphics control extension
						//echo("Graphics Control Extension 21f9: $extsizedec bytes<br/>");
                        debug("Graphics Control Extension 21f9", $extsizedec." bytes");
						$gif_structure = $gif_structure."Graphics Control Extension: $extsizedec bytes<br/>";


						getBytesFromFile($lf,$offset ,$extsizedec);
						list($ext,$extstr) = getStringOfNoofBytes(0,$extsizedec);
						//echo("_gce: $ext<br/>");
                        //echo("_appext 21f9: app: $extstr<br/>");

						$blocklength = $extsizedec;
                        $offset = $offset + $blocklength;

                        // block trailer of one byte

                        getBytesFromFile($lf,$offset ,1);
                        list($ext,$extstr) = getStringOfNoofBytes(0,1);
                        if($extstr == '00')
                        {
                            debug("Graphics Control Extension 21f9 TRAILER at offset ", $offset);
                        }
                        $offset = $offset + 1;

						break;

					case '01';
						// Plain Text Extension
						//echo("Plain Text Extension 2101: $extsizedec bytes<br/>");
                        debug("Plain Text Extension 2101", $extsizedec." bytes<br/>");
						$gif_structure = $gif_structure."Plain Text Extension 2101: $extsizedec bytes<br/>";

						getBytesFromFile($lf,$offset,$extsizedec);
					    list($txt,$txtstr) = getStringOfNoofBytes(0,$extsizedec);
						//echo ("_txt: ".$txtstr."<br/>");
                        //echo("_appext 2101: app: $extstr<br/>");

						$comment_text .= $txtstr."<br/>";
						$comment_bytes = $comment_bytes + $extsizedec;

						$blocklength = $extsizedec;
                        $offset = $offset + $blocklength;

                        // block trailer of one byte
                        getBytesFromFile($lf,$offset ,1);
                        list($ext,$extstr) = getStringOfNoofBytes(0,1);
                        if($extstr == '00')
                        {
                            debug("Plain Text Extension 2101 TRAILER at offset ", $offset);
                        }
                        $offset = $offset + 1;

						break;

					case 'fe';
						// Comment Extension
						//echo("Comment Extension 21fe: $extsizedec bytes<br/>");
                        debug("Comment Extension 21fe", $extsizedec." bytes<br/>");
						$gif_structure = $gif_structure."Comment Extension 21 fe: $extsizedec bytes<br/>";

						getBytesFromFile($lf,$offset,$extsizedec);
					    list($txt,$txtstr) = getStringOfNoofBytes(0,$extsizedec);
						//echo ("$lf: GIF_comment: ".$txtstr."<br/>");
                        //echo("_appext 21fe: app: $extstr<br/>");

						$comment_text .= $txtstr."<br/>";
						$comment_bytes = $comment_bytes + $extsizedec;

						$blocklength = $extsizedec;
                        $offset = $offset + $blocklength;

                        // block trailer of one byte
                        getBytesFromFile($lf,$offset ,1);
                        list($ext,$extstr) = getStringOfNoofBytes(0,1);
                        if($extstr == '00')
                        {
                            debug("Comment Extension 21fe TRAILER at offset ", $offset);
                        }
                        $offset = $offset + 1;
						break;

					case 'ff':
						// Application Extension
						//echo("Application Extension 21ff: $extsizedec bytes<br/>");
                        debug("Application Extension 21ff", $extsizedec." bytes<br/>");
						getBytesFromFile($lf,$offset ,$extsizedec);
						list($ext,$extstr) = getStringOfNoofBytes(0,$extsizedec);
						debug ("_appext 21ff: app: ",$extstr.": length: ".$extsizedec);

                        $gif_structure = $gif_structure."Application Extension 21ff: $extstr<br/>";

                        $offset = $offset + $extsizedec - 1;


                        $noofimgdatablocks = 1;

                        debug ("extdata offset ",$offset);
        				getBytesFromFile($lf,$offset,2);
        				list($lzwmincodesize,$lzwmincodesizedec) = getDecimalOfNoofBytes(0,1);
        				list($datasubblocksize,$datasubblocksizedec) = getDecimalOfNoofBytes(1,1);
        				$offset = $offset + 1;
        				debug ("start of data block - size",$datasubblocksizedec ." at offset ".$offset);
                        $offset = $offset + 1;

                        //echo "_imgdata<br/>";
        				$imgdatablockfound = true;
        				while(  $imgdatablockfound == true):
                            $blocklength = $datasubblocksizedec;

            				debug ("extdata block ". $noofimgdatablocks. " at offset ".$offset, "of length: ".$blocklength);
        					getBytesFromFile($lf,$offset,$blocklength);

                            // no data to actually get here
         					$offset = $offset + $blocklength;


                             // get block and check for a trailer
                            getBytesFromFile($lf,$offset,1);

                            list($ext,$extstr) = getStringOfNoofBytes(0,1);
                            debug("hex at offset ", $offset, $ext);
                            if($ext == '00') // 00
                            {
                                debug("Application Extension 21ff TRAILER at offset ", $offset);

        						$imgdatablockfound = false;
        						echo "extdata block terminator 00 found: $offset <br/>";
						        $offset = $offset + 1;
        					}
        					else
        					{
            					getBytesFromFile($lf,$offset,2);
        						list($lzwmincodesize,$lzwmincodesizedec) = getDecimalOfNoofBytes(0,1);
                                $offset = $offset + 1;
        				        list($datasubblocksize,$datasubblocksizedec) = getDecimalOfNoofBytes(1,1);
                                $offset = $offset + 1;

        					}

        					$noofimgdatablocks = $noofimgdatablocks + 1;
        				endwhile;

                        //echo "final appext data = $appextdatastr<br/>";
                        if(substr($extstr,0,3) == 'XMP')
                        {
                            $gifxmp .= $extstr . $appextdatastr;
                            $gifxmpbytes = strlen($gifxmp);
                        }



						break;
				
				} // end switch extension
				break;
				
		} // end switch block header


		// get next block
		//if($blockheader != '2c')
       // {
		  //$offset = $offset + $blocklength; // allready added bytes for image data
  		// check for block trailer
  		//getBytesFromFile($lf,$offset,1);
  		//list($bytes,$bytesstr) =getStringOfNoofBytes(0,1);
        //  $offset = $offset + 1;
       // }

		if ($bytes == '3b' )
		{
			$blockfound = false;
			//echo "_image trailer 3B found: $offset <br/>";
		}
		else
		{
			getBytesFromFile($lf,$offset,3);
			// debug
			//echo "next block offset=$offset<br/>";
			//list($bytes,$bytesstr) =getStringOfNoofBytes(0,2);
			//echo "next block 2 bytes at offset: $bytes <br/>"; // debug

			switch(strtolower($blockheader))
			{
				case '2c':
				case '21':
                default: // block header can be continuation of an extension block
					$blockfound = true;
                    //echo "next block header=$blockheader<br/>";
					break;

				case "3b":

					$blockfound = false;
                    break;
			}
		}

	endwhile;

	//echo("<br/>GIF Structure after analysis: <br/>".$gif_structure);


/*
Image Block

Offset   Length   Contents
  0      1 byte   Image Separator (0x2c)
  1      2 bytes  Image Left Position
  3      2 bytes  Image Top Position
  5      2 bytes  Image Width
  7      2 bytes  Image Height
  8      1 byte   bit 0:    Local Color Table Flag (LCTF)
                  bit 1:    Interlace Flag
                  bit 2:    Sort Flag
                  bit 2..3: Reserved
                  bit 4..7: Size of Local Color Table: 2^(1+n)
         ? bytes  Local Color Table(0..255 x 3 bytes) if LCTF is one
         1 byte   LZW Minimum Code Size
[ // Blocks
         1 byte   Block Size (s)
        (s)bytes  Image Data
]*
         1 byte   Block Terminator(0x00)
Graphic Control Extension Block

Offset   Length   Contents
  0      1 byte   Extension Introducer (0x21)
  1      1 byte   Graphic Control Label (0xf9)
  2      1 byte   Block Size (0x04)
  3      1 byte   bit 0..2: Reserved
                  bit 3..5: Disposal Method
                  bit 6:    User Input Flag
                  bit 7:    Transparent Color Flag
  4      2 bytes  Delay Time (1/100ths of a second)
  6      1 byte   Transparent Color Index
  7      1 byte   Block Terminator(0x00)
Comment Extension Block

Offset   Length   Contents
  0      1 byte   Extension Introducer (0x21)
  1      1 byte   Comment Label (0xfe)
[
         1 byte   Block Size (s)
        (s)bytes  Comment Data
]*
         1 byte   Block Terminator(0x00)
Plain Text Extension Block

Offset   Length   Contents
  0      1 byte   Extension Introducer (0x21)
  1      1 byte   Plain Text Label (0x01)
  2      1 byte   Block Size (0x0c)
  3      2 bytes  Text Grid Left Position
  5      2 bytes  Text Grid Top Position
  7      2 bytes  Text Grid Width
  9      2 bytes  Text Grid Height
 10      1 byte   Character Cell Width(
 11      1 byte   Character Cell Height
 12      1 byte   Text Foreground Color Index
 13      1 byte   Text Background Color Index
[
         1 byte   Block Size (s)
        (s)bytes  Plain Text Data
]*
         1 byte   Block Terminator(0x00)
Application Extension Block

Offset   Length   Contents
  0      1 byte   Extension Introducer (0x21)
  1      1 byte   Application Label (0xff)
  2      1 byte   Block Size (0x0b)
  3      8 bytes  Application Identifire
[
         1 byte   Block Size (s)
        (s)bytes  Application Data
]*
         1 byte   Block Terminator(0x00)
	
	*/


	if ($noofFrames > 1)
	{
		//echo "<br/>$lf: Animated GIF: $noofFrames frames ($noofimgdatablocks datablocks)<br/>";
		$animation = 'Animated ('.$noofFrames.' frames)';
	}
	//else
		//echo "<br/>$lf: Non-Animated GIF<br/>";


	$mdbytesize = $mdbytesize + $comment_bytes + $gifxmpbytes;
	$arr = array($gif_signature,$imageencoding,$lswdec,$lshdec,$interlace,$colresdec,$mdbytesize,$comment_bytes,$comment_text,$gif_structure,$animation,$gifxmp,$gifxmpbytes);

	return($arr);
}


function decodePNGold($content,$lf)
{
	$hexstring = unpack("H*", $content);
	$content = implode($hexstring);
	//print_r("<br/>".$lf.": ".$content);	
	
	//echo("<br/>"."Analysing PNG file: ".$lf."<br/>");

	//A PNG file starts with an 8-byte signature:[9]//
	//
	//Bytes	Purpose
	//89	Has the high bit set to detect transmission systems that do not support 8 bit data and to reduce the chance that a text file is mistakenly interpreted as a PNG, or vice versa.
	//50 4E 47	In ASCII, the letters PNG, allowing a person to identify the format easily if it is viewed in a text editor.
	//0D 0A	A DOS-style line ending (CRLF) to detect DOS-Unix line ending conversion of the data.
	//1A	A byte that stops display of the file under DOS when the command type has been used—the end-of-file character
	//0A	A Unix-style line ending (LF) to detect Unix-DOS line ending conversion.
	
	
	//PNG Header
	list($bytesHDR,$bytestr) = getStringOfNoofBytes(0,8);
	if ($bytesHDR == '89504e470d0a1a0a')
	{
		// a valid PNG file
	}
	$png_signature =  substr($bytestr,1,3); //

	$l = strlen($bytestr);
	//echo("PNG bytestr length: ".$bytestr.": $l<br/>");
	//echo("PNG file sig: ".$png_signature."<br/>");
	



	// a number of chunks
	
	//A chunk consists of four parts:
	// length (4 bytes)
	// chunk type/name (4 bytes)
	// chunk data (length bytes)
	// CRC (cyclic redundancy code/checksum; 4 bytes)


	$png_structure ='';
	$chunk_start = 8;
	getBytesFromFile($lf,$chunk_start,8);
	
	$chunk_length_bytes_start = 0;
	$chunk_type_bytes_start = 4;
	

	list($chunk_length,$bytestr) = getStringOfNoofBytes($chunk_length_bytes_start,4); // 4bytes
	$chunk_length_dec = hexdec($chunk_length); // returns unsigned integer
	//echo("chunk length: $bytestr: ".$chunk_length. ": dec = ". "$chunk_length_dec bytes<br/>");	

	list($chunk_type,$type) = getStringOfNoofBytes($chunk_type_bytes_start,4); // 4bytes
	$png_structure = $png_structure . $type. ": ". "$chunk_length_dec bytes<br/>";
	if ($type == 'IHDR')  // must be first chunk in the file
	{
		//echo("chunk type found: ".$type. ": ". "$chunk_length_dec bytes<br/>");
		$chunkfound = true;
	}
	else
		$chunkfound = false;
	
	$mdbytesize = 0;
	$noofIDATchunks = 1;
	$comment_text = '';
	$comment_bytes = 0;
	$xmpbytes = 0;
	$xmpinfo = '';
	// iterate through the file looking for PNG chunks
	while($chunkfound === true ):
		//echo("chunk length: $bytestr: ".$chunk_length. ": dec = ". "$chunk_length_dec bytes<br/>");	
		// get chunk data of the given length in bytes
		getBytesFromFile($lf,$chunk_start + 8,$chunk_length_dec);
		list($chunk_data,$bytestr) = getStringOfNoofBytes(0,$chunk_length_dec); // x bytes from from the data array 
		//echo("chunk data hex: ".$chunk_data ."<br/>");
		


		// deal with chunk data 
		switch ($type)
		{
			case 'PLTE': // Colour Palette
			
				break;
			case 'IHDR': // image data
				$noofIDATchunks .= 1; // there may be more than one IDAT
				
				//echo("IHDR data hex: ".$chunk_data ."<br/>");

				//Width	4 bytes
				//Height	4 bytes
				//Bit depth	1 byte
				//Colour type	1 byte
				//Compression method	1 byte
				//Filter method	1 byte
				//Interlace method	1 byte
				

				list($width,$widthstr) = getDecimalOfNoofBytes(0,4); // 4bytes
				list($height,$heightstr) = getDecimalOfNoofBytes(4,4); // 4bytes
				list($bitdepth,$bitdepthstr) = getDecimalOfNoofBytes(8,1); // 1byte
				list($colourtype,$colourtypedec) = getDecimalOfNoofBytes(9,1); // 1byte
				list($compressionmethod,$compressionmethoddec) = getDecimalOfNoofBytes(10,1); // 1byte
				list($filtermethod,$filtermethodstr) = getDecimalOfNoofBytes(11,1); // 1byte
				list($interlacermethod,$interlacemethoddec) = getDecimalOfNoofBytes(12,1); // 1byte
				
				switch($colourtypedec)
				{
					case 0:
						$colourtypestr = 'Greyscale';
						break;
					case 2:
						$colourtypestr = 'Truecolour';
                        $bitdepth = $bitdepth * 3;
                        $bitdepthstr = $bitdepth;
						break;
					case 3:
						$colourtypestr = 'Indexed-colour';
						break;
					case 4:
						$colourtypestr = 'Greyscale with alpha';
						break;
					case 6:
						$colourtypestr = 'Truecolour with alpha';
                        $bitdepth = $bitdepth * 4;
                        $bitdepthstr = $bitdepth;
						break;
				}
				
				switch($compressionmethoddec)
				{
					case 0:
						$compressionmethodstr = 'Deflate LZ77';
						$imageencoding = "Deflate (LZ77 & Huffman coding)";
						break;
					case 1:
						$compressionmethodstr = 'unknown';
						$imageencoding = 'unknown';
						break;
				}
				
				switch($interlacemethoddec)
				{
					case 0:
						$interlacemethodstr = 'Non-Interlaced';
						break;
					case 1:
						$interlacemethodstr = 'Progressive';
						break;
				}
				
				//echo("_IHDR width and height: ".$widthstr." x " . $heightstr ."<br/>");
				//echo("_IHDR bit depth: ".$bitdepthstr."<br/>");
				//echo("_IHDR colour type: ".$colourtypestr."<br/>");
				//echo("_IHDR compression method: ".$compressionmethodstr."<br/>");
				//echo("_IHDR filter method: ".$filtermethodstr."<br/>");
				//echo("_IHDR interlace method: ".$interlacemethodstr."<br/>");


				
				//echo("chunk type found: ".$type. ": ". "$chunk_length_dec bytes<br/>");
				break;

			case 'IDAT': // image data
				break;
			case 'bKGD': // gives the default background color. It is intended for use when there is no better choice available, such as in standalone image viewers (but not web browsers; see below for more details).
				break;
			case 'cHRM': // gives the chromaticity coordinates of the display primaries and white point.
				break;
			case 'gAMA': // specifies gamma.
				break;
			case 'hIST': //  can store the histogram, or total amount of each color in the image.
				break;
			case 'iCCP': //  is an ICC color profile.
				break;

			case 'pHYs': // holds the intended pixel size and/or aspect ratio of the image.
				break;
			case 'sBIT': // (significant bits) indicates the color-accuracy of the source data.
				break;
			case 'sPLT': // suggests a palette to use if the full range of colors is unavailable.
				break;
			case 'sRGB': // indicates that the standard sRGB color space is used.
				break;
			case 'sTER': // stereo-image indicator chunk for stereoscopic images.[13]
				break;
			case 'tIME': // stores the time that the image was last changed.
				break;
			case 'tRNS': // contains transparency information. For indexed images, it stores alpha channel values for one or more palette entries. For truecolor and grayscale images, it stores a single pixel value that is to be regarded as fully transparent.
				break;

			case 'tEXt': // can store text that can be represented in ISO/IEC 8859-1, with one name=value pair for each chunk.
				// METADATA
				
				$textlength = $chunk_length_dec;
				list($text,$textstr) = getStringOfNoofBytes(0,$textlength); // 4bytes
				
				$pieces = explode("00", $text);

				$keyword = hex2str($pieces[0]);
				$text = hex2str($pieces[1]);
				//echo("_tEXt: ".$keyword.": '".$text."'<br/>");
				if(substr($keyword,0,3) == "XML")
				{
					//echo("_tEXt: XML found<br/>");
					$comment_text = $comment_text . $keyword.": [XML]";
					$xmpbytes = $xmpbytes + $chunk_length_dec;
					$xmpinfo = $xmpinfo + $pieces[1];
					//echo("_tEXt: XML found". $pieces[1]."<br/>");
				}
				else
				{
					$comment_text = $comment_text . $keyword.": ".$text."<br/>";
					$comment_bytes = $comment_bytes + $chunk_length_dec;
				}

				
				break;
			
			case 'iTXt': // contains UTF-8 text, compressed or not, with an optional language tag. iTXt chunk with the keyword 'XML:com.adobe.xmp' can contain Extensible Metadata Platform (XMP).
				$textlength = $chunk_length_dec;
				list($text,$textstr) = getStringOfNoofBytes(0,$textlength); // 4bytes
				
				$pieces = explode("00", $text);
				
				$keyword = hex2str($pieces[0]);
				$text = hex2str($pieces[1]);
				//echo("_ITXt: ".$keyword.": ".$text."<br/>");
				if(substr($keyword,0,3) == "XML")
				{
					//echo("_iTXt: XML found<br/>");
					$comment_text = $comment_text . $keyword.": [XML]";
					$xmpbytes = $xmpbytes + $chunk_length_dec;
					$xmpinfo = $xmpinfo + $pieces[1];
					//echo("_iTXt: XML found". $pieces[1]."<br/>");
				}
				else
				{
					$comment_text = $comment_text . $keyword.": ".$text."<br/>";
					$comment_bytes = $comment_bytes + $chunk_length_dec;
				}

				// METADATA
				
				break;			
			case 'zTXt': // contains compressed text with the same limits as tEXt.
				$textlength = $chunk_length_dec;
				list($text,$textstr) = getStringOfNoofBytes(0,$textlength); // 4bytes
				
				$pieces = explode("00", $text);
				
				$keyword = hex2str($pieces[0]);
				$text = hex2str($pieces[1]);
				//echo("_zTXt: ".$keyword.": ".$text."<br/>");
				if(substr($keyword,0,3) == "XML")
				{
					//echo("_zTXt: XML found<br/>");
					$comment_text = $comment_text . $keyword.": [XML]";
					$xmpbytes = $xmpbytes + $chunk_length_dec;
					$xmpinfo = $xmpinfo + $text;
				}
				else
				{
					$comment_text = $comment_text . $keyword.": ".$text."<br/>";
					$comment_bytes = $comment_bytes + $chunk_length_dec;
				}
				// METADATA
				break;			

				
			case 'IEND': // end marker
				//echo("chunk type found: ".$type. ": ". "$chunk_length_dec bytes<br/>");
				
				break;
		}

		// end this chunk
		// get CRC
		getBytesFromFile($lf,$chunk_start + 8 + $chunk_length_dec,4);
		
		//
		// get the next chunk
		$chunk_start = $chunk_start + 4 + 4 + $chunk_length_dec + 4;  //last start pos + 4length + 4 type + data length + 4 CRC	
		getBytesFromFile($lf,$chunk_start,8);

		list($chunk_length,$bytestr) = getStringOfNoofBytes($chunk_length_bytes_start,4); // 4bytes
		$chunk_length_dec = hexdec($chunk_length); 
		list($chunk_type,$type) = getStringOfNoofBytes($chunk_type_bytes_start,4); // 4bytes
		$png_structure = $png_structure . $type. ": ". "$chunk_length_dec bytes<br/>";

		switch ($type)
		{
			case 'IHDR': // Colour Palette
			case 'PLTE': // Colour Palette
			case 'IDAT': // image data
			case 'bKGD': // gives the default background color. It is intended for use when there is no better choice available, such as in standalone image viewers (but not web browsers; see below for more details).
			case 'cHRM': // gives the chromaticity coordinates of the display primaries and white point.
			case 'gAMA': // specifies gamma.
			case 'hIST': //  can store the histogram, or total amount of each color in the image.
			case 'iCCP': //  is an ICC color profile.
			case 'iTXt': // contains UTF-8 text, compressed or not, with an optional language tag. iTXt chunk with the keyword 'XML:com.adobe.xmp' can contain Extensible Metadata Platform (XMP).
			case 'pHYs': // holds the intended pixel size and/or aspect ratio of the image.
			case 'sBIT': // (significant bits) indicates the color-accuracy of the source data.
			case 'sPLT': // suggests a palette to use if the full range of colors is unavailable.
			case 'sRGB': // indicates that the standard sRGB color space is used.
			case 'sTER': // stereo-image indicator chunk for stereoscopic images.[13]
			case 'tEXt': // can store text that can be represented in ISO/IEC 8859-1, with one name=value pair for each chunk.
			case 'tIME': // stores the time that the image was last changed.
			case 'tRNS': // contains transparency information. For indexed images, it stores alpha channel values for one or more palette entries. For truecolor and grayscale images, it stores a single pixel value that is to be regarded as fully transparent.
			case 'zTXt': // contains compressed text with the same limits as tEXt.
				$chunkfound = true;
				//echo("chunk type found: ".$type. ": ". "$chunk_length_dec bytes<br/>");
				break;
				
			case 'IEND': // end marker
				//echo("chunk type found: ".$type. ": ". "$chunk_length_dec bytes<br/>");
				$chunkfound = false;
				break;
		}
			
			
	endwhile;

	//echo("<br/>PNG Structure:<br/>".$png_structure);
	
	//aggregate metadatabytes
	$mdbytesize = $mdbytesize + $comment_bytes + $xmpbytes;

	$arr = array($png_signature,$imageencoding,$png_structure,$widthstr,$heightstr,$bitdepthstr,$colourtypestr,$filtermethodstr,$interlacemethodstr,$mdbytesize,$comment_bytes,$comment_text,$xmpbytes);

	return($arr);


}



function decodeJPEG($content,$lf)
{
	global $OS,$perlbasedir;
	//  Analyse JPEG file and count metadata bytes, including EXIF, thumbnails,ICC profiles

	// convert from binary
	$hexstring = unpack("H*", $content);
	$content = implode($hexstring);

	//print_r("<br/>".$lf.": ".$content);	

	//echo("<br/>"."Analysing JPEG file: ".$lf."<br/>");
	
	// work through segments in jpeg file
	// 2 bytes segment header
	// 2 bytes length of segment
	// rest of data
	

	list($bytesHDR,$bytestr) = getStringOfNoofBytes(0,2);
	list($bytesSig,$file_signature) = getStringOfNoofBytes(2,2);
	if ($bytesHDR == 'ffd8') // and strpos($lf,"base64") != false
	{
		//echo("JPEG FILE: ".$bytesHDR."<br/>");
	}
	else
	{
		// invalid
		//echo("invalid JPEG FILE: ".$bytesHDR."<br/>");
		return;
	}
	
	// iterate through data for all segments in metadata after the JFIF header
	$segfound = true;
	$mdbytesize = 0;
	$seg_data_length_bytes = 0;
	$exifbytes = 0;
	$iptcbytes = 0;
	$xmpbytes = 0;
	$imgfiletype = 'JPEG';
	$imgfileencoding = '';
	$xmp_data = '';
	$comment_bytes = 0;
	$seg_comment = '';
	$xpixels=0;
	$ypixels=0;
	$density=0;
	$xmpinfo = '';
	$iccbytes = 0;
	$jpeg_substructure = '';
    $app12_ducky_quality = '';
	$app12size = 0;
	$chromasubsampling = '';
	$duckytext = '';
    $commentquality = '';

	$seg_start = 4;
	$seg_start_bytes = 2;
	$jpeg_structure = '';
	
	getBytesFromFile($lf,0,2);
	//$seg_hdr = substr($content, $seg_start, 4); // 2bytes
	$seg_start_bytes = 2;
	list($seg_hdr,$bytestr) = getStringOfNoofBytes($seg_start_bytes,2); // 2bytes
	$jpeg_structure = $jpeg_structure . "SOI". " (".$seg_hdr. "): ". "2 bytes<br/>";
	
	while($segfound === true && $seg_hdr <> ''):

		//echo("segment hdr: ".$seg_hdr."; seg_start: $seg_start<br/>");
		//echo("segment hdr: ".$seg_hdr."; seg_start_bytes: $seg_start_bytes<br/>");
		//$seg_data_size_hex = substr($content, $seg_start + 4, 4); // 2bytes after the hdr

		// load byte array with bytes from file
		getBytesFromFile($lf,$seg_start_bytes,4);
		list($seg_data_size_hex,$bytestr) = getStringOfNoofBytes(2,2); // 2bytes
		
		$seg_data_start = $seg_start + 8; // add 4bytes to $seg_start to get data start pos
		$seg_data_start_bytes = $seg_start_bytes + 4 ; // add 4bytes to $seg_start
		
		$seg_data_size_dec = hexdec($seg_data_size_hex); //ok
		//echo("loop seg $seg_hdr data size hex: ".$seg_data_size_hex." = $seg_data_size_dec dec<br/>"); //ok

		$seg_length = $seg_data_size_dec * 2; // length of data in chars
		$seg_length_bytes = $seg_data_size_dec - 2; // length of data in bytes
		// load byte array with bytes from file
		getBytesFromFile($lf,$seg_start_bytes + 2,$seg_length_bytes);
		
		
		$seg_data_length = $seg_length - 4; // subtract 2 bytes data length
		$seg_data_length_bytes = $seg_length_bytes; // subtract 2 bytes data length  
		
		$seg_data = substr($content, $seg_data_start, $seg_data_length);
		list($seg_data_bytes,$bytestr) = getStringOfNoofBytes(0,$seg_data_length_bytes); // data lsngth bytes
		
		
		//echo("loop segment data: ".$seg_data."<br/>");
		//echo("loop segment data: ".$seg_data_bytes."<br/>");

		
		$seg_data_end   = $seg_data_start + $seg_data_size_dec * 2;// allow for the 2x2bytes for length
		$seg_data_end_bytes   = $seg_data_start_bytes + $seg_data_size_dec;


		//$seg_data = substr($content, $seg_data_start, $seg_length - 4);

		//echo("seg length: ".$seg_length." chars<br/>");
		//echo("seg data length: ".$seg_data_length." bytes<br/>");
	
		$jpeg_substructure = '';
		switch($seg_hdr)
		{
			case 'ffd8':
				$seg_hdr_marker = 'SOI (Start of Image)';
				break;
			case 'ffd9':
				$seg_hdr_marker = 'EOI (End of Image)';
				break;
			
			case 'ffe0':
				$seg_hdr_marker = 'APP0 JFIF';
				
				// APP0 JFIF data
				$app0_data_size_hex   = $seg_data_size_hex;
				
				//echo("jfif data size hex: ".$app0_data_size_hex."<br/>");
				$app0_data_size_dec   = hexdec($app0_data_size_hex);
				//echo("jfif data size decimal: ".$app0_data_size_dec."<br/>");
				// load segment data
				getBytesFromFile($lf,$seg_start_bytes + 4,$app0_data_size_dec - 2);  // start after length, subtract 2 bytes for length
				
				//echo("jfif datastr: ".$seg_data."<br/>");
				
				list($jpegtype_identifier_bytes,$jpegtype_identifier) = getStringOfNoofBytes(0,5); // 5 bytes

				
				switch(trim($jpegtype_identifier))
				{

					case "JFIF":
						//echo("APP0 identifier: ".$jpegtype_identifier."<br/>");
						//echo("JFIF data size (bytes)l: ".$app0_data_size_dec."<br/>");
						list($jpegtype_v1_bytes,$jpegtype_v1) = getStringOfNoofBytes(5,1); // 1 byte
						list($jpegtype_v2_bytes,$jpegtype_v2) = getStringOfNoofBytes(6,1); // 1 byte
						
						$jfif_version   = $jpegtype_v1_bytes.".".$jpegtype_v2_bytes;
						//echo("JFIF version: ".$jfif_version."<br/>");
						$imgfiletype = "JPEG/JFIF v".$jfif_version;
						
						// jfif  units, xdensity, ydensity, xthumbnail,ythumbnail and RGB bytes
						list($unit,$unit) = getStringOfNoofBytes(7,1); // 2 bytes - x size
						list($xbytes,$xstr) = getStringOfNoofBytes(8,2); // 2 bytes - x size
						list($ybytes,$ystr) = getStringOfNoofBytes(10,2); // 2 bytes - y size
						$x_dec   = hexdec($xbytes);
						$y_dec   = hexdec($ybytes);
						switch($unit)
						{
							case 0:
								$unitstr = 'aspect ratio';
								//echo("Aspect Ratio: ".$x_dec .":" .$y_dec."<br/>");
								break;
							case 1:
								$unitstr = 'DPI';
								//echo("Image Density: ".$x_dec ."x" .$y_dec." ".$unitstr."<br/>");
								$density = $x_dec.$unitstr;
								break;
							case 2:
								$unitstr = 'DPCM';
								//echo("Image Density: ".$x_dec ."x" .$y_dec." ".$unitstr."<br/>");
								$density = $x_dec.$unitstr;
								break;
						}

						break;
					
					default:
						//echo("APP0 identifier: ".$jpegtype_identifier."<br/>");
						//echo("JFIF Extension data size (bytes): ".$app0_data_size_dec."<br/>");
					
				}
				
				// add to metadata cumulative size if JFIF block is more than the signature alone
				if($seg_data_length_bytes > 16)
					$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				
				break;
			case 'ffe1':
				$seg_hdr_marker = 'APP1';
			
				
				// EXIF, XMP, IPTC
				//echo("seg $seg_hdr data: ".$seg_data."<br/>");
				//echo("seg $seg_hdr data size dec: ".$seg_data_size_dec."<br/>");
				

				// check for EXIF tag
				$seg_app1_id =  substr($seg_data,0,12); // 12 chars in hexstring
				//echo("exif seg_app1_id: ".$seg_app1_id."<br/>");    
				
				if ($seg_app1_id == '457869660000') // 6 bytes identifier
				{
					//echo("Extracting EXIF Metadata in APP1<br/>");
					//echo("EXIF Metadata found in APP1<br/>");
					
					// set image file type if not a JFIF
					if ($imgfiletype == 'JPEG')
						$imgfiletype = "JPEG/EXIF";

					// EXIF
					$seg_app1_type = hex2str($seg_app1_id);
					$exifbytes = $exifbytes + $seg_data_length_bytes + 2;
					$seg_hdr_marker = "APP1 EXIF";

					// data may include EXIF thumbnail
					
					//	 [Record name]    [size]   [description]
					//	---------------------------------------
					//	Identifier       6 bytes   ("Exif\000\000" = 0x457869660000), not stored
					//	Endianness       2 bytes   'II' (little-endian) or 'MM' (big-endian)
					//	Signature        2 bytes   a fixed value = 42
					//	IFD0_Pointer     4 bytes   offset of 0th IFD (usually 8), not stored
					//	IFD0                ...    main image IFD
					//	IFD0@SubIFD         ...    Exif private tags (optional, linked by IFD0)
					//	IFD0@SubIFD@Interop ...    Interoperability IFD (optional,linked by SubIFD)
					//	IFD0@GPS            ...    GPS IFD (optional, linked by IFD0)
					//	APP1@IFD1           ...    thumbnail IFD (optional, pointed to by IFD0)
					//	ThumbnailData       ...    Thumbnail image (optional, 0xffd8.....ffd9)

					
					// TIFF HEADER
					$tiff_header_offset = $seg_start_bytes;
					getBytesFromFile($lf,$tiff_header_offset + 4,12); 
					list($Endianness,$strEndianness) = getStringOfNoofBytes(6,2); // bytes
					
					if($strEndianness == 'MM')
					{
						list($Signature,$decSignature) = getDecimalOfNoofBytes(8,2); // bytes
						list($IFD0_Pointer,$decIFD0_Pointer) = getDecimalOfNoofBytes(10,2); // bytes
					}
					else
					{
						list($Signature,$decSignature) = getDecimalOfNoofBytesLittleEndian(8,2); // bytes
						list($IFD0_Pointer,$decIFD0_Pointer) = getDecimalOfNoofBytesLittleEndian(10,2); // bytes
					}
					//echo ("IFD0: " . $strEndianness . "; " . $decSignature  . "; " . $decIFD0_Pointer."<br/.");
					
										

					$IFDoffset = $tiff_header_offset + $decIFD0_Pointer;
					// IFD0 Image File Directory
					getBytesFromFile($lf,$IFDoffset,4);  // no. of directory entries
					if($strEndianness == 'MM')
						list($NoofDirEntries,$decNoofDirEntries) = getDecimalOfNoofBytes(8,2); // bytes
					else
						list($NoofDirEntries,$decNoofDirEntries) = getDecimalOfNoofBytesLittleEndian(8,2); // bytes
					
					//loop for each dir entry
					for($ifdcount = 0; $ifdcount < $decNoofDirEntries; $ifdcount++)
					{
						getBytesFromFile($lf,$IFDoffset + 4 + ($ifdcount * 12),12);  // no. of directory entries
						
						
						if($strEndianness == 'MM')
						{
							list($TagNo,$decTagNo) = getDecimalOfNoofBytes(0,2); // 2 bytes
							list($DataFormat,$decDataFormat) = getDecimalOfNoofBytes(2,2); // 2 bytes
							list($NoofComponents,$decNoofComponents) = getDecimalOfNoofBytes(4,4); // 4 bytes
							list($DataValueOffset,$decDataValueOffset) = getDecimalOfNoofBytes(7,4); // 4 bytes
						}
						else
						{
							list($TagNo,$decTagNo) = getDecimalOfNoofBytes(0,2); // 2 bytes
							list($DataFormat,$decDataFormat) = getDecimalOfNoofBytes(2,2); // 2 bytes
							list($NoofComponents,$decNoofComponents) = getDecimalOfNoofBytes(4,4); // 4 bytes
							list($DataValueOffset,$decDataValueOffset) = getDecimalOfNoofBytes(8,4); // 4 bytes
						}
						
						//echo ("IFD0: DirEntry $ifdcount: tag no:" . $decTagNo . "; Data format: " . $decDataFormat  . "; Noof Components: " . $decNoofComponents."; DataVal Offset: " . $decDataValueOffset." (hex $DataValueOffset)<br/.");

					} // end loop for each dir entry
					
					getBytesFromFile($lf,$IFDoffset + 4 + ($ifdcount * 12),4);  // no. of directory entries
					if($strEndianness == 'MM')
						list($OffsetToNextIFD,$decOffsetToNextIFD) = getDecimalOfNoofBytes(0,4); // bytes
					else
						list($OffsetToNextIFD,$decOffsetToNextIFD) = getDecimalOfNoofBytes(0,4); // bytes
					

					// next IFD
					

					
					


				}  // end exif
				else
				{
					// search for XMP data
					//echo("Extracting XMP Metadata in APP1<br/>");
					
					//echo("seg $seg_hdr data: ".$seg_data."<br/>");
					//echo("seg $seg_hdr data: ".$seg_data_bytes."<br/>");
					
					//	    [Record name]    [size]   [description]
					//		---------------------------------------
					//		Identifier      29 bytes   http://ns.adobe.com/xap/1.0/
					//		<XMP packet>       ...     the actual Unicode XMP packet

					// search for XMP data
					
					//$xmp_data_start = strpos(strtolower($seg_data), "http://ns.adobe.com/xap/1.0/]");
					
					$xmp_data_start = strpos(strtolower($seg_data_bytes), "http://ns.adobe.com/xap/1.0/]"); // start identifier
					$identier_length = strlen("http://ns.adobe.com/xap/1.0/]") + 1;
					$xmp_data_end   = strpos(strtolower($seg_data_bytes), '3c3f787061636b657420656e64'); // end packet

					$xmp_length     = $xmp_data_end - $xmp_data_start;
					//echo("APP1_XMP id found in APP1: length= $xmp_length<br/>");
					//echo("XMP Metadata found<BR/>");
					$seg_hdr_marker = "APP1 XMP";

						
					getBytesFromFile($lf,$seg_start_bytes + 4 + 29,$xmp_length);  // start after length + 29 bytes for header
				
					//echo(utf8_decode(hex2str($seg_data_bytes)). "<br/>");
					//$xmpinfo .= substr(utf8_decode(hex2str($seg_data_bytes)),$xmp_data_start+$identier_length). "<br/>";

					$res = array();
                    if($OS == "Windows")
					    exec($perlbasedir . 'perl win_tools\ExifToolPerl\exiftool.pl -xmp -b ' . escapeshellarg($lf),$res);
                    else
                        exec('./lnx_tools/ExifTool/exiftool -xmp -b ' . escapeshellarg($lf),$res);
					$xmpinfo = $res;
					$xmpinfo = str_replace("> <",">\r\n<",$xmpinfo);
					//echo ($lf. ' xmpinfo: ' . $xmpinfo."<br/>");
				
					
				/*
					list($seg_data_bytes,$str) = getStringOfNoofBytes(0,$xmp_length); // length bytes
					echo("APP1_XMP data bytes = $seg_data_bytes<br/>");

					// at start point of xmp data
									
					$xmp_packet_start  = strpos($seg_data_bytes, '3c3f787061636b657420626567696e');// start packet
					$xmp_packet_end    = strpos($seg_data_bytes, '3c3f787061636b657420656e64');// end packet

					$xmp_packet_length = $xmp_packet_end - $xmp_packet_start;
					echo("APP1_XMP packet found in APP1: length= $xmp_packet_length<br/>");
						
					if($xmp_packet_length > 0)
					{
						$xmp_packet_data    = substr($seg_data_bytes, $xmp_packet_start, $xmp_packet_length+37);

						//echo("APP1_XMP packet bytes = $xmp_packet_data<br/>");
						
						//echo("<pre>");
						//hexDump($xmp_packet_data ,16);
						//echo("</pre>");
						
					//		$dd1 = new DOMDocument();
					//		$dd1->formatOutput = true;
					//		$dd1->loadXML($xmp_packet_data);
					//		
					//		echo $dd1->saveXML();

									
		


						$xmp_details = hex2str($xmp_packet_data);
						//echo ("APP1_XMP hex: ".$xmp_data."<br/>");
						//print("APP1_XMP stringified: ".$xmp_details."<br/>");
						$xmpinfo .= $xmp_details. "<br/>";

					}
					*/	

					$xmpbytes = $xmpbytes + $seg_data_length_bytes + 2;
				} // end xmp

				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				
				
				break;
			case 'ffe2':
				$seg_hdr_marker = 'APP2 ICC Profile';
				$iccbytes += $seg_data_length_bytes + 2;
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				break;
			case 'ffe3':
				$seg_hdr_marker = 'APP3 META';
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				break;
			case 'ffe4':
				$seg_hdr_marker = 'APP4';
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				break;
			case 'ffe5':
				$seg_hdr_marker = 'APP5';
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				break;
			case 'ffe6':
				$seg_hdr_marker = 'APP6';
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				break;
			case 'ffe7':
				$seg_hdr_marker = 'APP7';
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				break;
			case 'ffe8':
				$seg_hdr_marker = 'APP8';
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				break;
			case 'ffe9':
				$seg_hdr_marker = 'APP9';
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize +$seg_data_length_bytes + 2;
				break;
			case 'ffea':
				$seg_hdr_marker = 'APP10';
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				break;
			case 'ffeb':
				$seg_hdr_marker = 'APP11';
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				break;
			case 'ffec':
				//$seg_hdr_marker = 'APP12 Picture Info / Ducky';
				$seg_value_data =  substr($seg_data,0,$seg_length);  
				$seg_value = hex2str($seg_value_data);
				//echo("APP12 data: ".$seg_value_data."<br/>");
				$app12size = $seg_data_size_dec;
				//echo("APP12 data bytes: ".$app12size."<br/>");


				$seg_app12_hex  = substr($content, $seg_data_start,10);  //4chars = 2bytes
				$app12type = hex2str($seg_app12_hex);


				if(strtolower($app12type) == "ducky")
				{
					// DUCKY TAG - Photoshop
					//echo("seg APP12 type: ".$app12type." found<br/>");
					$seg_hdr_marker = 'APP12 Ducky (Photoshop Save For Web)';
					$seg_data_start =$seg_data_start + 10; // 2x5bytes
					// 2bytes total length, 5bytes DUCKY

					$duckytext = '';
					// loop

					$seg_data_tag_hex   = substr($content, $seg_data_start,4);  //4chars = 2bytes	
					$seg_data_tag_dec   = hexdec($seg_data_size_hex);
					$seg_data_size_hex   = substr($content, $seg_data_start + 4 ,4);	//4chars = 2bytes
					$seg_data_size_dec   = hexdec($seg_data_size_hex);
					//echo("seg APP12 tag hex: ".$seg_data_tag_hex."<br/>");
					//echo("seg APP12 length: ".$seg_data_size_dec."<br/>");
					$app12totbytesread = 11; // 5 bytes ducky, 2, bytes tag, 2 bytes len
					while ( $seg_data_tag_hex != '0000')
					{
						switch($seg_data_tag_hex)
						{
							case '0001':

								$seg_data_q_hex   = substr($content, $seg_data_start + 8 ,$seg_data_size_dec * 2);	//8chars = 4bytes
								$seg_data_q_dec   = hexdec($seg_data_q_hex);
								$app12_ducky_quality = $seg_data_q_dec;
								//echo("seg APP12 q hex: ".$seg_data_q_hex."<br/>");
								//echo("seg APP12 q: ".$seg_data_q_dec."<br/>");
								$app12totbytesread = $app12totbytesread + $seg_data_q_dec;
								$jpeg_substructure = $jpeg_substructure .  "- 0001 Quality: ".$seg_data_size_dec  ." bytes<br/>";
								$duckytext .= "Quality: ".$app12_ducky_quality ."<br/>";
								break;
						
							case '0002': // comment
								$seg_data_c_hex   = substr($content, $seg_data_start + 8 ,$seg_data_size_dec * 2);	//8chars = 4bytes
								$app12comment = hex2str($seg_data_c_hex);
								//echo("seg APP12 c hex: ".$seg_data_c_hex."<br/>");
								//echo("seg APP12 c: ".$app12comment."<br/>");
								$app12totbytesread = $app12totbytesread + $seg_data_c_dec;
								$jpeg_substructure = $jpeg_substructure .  "- 0002 Comment: ".$seg_data_size_dec  ." bytes<br/>";
								$duckytext .= "Comment: ".$app12comment."<br/>";
								break;

							case '0003': // copyright
								$seg_data_cp_hex   = substr($content, $seg_data_start + 8 ,$seg_data_size_dec * 2);	//8chars = 4bytes
								$app12copyright = hex2str($seg_data_cp_hex);
								//echo("seg APP12 cp hex: ".$seg_data_cp_hex."<br/>");
								//echo("seg APP12 cp: ".$app12copyright."<br/>");						
								$app12totbytesread = $app12totbytesread + $seg_data_cp_dec;
								$jpeg_substructure = $jpeg_substructure .  "- 0003 Copyright: ".$seg_data_size_dec  ." bytes<br/>";
								$duckytext .= "Copyright: ".$app12copyright."<br/>";
								break;
						}

						//echo("APP12 data tag bytes: ".$seg_data_size_dec."<br/>");
						$seg_data_start = $seg_data_start + (2 + 2 + $seg_data_size_dec) * 2 ; // 2x5bytes
						
						// next tag - are next two bytes 0000 if so end
						$app12totbytesread += 2; // tag marker 2 bytes
						$seg_data_tag_hex = substr($content, $seg_data_start ,4);	//4chars = 2bytes
						$seg_data_size_hex = substr($content, $seg_data_start + 4 ,4);	//4chars = 2bytes
						//if($seg_data_size_hex == '0000')
							//echo("APP12 tag end: <br/>");
						$seg_data_size_dec   = hexdec($seg_data_size_hex);
						//echo("<br/>next APP12 tag hex: ".$seg_data_tag_hex."<br/>");
						//echo("next APP12 length: ".$seg_data_size_dec."<br/>");
						//echo("APP12 data bytes read 2: ".$app12totbytesread."<br/>");

						
						
					}
					
			//		  Tag ID   Tag Name                             Writable
			//		  ------   --------                             --------
			//		  0x0001   Quality                              int32u/
			//		  0x0002   Comment                              string/
			//		  0x0003   Copyright                            string/
				}
				else
				{
					//picture info tag	
					
					
				}
				

				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				
				break;	
				
			case 'ffed':

				$seg_hdr_marker = 'APP13 PhotoShop';

				$seg_value_data =  substr($seg_data,8,26);
				$seg_data_str = hex2str($seg_value_data);
				//echo("seg APP13: ".$seg_data_str."<br/>");


				$seg_data_size_hex   = substr($content, $seg_data_start + 4,4);	
				$seg_data_size_dec   = hexdec($seg_data_size_hex);
				//echo("seg $seg_hdr_marker data size hex: ".$seg_data_size_hex."<br/>");
				//echo("seg $seg_hdr_marker data size dec: ".$seg_data_size_dec."<br/>");

				//
				$seg_app13_type = substr($seg_data,0,8); // 4 bytes
				//echo("APP13 segment type: ".$seg_app13_type."<br/>");
				
				$seg_app13_id = substr($seg_data,0,4); // 2 bytes
				//echo("APP13 segment id: ".$seg_app13_id."<br/>");

	
				//echo("seg $seg_hdr_marker found<br/>");
				$app13_seg_data_end   =  $seg_data_start +  $seg_data_size_dec * 2 + 4;
				$app13_seg_length     = ($seg_data_size_dec * 2) + 2; // allow for the 2x2bytes for length
				$app13_seg_data       = substr($content, $seg_data_start, $app13_seg_length);
				//echo("seg $seg_hdr_marker data: ".$app13_seg_data."<br/>");
				$seg_data_str = hex2str($app13_seg_data);
				//echo("seg data: ".$app13_seg_data."<br/>");
				
				// search for XMP data
				//echo("Looking For XMP Metadata in APP13<br/>");

				// check for IPTC 8BIM
				$iptc8bim_data_start = strpos($seg_data, '3842494d');
				$iptc8bim_data_end   = strpos($seg_data, '00',$iptc8bim_data_start);
				$iptc8bim_length     = $iptc8bim_data_end - $iptc8bim_data_start;
				$iptc8bim_data       = substr($content, $iptc8bim_data_start, $iptc8bim_length + 12);
				if($iptc8bim_data_start > 0)
				{
					//echo("IPTC Metadata found in APP13<br/>");
					$seg_hdr_marker = "APP13 IPTC";
					$iptcbytes = $iptcbytes + $seg_data_length_bytes;

				
				
				//	8BIM IPTC text metadata - starts with 38 42 49 4D 04 04 00 00 00 00 XX XX ..
				//  IPTC field starts with 1C 02 50 XX XX .. (0x50 = 80, IPTC field #80).

	
				//    [Record name]    [size]   [description]
				//	---------------------------------------
				//	(Type)           4 bytes  Photoshop uses '8BIM' from ver 4.0 on
				//	(ID)             2 bytes  a unique identifier, e.g., "\004\004" for IPTC
				//	(Name)             ...    a Pascal string (padded to make size even)
				//	(Size)           4 bytes  actual size of resource data
				//	(Data)             ...    resource data, padded to make size even
					
					
				}

					
				// search for XMP data

				$xmp_data_start = strpos($content, '<?xpacket begin');
				$xmp_data_end   = strpos($content, '<?xpacket end');
				$xmp_length     = $xmp_data_end - $xmp_data_start;
				$xmp_data       = substr($content, $xmp_data_start, $xmp_length + 12);
				$xmp_details = hex2str($xmp_data);
				//echo("XMP length in APP13: $xmp_length<br/>");
				if($xmp_length > 0)
				{
					//echo("XMP Metadata found<BR/>");
					$seg_hdr_marker = "APP13 XMP";
					$xmpbytes = $xmpbytes + $xmp_length;

					//echo ("APP13_XMP: ".$xmp_data);
					$xmpinfo .= $xmp_details. "<br/>";
					//$xmpbytes = $xmpbytes + $seg_data_length_bytes + 2;
				}
				
					
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;

					
				break;
			case 'ffee':
				$seg_hdr_marker = 'APP14 Adobe';
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				break;
			case 'ffef':
				$seg_hdr_marker = 'APP15';
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes + 2;
				break;
				
			case 'ffda': //
				$seg_hdr_marker = 'SOS Start of Scan';
				// Segment length is the length of the segment excluding the marker and scan data (12)
				// must be 6+2*(number of components in scan)
				$jpeg_sos_structure = "Scan Header length: ". $seg_data_size_dec."<br/>";
				$noofComponentsInScan = ($seg_data_size_dec - 6) / 2;
				$jpeg_sos_structure = $jpeg_sos_structure ."No. of Components in Scan: ". $noofComponentsInScan."<br/>";
				
				//SOS (Start Of Scan) marker:
				//Field                      	Size        	Description
				//Marker Identifier       		2 bytes      	0xff, 0xda identify SOS marker
				//Length                       	2 bytes      	This must be equal to 6+2*(number of components in scan).
				//Number of
				//Components in scan  			1 byte        	This must be >= 1 and <=4 (otherwise error), usually 1 or 3
				//Each component        		2 bytes      	For each component, read 2 bytes. It contains,
				//                                                   1 byte   Component Id (1=Y, 2=Cb, 3=Cr, 4=I, 5=Q),
				//                                                 	 1 byte   Huffman table to use :
				//                                         			 bit 0..3 : AC table (0..3)
				//                                                   bit 4..7 : DC table (0..3)
 
				//Ignorable Bytes          		3 bytes      	We have to skip 3 bytes.
				
				//03010002110311003f00
				$noofbytes = $seg_length/2;
				$seg_bytes_data =  substr($seg_data,0,$seg_length);
				//echo "JPEG SOS: len: ".$noofbytes . " bytes: "  . $seg_bytes_data."<br/>";
				
				
				//
				$jpeg_structure = $jpeg_structure . $jpeg_sos_structure;
				break;
				
			case 'ffdb': //
				$seg_hdr_marker = 'DQT Define Quantization Table';
				break;
			
			case 'ffdc': //
				$seg_hdr_marker = 'DNL Define Number of Lines';
				break;
			
			case 'ffdd': //
				$seg_hdr_marker = 'DRI Define Restart Interval';
				break;

			case 'ffde': //
				$seg_hdr_marker = 'DHP Define Hierarchical Progression';
				break;

			case 'ffdf': //
				$seg_hdr_marker = 'EXP Expand Reference Component(s)';
				break;
						
			case 'fffe': // comment
				$seg_hdr_marker = 'Comment';
				$seg_comment_data =  substr($seg_data,0,$seg_length);  
				$seg_comment = hex2str($seg_comment_data);
				//echo("$lf: JPEG Comment: ".$seg_comment."<br/>");
				$comment_bytes = $seg_data_length_bytes;
				// add to metadata cumulative size
				$mdbytesize = $mdbytesize + $seg_data_length_bytes;
				
				$qualitypos = strpos(strtolower($seg_comment),'quality = ');
				$qualityposEq = strpos(strtolower($seg_comment),'= ');
				if($qualitypos > 0)
				{
					$commentquality = trim(substr($seg_comment,$qualityposEq+1));
					//echo ("comment = ".$seg_comment."<br/>");
					//echo ("comment quality = ".$commentquality."<br/>");
				}
				break;

				
			case 'ffc0': //SOF0
				$seg_hdr_marker = 'SOF';
				$imgfileencoding = 'Baseline DCT';
				
				//SOF0 (Start Of Frame 0) marker:
 
				//Field                             Size       	Description
				// Marker Identifier                2 bytes    	0xff, 0xc0 to identify SOF0 marker
				//Length                            2 bytes   	This value equals to 8 + components*3 //value
				//Data precision                    1 byte     	This is in bits/sample, usually 8
				//                                                (12 and 16 not supported by most software).
				//Image height                      2 bytes    	This must be > 0
				//Image Width                       2 bytes    	This must be > 0
				//Number of components        		1 byte      Usually 1 = grey scaled, 3 = color YcbCr or YIQ, 4 = color CMYK
				//Each component                   	3 bytes     Read each component data of 3 bytes. It contains:
				//                                              component Id(1byte)(1 = Y, 2 = Cb, 3 = Cr, 4 = I, 5 = Q),   
				//                                              sampling factors (1byte) (bit 0-3 vertical., 4-7 horizontal.),
				//                                              quantization table number (1 byte)).
				// DP hght widt no comp1  comp2  comp3
				//                   vv     vv     vv   
				// 08 0086 0190 03 011100 021101 031101
				//080086019003012200021101031101
				//echo "JPEG SOF0: seg length: ". $seg_length."<br/>";
				$noofbytes = $seg_length/2;
				$seg_bytes_data =  substr($seg_data,0,$seg_length);
				$noofcomponents = substr($seg_bytes_data,10,2);
				//echo "JPEG SOF0: len: ".$noofbytes . " bytes: "  . $seg_bytes_data."<br/>";
				$chromasubsampling = substr($seg_bytes_data,14,2);
				//if($noofcomponents == '01')
				//	echo "JPEG Baseline SOF0: Chroma Sub-sampling irrelevant (Grayscale)<br/>";
				//else
				//	echo "JPEG Baseline SOF0: Chroma Sub-sampling (".$noofcomponents." components): ".$chromasubsampling."<br/>";
				
				switch ($chromasubsampling)
				{
					case '11':
						//echo "1h1v,1h1v,1h1v (also called 4:4:4 or 1x1 sampling)";
						$chromasubsampling = "1x1 (4:4:4)";
						break;
					case '21':
						//echo "2h1v,1h1v,1h1v (also called 4:2:2 or 2x1 sampling)";
						$chromasubsampling = "2x1 (4:2:2)";
						break;
					case '22':
						//echo "2h2v,1h1v,1h1v (also called 4:2:0 or 2x2 sampling)";
						$chromasubsampling = "2x2 (4:2:0)";
						break;
					case '11':
						//echo "11h2v,1h1v,1h1v (also called 1x2 sampling))";
						$chromasubsampling = "1x2";
						break;						
				}
				//The sampling factors for YCbCr images must be one of these sets:
				//1h1v,1h1v,1h1v (also called 4:4:4 or 1x1 sampling)
				//2h1v,1h1v,1h1v (also called 4:2:2 or 2x1 sampling)
				//2h2v,1h1v,1h1v (also called 4:2:0 or 2x2 sampling)
				//1h2v,1h1v,1h1v (also called 1x2 sampling)
				//
				//Photoshop Save As Quality 0-6 - 2x2 Chroma Subsampling
				//Photoshop Save As Quality 7-12 - 1x1 No Chroma Subsampling
				//Photoshop Save For Web Quality 0-50 - 2x2 Chroma Subsampling
				//Photoshop Save For Web Quality 51-100 - 1x1 No Chroma Subsampling
				break;	
			case 'ffc1': //SOF1
				$seg_hdr_marker = 'SOF1';
				$imgfileencoding = 'Sequential';
				break;
			case 'ffc2': //SOF2
				$seg_hdr_marker = 'SOF2';
				$imgfileencoding = 'Progressive';

				//echo "JPEG SOF2: seg length: ". $seg_length."<br/>";
				$noofbytes = $seg_length/2;
				$seg_bytes_data =  substr($seg_data,0,$seg_length);
				$noofcomponents = substr($seg_bytes_data,10,2);
				//echo "JPEG SOF2: len: ".$noofbytes . " bytes: "  . $seg_bytes_data."<br/>";
				$chromasubsampling = substr($seg_bytes_data,14,2);
				//if($noofcomponents == '01')
				//	echo "JPEG Baseline SOF2: Chroma Sub-sampling irrelevant (Grayscale)<br/>";
				//else
				//	echo "JPEG Baseline SOF2: Chroma Sub-sampling (".$noofcomponents." components): ".$chromasubsampling."<br/>";
				
				switch ($chromasubsampling)
				{
					case '11':
						//echo "1h1v,1h1v,1h1v (also called 4:4:4 or 1x1 sampling)";
						$chromasubsampling = "1x1 (4:4:4)";
						break;
					case '21':
						//echo "2h1v,1h1v,1h1v (also called 4:2:2 or 2x1 sampling)";
						$chromasubsampling = "2x1 (4:2:2)";
						break;
					case '22':
						//echo "2h2v,1h1v,1h1v (also called 4:2:0 or 2x2 sampling)";
						$chromasubsampling = "2x2 (4:2:0)";
						break;
					case '11':
						//echo "11h2v,1h1v,1h1v (also called 1x2 sampling))";
						$chromasubsampling = "1x2";
						break;						
				}
				break;
			case 'ffc3': //SOF3
				$seg_hdr_marker = 'SOF3';
				$imgfileencoding = 'Lossless';
				break;
			case 'ffc4': //DHT
				$seg_hdr_marker = 'DHT Define Huffman Table';
				if (strpos($imgfileencoding,'Huffman')==0)
					$imgfileencoding .= ', Huffman Encoding';
				break;
			case 'ffc5': //SOF5
				$seg_hdr_marker = 'SOF5';
				$imgfileencoding = 'Differential Sequential';
				break;
			case 'ffc6': //SOF6
				$seg_hdr_marker = 'SOF6';
				$imgfileencoding = 'Differential Progressive';
				break;
			case 'ffc7': //SOF7
				$seg_hdr_marker = 'SOF7';
				$imgfileencoding = 'Differential Lossless';
				break;
			case 'ffc9': //SOF9
				$seg_hdr_marker = 'SOF9';
				$imgfileencoding = 'Extended Sequential, Arithmetic Coding';
				break;
			case 'ffca': //SOF10
				$seg_hdr_marker = 'SOF10';
				$imgfileencoding = 'Progressive, Arithmetic Coding';
				break;
			case 'ffcb': //SOF11
				$seg_hdr_marker = 'SOF11';
				$imgfileencoding = 'Lossless, Arithmetc Coding';
				break;
			case 'ffcc': //DAC
				$seg_hdr_marker = 'DAC Define Arithmetic Coding Conditioning(s)';
			case 'ffcd': //SOF13
				$seg_hdr_marker = 'SOF13';
				$imgfileencoding = 'Differential Sequential, Arithmetic Coding';
				break;
			case 'ffce': //SOF14
				$seg_hdr_marker = 'SOF14';
				$imgfileencoding = 'Differential Progressive, Arithmetic Coding';
				break;
			case 'ffcf': //SOF15
				$seg_hdr_marker = 'SOF15';
				$imgfileencoding = 'Differential Lossless, Arithmetic Coding';
				break;
				
			default:
				$seg_hdr_marker = $seg_hdr;
				break;
		}
		$total_seg_length = $seg_data_length_bytes + 2; // add 2 for segment length bytes, but don't add 2 for the segement header
		$jpeg_structure = $jpeg_structure . $seg_hdr_marker. " (".$seg_hdr. "): ". $total_seg_length." bytes<br/>";
		if($jpeg_substructure != '')
			$jpeg_structure = $jpeg_structure . $jpeg_substructure;


		// output data header meaning and the data
		//echo("$seg_hdr_marker data: ".$seg_data."<br/>");
		//echo("$seg_hdr_marker data marker found: length=". $seg_length/2 ."<br/>");
		//echo("$seg_hdr_marker data marker found: byte length=". $seg_length_bytes ."<br/>");		
		//echo("data: ".$seg_data."<br/>");
		

		// NEXT
		
		$seg_start = $seg_start + $seg_length + 4;
		$seg_start_bytes = $seg_start_bytes + $seg_length_bytes + 4; //allow 2extra bytes for seg_hdr + 2
		
		
		//$seg_hdr = substr($content, $seg_start, 4); // 2bytes
		
		//echo("next set start bytes: ".$seg_start_bytes."<br/>");
		// load byte array with bytes from file
		getBytesFromFile($lf,$seg_start_bytes,2);
		list($seg_hdr,$bytestr) = getStringOfNoofBytes(0,2); // 2bytes
		
		//echo("<br/>NEXT segment hdr: ".$seg_hdr."<br/>");
		
	
		switch($seg_hdr)
		{
				
			case 'ffd8': // Start of Image
				$segfound = true;
				break;
			case 'ffd9': // End of Image
				$segfound = true;
				break;
			
			case 'ffe0':
			case 'ffe1':
			case 'ffe2':
			case 'ffe3':
			case 'ffe4':
			case 'ffe5':
			case 'ffe6':
			case 'ffe7':
			case 'ffe8':
			case 'ffe9':
			case 'ffea':
			case 'ffeb':
			case 'ffec':
			case 'ffed':
			case 'ffee':
			case 'ffef':
				$segfound = true;
				break;


			case 'ffc0': //SOF
			case 'ffc1':
			case 'ffc2':
			case 'ffc3':
			case 'ffc4':
			case 'ffc5':
			case 'ffc6':
			case 'ffc7':
			case 'ffc8':
			case 'ffc9':
			case 'ffca':
			case 'ffcb':
			case 'ffcc':
			case 'ffcc':
			case 'ffcd':
			case 'ffce':
			case 'ffcf':
				$segfound = true;
				break;
			
			case 'ff01': // temporary for arithmetic coding
			case 'ffd0': // restart marker
			case 'ffd1': // restart marker
			case 'ffd2': // restart marker
			case 'ffd3': // restart marker
			case 'ffd4': // restart marker
			case 'ffd5': // restart marker
			case 'ffd6': // restart marker
			case 'ffd7': // restart marker
			case 'ffda': // Start of Scan
			case 'ffdb': // Define Quanitization Table
			case 'ffdc': // Define Number of Lines
			case 'ffdd': // Define restart onterval
			case 'ffde': // Define Hierarchical Progression
			case 'ffdf': // Expand reference component
				$segfound = true;
				break;
			
			case 'fffe': // commment
				$segfound = true;
				break;
				
			default:
				$segfound = false;
				break;
		}
		
		
	endwhile;

	//echo("JPEG file: ".$lf.": Total metadata bytes: ".$mdbytesize."<br/>");
	//echo("JPEG file: ".$lf.": Type: ".$imgfileencoding."<br/>");
	
	//echo("JPEG file: ".$lf.": Structure:<br/>".$jpeg_structure);

	//echo("<br/>".$app12size . " - ".$duckytext."<br/>");
	$arr = array($imgfiletype,$imgfileencoding,$exifbytes,$iptcbytes,$xmpbytes,$xmpinfo,$seg_comment,$comment_bytes,$mdbytesize,$density,$iccbytes,$jpeg_structure,$app12_ducky_quality,$app12size,$duckytext,$commentquality,$chromasubsampling);

	return($arr);
}


function decodeBMP($content,$lf)
{
    global $perlbasedir;
	$hexstring = unpack("H*", $content);
	$content = implode($hexstring);

    $compression = '';
    $bitcount = '';

    $structure = "Bitmap File Header: <br/>";

	// BMP Header
	list($file_hdr1,$bytestr) = getStringOfNoofBytes(0,2); // 8bytes


    switch (strtoupper($file_hdr1))
    {
        case '424D':
            $imgfiletype = "Windows BMP";
            $bmptype = "Windows";
            break;

        case '4241':
          $imgfiletype = "OS/2 BMP";
          $bmptype = "OS/2";
          break;


    }
    // next four bytes = file size
    list($byteshex,$bytestr) = getDecimalOfNoofBytesLittleEndian(2,4); // 4bytes
    $offset = 6;
   // echo "Analysing ".$bmptype." BMP image of " . $bytestr . " bytes<br/>";

    //next 4 bytes are zero, reserved
    $offset +=4;

    // next four bytes = image offset
    list($byteshex,$bytestr) = getDecimalOfNoofBytesLittleEndian(10,4); // 4bytes
    //echo "offset ".$offset.": image begins at byte = " . $bytestr . "<br/>";
    $offset +=4;


    getBytesFromFile($lf,14,4); // starts at byte offset 14
    // file header starts at offset 14 and is of fixed size 40 bytes
    list($byteshex,$bytestr) = getDecimalOfNoofBytesLittleEndian(0,4); // 4bytes size of head
    //echo $byteshex.": bitmapinfoheader at offset ".$offset." = " . $bytestr . " bytes<br/>";
    $offset +=4;
    $structure =$structure . "DIB Header: ".$bytestr . " bytes<br/>";

    getBytesFromFile($lf,14,$bytestr); // starts at byte offset 14 for a variable number of bytes depending on version

    switch($bytestr)
    {
        case 12:
            if(strtoupper($file_hdr1) == '424D')
                $imgfiletype = 'Windows Bitmap v2';
            if(strtoupper($file_hdr1) == '4241')
                $imgfiletype = 'OS/2 Bitmap v1';
            // width and height next
            list($byteshex,$width) = getDecimalOfNoofBytesLittleEndian(4,2); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": width = " . $width . "<br/>";
            $offset +=2;
            list($byteshex,$height) = getDecimalOfNoofBytesLittleEndian(6,2); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": height = " . $height . "<br/>";
            $offset +=2;
            //image planes
            list($byteshex,$noofimageplanes) = getDecimalOfNoofBytesLittleEndian(8,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": imageplanes = " . $noofimageplanes . "<br/>";
            $offset +=2;

            //bitcount
            list($byteshex,$bitcount) = getDecimalOfNoofBytesLittleEndian(10,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": bitcount = " . $bitcount . "<br/>";
            $offset +=2;
            break;

        case 16:
            if(strtoupper($file_hdr1) == '4241')
                $imgfiletype = 'OS/2 Bitmap v2';
            // width and height next
            list($byteshex,$width) = getDecimalOfNoofBytesLittleEndian(4,2); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": width = " . $width . "<br/>";
            $offset +=2;
            list($byteshex,$height) = getDecimalOfNoofBytesLittleEndian(6,2); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": height = " . $height . "<br/>";
            $offset +=2;
            //image planes
            list($byteshex,$noofimageplanes) = getDecimalOfNoofBytesLittleEndian(8,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": imageplanes = " . $noofimageplanes . "<br/>";
            $offset +=2;

            //bitcount
            list($byteshex,$bitcount) = getDecimalOfNoofBytesLittleEndian(10,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": bitcount = " . $bitcount . "<br/>";
            $offset +=2;
            break;

        case 40:
            if(strtoupper($file_hdr1) == '424D')
              $imgfiletype = 'Windows Bitmap v3';
            if(strtoupper($file_hdr1) == '4241')
                $imgfiletype = 'OS/2 Bitmap v2';

            // width and height next
            list($byteshex,$width) = getDecimalOfNoofBytesLittleEndian(4,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": width = " . $width . "<br/>";
            $offset +=4;
            list($byteshex,$height) = getDecimalOfNoofBytesLittleEndian(8,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": height = " . $height . "<br/>";
            $offset +=4;

            //image planes
            list($byteshex,$noofimageplanes) = getDecimalOfNoofBytesLittleEndian(12,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": imageplanes = " . $noofimageplanes . "<br/>";
            $offset +=2;

            //bitcount
            list($byteshex,$bitcount) = getDecimalOfNoofBytesLittleEndian(14,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": bitcount = " . $bitcount . "<br/>";
            $offset +=2;

            //compression
            list($byteshex,$compression) = getDecimalOfNoofBytesLittleEndian(16,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": compression = " . $compression . "<br/>";
            $offset +=4;

            //image size
            list($byteshex,$imgbytes) = getDecimalOfNoofBytesLittleEndian(20,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": imgsize = " . $imgbytes . "bytes <br/>";
            $offset +=4;
            $structure =$structure . "Raster Data: ".$imgbytes . " bytes<br/>";
          break;

        case 56:
            if(strtoupper($file_hdr1) == '424D')
                $imgfiletype = 'Windows Bitmap v3';
            if(strtoupper($file_hdr1) == '4241')
                $imgfiletype = 'OS/2 Bitmap v2';

                        // width and height next
            list($byteshex,$width) = getDecimalOfNoofBytesLittleEndian(4,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": width = " . $width . "<br/>";
            $offset +=4;
            list($byteshex,$height) = getDecimalOfNoofBytesLittleEndian(8,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": height = " . $height . "<br/>";
            $offset +=4;

            //image planes
            list($byteshex,$noofimageplanes) = getDecimalOfNoofBytesLittleEndian(12,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": imageplanes = " . $noofimageplanes . "<br/>";
            $offset +=2;

            //bitcount
            list($byteshex,$bitcount) = getDecimalOfNoofBytesLittleEndian(14,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": bitcount = " . $bitcount . "<br/>";
            $offset +=2;

            //compression
            list($byteshex,$compression) = getDecimalOfNoofBytesLittleEndian(16,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": compression = " . $compression . "<br/>";
            $offset +=4;
            //image size
            list($byteshex,$imgbytes) = getDecimalOfNoofBytesLittleEndian(20,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": imgsize = " . $imgbytes . "bytes <br/>";
            $offset +=4;
            $structure =$structure . "Raster Data: ".$imgbytes . " bytes<br/>";

            break;

        case 64:
            $imgfiletype = 'OS/2 Bitmap v2';
                        // width and height next
            list($byteshex,$width) = getDecimalOfNoofBytesLittleEndian(4,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": width = " . $width . "<br/>";
            $offset +=4;
            list($byteshex,$height) = getDecimalOfNoofBytesLittleEndian(8,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": height = " . $height . "<br/>";
            $offset +=4;

            //image planes
            list($byteshex,$noofimageplanes) = getDecimalOfNoofBytesLittleEndian(12,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": imageplanes = " . $noofimageplanes . "<br/>";
            $offset +=2;

            //bitcount
            list($byteshex,$bitcount) = getDecimalOfNoofBytesLittleEndian(14,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": bitcount = " . $bitcount . "<br/>";
            $offset +=2;

            //compression
            list($byteshex,$compression) = getDecimalOfNoofBytesLittleEndian(16,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": compression = " . $compression . "<br/>";
            $offset +=4;
                        //image size
            list($byteshex,$imgbytes) = getDecimalOfNoofBytesLittleEndian(20,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": imgsize = " . $imgbytes . "bytes <br/>";
            $offset +=4;
            $structure =$structure . "Raster Data: ".$imgbytes . " bytes<br/>";

            break;

        case 108:
            $imgfiletype = 'Windows Bitmap v4';
                        // width and height next
            list($byteshex,$width) = getDecimalOfNoofBytesLittleEndian(4,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": width = " . $width . "<br/>";
            $offset +=4;
            list($byteshex,$height) = getDecimalOfNoofBytesLittleEndian(8,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": height = " . $height . "<br/>";
            $offset +=4;

            //image planes
            list($byteshex,$noofimageplanes) = getDecimalOfNoofBytesLittleEndian(12,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": imageplanes = " . $noofimageplanes . "<br/>";
            $offset +=2;

            //bitcount
            list($byteshex,$bitcount) = getDecimalOfNoofBytesLittleEndian(14,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": bitcount = " . $bitcount . "<br/>";
            $offset +=2;

            //compression
            list($byteshex,$compression) = getDecimalOfNoofBytesLittleEndian(16,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": compression = " . $compression . "<br/>";
            $offset +=4;

            //image size
            list($byteshex,$imgbytes) = getDecimalOfNoofBytesLittleEndian(20,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": imgsize = " . $imgbytes . "bytes <br/>";
            $offset +=4;
            $structure =$structure . "Raster Data: ".$imgbytes . " bytes<br/>";
            break;

        case 124:
            $imgfiletype = 'Windows Bitmap v5';
                        // width and height next
            list($byteshex,$width) = getDecimalOfNoofBytesLittleEndian(4,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": width = " . $width . "<br/>";
            $offset +=4;
            list($byteshex,$height) = getDecimalOfNoofBytesLittleEndian(8,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": height = " . $height . "<br/>";
            $offset +=4;

            //image planes
            list($byteshex,$noofimageplanes) = getDecimalOfNoofBytesLittleEndian(12,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": imageplanes = " . $noofimageplanes . "<br/>";
            $offset +=2;

            //bitcount
            list($byteshex,$bitcount) = getDecimalOfNoofBytesLittleEndian(14,2); // 2bytes size of head
            //echo $byteshex." offset ".$offset.": bitcount = " . $bitcount . "<br/>";
            $offset +=2;

            //compression
            list($byteshex,$compression) = getDecimalOfNoofBytesLittleEndian(16,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": compression = " . $compression . "<br/>";
            $offset +=4;

            //image size
            list($byteshex,$imgbytes) = getDecimalOfNoofBytesLittleEndian(20,4); // 4bytes size of head
            //echo $byteshex." offset ".$offset.": imgsize = " . $imgbytes . "bytes <br/>";
            $offset +=4;
            $structure =$structure . "Raster Data: ".$imgbytes . " bytes<br/>";

            break;
    }



// compression methods
//0	BI_RGB	none	Most common
//1	BI_RLE8	RLE 8-bit/pixel	Can be used only with 8-bit/pixel bitmaps
//2	BI_RLE4	RLE 4-bit/pixel	Can be used only with 4-bit/pixel bitmaps
//3	BI_BITFIELDS	OS22XBITMAPHEADER: Huffman 1D	BITMAPV2INFOHEADER: RGB bit field masks,
//BITMAPV3INFOHEADER+: RGBA
//4	BI_JPEG	OS22XBITMAPHEADER: RLE-24	BITMAPV4INFOHEADER+: JPEG image for printing[10]
//5	BI_PNG		BITMAPV4INFOHEADER+: PNG image for printing[10]
//6	BI_ALPHABITFIELDS	RGBA bit field masks	only Windows CE 5.0 with .NET 4.0 or later
//11	BI_CMYK	none	only Windows Metafile CMYK[4]
//12	BI_CMYKRLE8	RLE-8	only Windows Metafile CMYK
//13	BI_CMYKTLE4	RLE-4	only Windows Metafile CMYK

    if($bmptype == "Windows")
    {
      switch ($compression)
      {
          case 0:
              $encoding = 'uncompressed';
              break;
          case 1:
              $encoding = 'RLE 8-bit/pixel';
              break;
          case 2:
              $encoding = 'RLE 4-bit/pixel';
              break;
          case 3:
              $encoding = 'bitfields';
              break;
          case 4:
              $encoding = 'JPEG';
              break;
          case 5:
              $encoding = 'PNG';
              break;

          default:
              $encoding = '';
      }
    }
    else
    {
        // OS/2
        switch ($compression)
        {
            case 0:
                $encoding = 'uncompressed';
                break;
            case 1:
                $encoding = 'RLE 8-bit/pixel';
                break;
            case 2:
                $encoding = 'RLE 4-bit/pixel';
                break;
            case 3:
                $encoding = 'Huffman 1D';
                break;
            case 4:
                $encoding = 'RLE-24';
                break;

            default:
                $encoding = '';
        }
    }



    //// return analysis
	$arr = array($imgfiletype,$structure,$width,$height,$encoding,$bitcount);
	return($arr);
} // end BMP image


  function hex2str($hex) {
	$cstr = '';
    for($i=0;$i<strlen($hex);$i+=2)
    $cstr = $cstr . chr(hexdec(substr($hex,$i,2)));

    return $cstr;
  }
  
  function str2hex($string)
	{
	$hex='';
	for ($i=0; $i < strlen($string); $i++)
	{
		$hex .= dechex(ord($string[$i]));
	}
	return $hex;
	}


function TinyPNGAPI($input,$output)
{
$key = "<your api key>";
//$input = "large-input.png";
//$output = "tiny-output.png";

$request = curl_init();
curl_setopt_array($request, array(
  CURLOPT_URL => "https://api.tinypng.com/shrink",
  CURLOPT_USERPWD => "api:" . $key,
  CURLOPT_POSTFIELDS => file_get_contents_utf8($input),
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
  }
} else {
    print(curl_error($request));
  /* Something went wrong! */
  print("Compression failed");
}

}



function decodeGIF($content,$lf)
{
    global $filepath_domainsavedir,$OS,$perlbasedir;

      $gif_signature = '';
      $imageencoding = "Lempel-Ziv-Welch";
      $lswdec = 0;
      $lshdec = 0;
      $interlace = 'Non-Interlaced';
      $colresdec  = '';
      $mdbytesize = 0;
      $comment_bytes = 0;
      $comment_text = '';
      $gif_structure = '';
      $animation = '';
      $xmpinfo = '';
      $gifxmpbytes = 0;
      $noofFrames = 0;




    $res = array();
    if($OS == "Windows")
        exec($perlbasedir . 'perl win_tools\ExifToolPerl\exiftool.pl -j '. escapeshellarg($lf),$rawgifdata);
    else
        exec('./lnx_tools/ExifTool/exiftool -j '. escapeshellarg($lf),$rawgifdata);
    //eval('$gifdata=' . 'win_toolsexiftool -php -q '.$lf);
    $jsondata  = implode('',$rawgifdata);
    $gdata = json_decode($jsondata);
    $gifdata = $gdata[0];

    if(!isset($gifdata->FileType))
    {
        //echo($lf.' gif analysis<pre>');
        //print_r($gifdata);
        //echo('</pre>');
        //echo($gifdata->sourcefile);
        $arr = array('',$imageencoding,$lswdec,$lshdec,$interlace,$colresdec,$mdbytesize,$comment_bytes,$comment_text,'-',$animation,$xmpinfo,$gifxmpbytes);
	    return($arr);
    }
    //get data from exiftool array
    $gif_signature = $gifdata->FileType.$gifdata->GIFVersion;
    if(isset($gifdata->Comment))
    {
        $comment_text = $gifdata->Comment;
        $comment_bytes = strlen($comment_text);
    }
    $lswdec = $gifdata->ImageWidth;
    $lshdec = $gifdata->ImageHeight;
    $colresdec = $gifdata->ColorResolutionDepth;
    $bitsperpixeldec = $gifdata->BitsPerPixel;
    if(isset($gifdata->FrameCount))
        $noofFrames = $gifdata->FrameCount;
    if(isset($gifdata->XMPToolkit))
        {
            $res = array();
            if($OS == "Windows")
    		    exec($perlbasedir . 'perl win_tools\ExifToolPerl\exiftool.pl -xmp -b ' . escapeshellarg($lf),$res);
            else
                exec('./lnx_tools/ExifTool/exiftool -xmp -b ' . escapeshellarg($lf),$res);
    		$xmpinfo = implode('',$res);

            $gifxmpbytes = strlen($xmpinfo);
        }


    $res = array();
    if($OS == "Windows")
        exec($perlbasedir . 'perl win_tools\ExifToolPerl\exiftool.pl -verbose '. escapeshellarg($lf),$rawgifinfo);
    else
        exec('./lnx_tools/ExifTool/exiftool -verbose '. escapeshellarg($lf),$rawgifinfo);

    $rowcnt = 0;
    foreach($rawgifinfo as $row)
    {
        $rowcnt += 1;
        if(strlen($row) > 2 and $row != 'Gif file terminated normally.' and $rowcnt > 10)  // and substr($row,5,1) != ":"
            $gif_structure = $gif_structure. '<br/>' . $row;
        if($row == '	Image is Interlaced.')
            $interlace = "Progressive";
    }

//    echo($lf . ' GIF image '. $rowcnt .' rows<pre>');
//    print_r($gif_structure);
//    echo('</pre>');



	if ($noofFrames > 1)
	{
		//echo "<br/>$lf: Animated GIF: $noofFrames frames ($noofimgdatablocks datablocks)<br/>";
		$animation = 'Animated ('.$noofFrames.' frames)';

        $folder = '_Animation_Frames';
        $baseAnimfolder =  $filepath_domainsavedir.$folder;
        if (!file_exists($baseAnimfolder))
            mkdir($baseAnimfolder, 0777, true);

        $path_parts = pathinfo($lf);
        $filename = $path_parts['filename'];
        $animsavedirfile = $baseAnimfolder.DIRECTORY_SEPARATOR.$filename."_";
        //echo("anim save folder filename = ".$animsavedirfile."<br/>");

        // split out the frames
        // split out the frames
        $decoder = new A2_GIF_Decoder ($content);
        $frames = $decoder->getFrames();

        for ( $i = 0; $i < count ( $frames ); $i++ ) {
            $fname = ( $i < 10 ) ? $animsavedirfile."_frame0$i.gif" : $animsavedirfile."_frame$i.gif";
            fwrite ( fopen ( $fname, "wb" ), $frames [ $i ] );
        }

	}
	//else
		//echo "<br/>$lf: Non-Animated GIF<br/>";


	$mdbytesize = $mdbytesize + $comment_bytes + $gifxmpbytes;
	$arr = array($gif_signature,$imageencoding,$lswdec,$lshdec,$interlace,$colresdec,$mdbytesize,$comment_bytes,$comment_text,$gif_structure,$animation,$xmpinfo,$gifxmpbytes);

	return($arr);
}

function removeEmptyLines($string)
{
return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);
}


function decodePNG($content,$lf)
{
  global $OS,$perlbasedir;
  $png_signature = '';
  $imageencoding= '';
  $png_structure= '';
  $widthstr= '';
  $heightstr= '';
  $bitdepthstr= '';
  $colourtypestr= '';
  $filtermethodstr= '';
  $interlacemethodstr= '';
  $mdbytesize= 0;
  $comment_bytes= 0;
  $comment_text= '';
  $xmpbytes= 0;
  $iccbytes = 0;
  $xmpinfo = '';


  //pngcrush -n -v <img> to identify text chunnks

    $windows_cmd = $perlbasedir . 'perl win_tools\ExifToolPerl\exiftool.pl' .' -v ' .escapeshellarg($lf);
    $linux_cmd = "./lnx_tools/ExifTool/exiftool" .' -v ' .escapeshellarg($lf);
    //echo 'cmd = '.$windows_cmd;
    $res = array();
    if($OS == "Windows")
	    exec($windows_cmd,$res);
    else
        exec($linux_cmd,$res);
    //$res = shell_exec($windows_cmd);
    $impres = implode('',$res);
    $pngsplit = explode('PNG ',$impres);


  //echo('png $res: <pre>');
  //print_r($pnginfodata);
  //print_r($res);
  //echo('</pre>');

//  echo('pngsplit: <pre>');
  //print_r($pnginfodata);
//  print_r($pngsplit);
//  echo('</pre>');

  $arrtextchunkkeywords = array();
  //loop
  foreach($pngsplit as $pngchunk)
  {
    $chunkheader = substr($pngchunk,0,4);
    switch($chunkheader)
    {
        case 'iTXt':
        case 'zTXt':
        case 'tEXt':
            // check for xmp enbedded
            $xmppos = strpos('XML',$pngchunk);
            if($xmppos === false)
            {
			  $chunkinfo = explode(':  | ',$pngchunk);
				if(isset($chunkinfo[1]))
				{
					$chunkinfo2 = explode(' | ',$pngchunk);
					if(isset($chunkinfo2[2]))
					{
						$chunkdata2 = explode(' = ',$chunkinfo2[2]);
						$keyword = $chunkdata2[0];
						$arrtextchunkkeywords[] = $keyword;
					}
					else
					{
							$chunkdata = explode(' = ',$chunkinfo[1]);
							$keyword = $chunkdata[0];
							$keyword = str_replace(":","",$keyword); // colons need to be replaced
							$arrtextchunkkeywords[] = $keyword;
					}
				}
            }
            else
            {
              // ignore XMP here
            }
            break;
        case 'iCCP':
		$chunkinfo = explode(':  | ',$pngchunk);
		if(isset($chunkinfo[1]))
		{
			$chunkinfo2 = explode(' | ',$pngchunk);
			if(isset($chunkinfo2[2]))
			{
				$chunkdata2 = explode(' = ',$chunkinfo2[2]);
				$keyword = $chunkdata2[0];
				$arrtextchunkkeywords[] = $keyword;
			}
			else
			{
					$chunkdata = explode(' = ',$chunkinfo[1]);
					$keyword = $chunkdata[0];
					$keyword = str_replace(":","",$keyword); // colons need to be replaced
					$arrtextchunkkeywords[] = $keyword;
			}
		}
			if(isset($chunkinfo2))
				$fullString = $chunkinfo2[0];
			else
				$fullString = $chunkinfo[0];
            $start = 6; //strpos('(', $fullString);
            $end = strlen($fullString) - strpos(')', $fullString);
            $shortString = substr($fullString, $start, $end)    ;
            //echo('png chunk ICCP fullstring:' . $fullString."<br/>");
            //echo('png chunk ICCP shortstring:' . $shortString."<br/>");
            $shortString = str_replace(' bytes)','',$shortString);
            //echo('png chunk ICCP shortstring2:' . $shortString."<br/>");
            $chunklength = intval($shortString);
            $iccbytes = $chunklength;

			// get profile size from 2nd part
			if(isset($chunkinfo[1]))
			{
				$iccdata = $chunkinfo[1];

            //echo('png chunk ICCP data:' . $iccdata."<br/>");
            $arriccdata = explode('entries,',$iccdata);
            $nparts = count($arriccdata);
            //echo('png chunk ICCP data noof parts' . $nparts."<br/>");

            //echo('png chunk ICCP data part 1' . $arriccdata[0]."<br/>");
            //echo('png chunk ICCP data part 2' . $arriccdata[1]."<br/>");
            $arriccdata2 = explode('bytes] ',$arriccdata[1]);

            $shortString = $arriccdata2[0];
			}
			else
			{
				$iccdata = $shortString;
			}
            //echo('png chunk ICCP shortstring2:' . $shortString."<br/>");
            $iccbytes = intval($shortString);
            break;
            //ICC_Profile directory with 17 entries, 3144 bytes


        default:
            break;
    }
  }

//  echo('png chunk keywords: <pre>');
  //print_r($pnginfodata);
//  print_r($arrtextchunkkeywords);
//  echo('</pre>');
  //echo('png chunk ICCP bytes:' . $fullString ." ===== " .$iccbytes."<br/>");

  $res = array();
  if($OS == "Windows")
    exec($perlbasedir . 'perl win_tools\ExifToolPerl\exiftool.pl -j '. escapeshellarg($lf),$rawpngdata);
  else
    exec('./lnx_tools/ExifTool/exiftool -j '. escapeshellarg($lf),$rawpngdata);
  $jsondata  = implode('',$rawpngdata);
  $pdata = json_decode($jsondata);
  $pngdata = $pdata[0];

  $text_bytes = 0;
  $pngtext_all = '';
  $pngbytes_all = 0;
  $kwcount = count($arrtextchunkkeywords);
  $kwcounter = 0;
  foreach($arrtextchunkkeywords as $keyword)
  {
    $kwcounter +=1;
    $pngtext = $pngdata->$keyword;
    $text_bytes = strlen($pngtext) + strlen($keyword);
    $pngtext_all .= $keyword . ': ' . $pngtext;
    if($kwcounter < $kwcount)
      $pngtext_all .= '<br/>';
    $pngbytes_all += $text_bytes;
  }
  //echo('png extracted keywords: <pre>');
  //print_r($pnginfodata);
  //print_r($pngtext_all);
  //echo('</pre>');

  $comment_text = '';//$pngtext_all;
  $comment_bytes = $pngbytes_all;
// Get the content that is in the buffer and put it in your file //


  $widthstr = $pngdata->ImageWidth;
  $heightstr = $pngdata->ImageHeight;
  $colourtypestr = $pngdata->ColorType;
  $bitdepth = $pngdata->BitDepth;
  switch($colourtypestr)
  {
  	case 'Greyscale':
                      $bitdepthstr = $bitdepth;
  		break;
  	case 'RGB':
                      $bitdepth = $bitdepth * 3;
                      $bitdepthstr = $bitdepth;
  		break;
  	case 'Palette':
                      $bitdepthstr = $bitdepth;
  		break;
  	case 'Greyscale with Alpha':
                      $bitdepthstr = $bitdepth;
  		break;
  	case 'RGB with Alpha':
                      $bitdepth = $bitdepth * 4;
                      $bitdepthstr = $bitdepth;
  		break;
                  default:
                      $bitdepthstr = $bitdepth;
  }


  $imageencoding = $pngdata->Compression;
  $interlacemethodstr = $pngdata->Interlace;
  if($interlacemethodstr == 'Adam7 Interlace')
      $interlacemethodstr = 'Progressive';
  else
      $interlacemethodstr = 'Non-Interlaced';
  $filtermethodstr = $pngdata->Filter;
  if(isset($pngdata->XMPToolkit))
      {
        $res = array();
        if($OS == "Windows")
  		    exec($perlbasedir . 'perl win_tools\ExifToolPerl\exiftool.pl -xmp -b ' . escapeshellarg($lf),$res);
        else
            exec('./lnx_tools/ExifTool/exiftool -xmp -b ' . escapeshellarg($lf),$res);
  		$xmpinfo = implode('',$res);

          $xmpbytes = strlen($xmpinfo);
      }


    $res = array();
    //exec('win_tools\pngcheck -v '. $lf,$pngstructure);
    if($OS == "Windows")
        exec($perlbasedir . 'perl win_tools\ExifToolPerl\exiftool.pl -v '. escapeshellarg($lf),$pngstructure);
    else
        exec('./lnx_tools/ExifTool/exiftool -v '. escapeshellarg($lf),$pngstructure);
    $png_structure = implode('<br/>',$pngstructure);
    $ne = strpos($png_structure,'PNG IHDR');
    $png_structure = substr($png_structure,$ne);


    $mdbytesize = $xmpbytes + $comment_bytes + $iccbytes;

    $png_signature = 'PNG';

    $arr = array($png_signature,$imageencoding,$png_structure,$widthstr,$heightstr,$bitdepthstr,$colourtypestr,$filtermethodstr,$interlacemethodstr,$mdbytesize,$comment_bytes,$comment_text,$xmpbytes,$iccbytes,$xmpinfo);

	return($arr);
}
function is64Bits() {
    return strlen(decbin(~0)) == 64;
}

?>
