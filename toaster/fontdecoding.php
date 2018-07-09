<?php
require_once 'base128.php'; 
header('Content-type: text/html; charset=utf-8');
// font decoding - WOFF WOFF2
function getFontSignature($lf)
{
//echo "reading signature of WOFF/WOFF2 file - " . $lf . "<br/>";
	$truefonttype = '';
	debug("checking true font type",$lf);
//echo(__FUNCTION__ . " checking true font type: ".$lf."<br/>");
	
	// load byte array with 16 bytes from file
	fgetBytesFromFile($lf,0,4);
	
	// Check for WOFF/WOFF2 Header
    list($bytesHdrSignature,$bytestr) = fgetStringOfNoofBytes(0,4);

//echo "bytes = " .  $bytestr;
    
    if($bytestr != "wOFF" and $bytestr != "wOF2")
        return false;
    else
        return $bytestr; // it's a WOFF or WOFF2!
} // end function getFontSignature


function readWOFFFont($lf)
{
    global $strHdrSignature;
    $fontname = '';
    // read woff font

/*
WOFF File Structure
WOFFHeader	        File header with basic font type and version, along with offsets to metadata and private data blocks.
TableDirectory	    Directory of font tables, indicating the original size, compressed size and location of each table within the WOFF file.
FontTables	        The font data tables from the input sfnt font, compressed to reduce bandwidth requirements.
ExtendedMetadata 	An optional block of extended metadata, represented in XML format and compressed for storage in the WOFF file.
PrivateData	        An optional block of private data for the font designer, foundry, or vendor to use
*/

//echo "<br/>reading WOFF/WOFF2 file - " . $lf . "<br/>";

/*

WOFFHeader
UInt32	signature	0x774F4646 'wOFF'
UInt32	flavor	The "sfnt version" of the input font.
UInt32	length	Total size of the WOFF file.
UInt16	numTables	Number of entries in directory of font tables.
UInt16	reserved	Reserved; set to zero.
UInt32	totalSfntSize	Total size needed for the uncompressed font data, including the sfnt header, directory, and font tables (including padding).
UInt16	majorVersion	Major version of the WOFF file.
UInt16	minorVersion	Minor version of the WOFF file.
UInt32	metaOffset	Offset to metadata block, from beginning of WOFF file.
UInt32	metaLength	Length of compressed metadata block.
UInt32	metaOrigLength	Uncompressed size of metadata block.
UInt32	privOffset	Offset to private data block, from beginning of WOFF file.
UInt32	privLength	Length of private data block.
*/
	// readWOFF/WOFF2 Header
	fgetBytesFromFile($lf,0,20);
    list($bytesHdrSignature,$strHdrSignature) = fgetStringOfNoofBytes(0,4);
//echo "<br/>Reading ".$strHdrSignature." fontfile - " . $lf . "<br/>";
    list($bytesHdrFlavour,$strHdrFlavour) = fgetDecimalOfNoofBytes(4,4,false,"flavour");
    list($bytesHdrLength,$strLength) = fgetDecimalOfNoofBytes(8,4,false,"filelength");
    list($bytesHdrNumTables,$strHdrNumTables) = fgetDecimalOfNoofBytes(12,2,false, "no of font tables");
    list($bytesHdrReserved,$strHdrReserved) = fgetDecimalOfNoofBytes(14,2,false,"reserved (0)");
    list($bytesHdrTotalSfntSize,$strTotalSfntSize) = fgetDecimalOfNoofBytes(16,4,false, "uncommpressed sfnt size");
    
    switch ($bytesHdrFlavour)
    {
        case "00010000":
            $fontflavour="TrueType";
            break;
        case "4F54544F":
            $fontflavour="CFF";
            break;
        default:
            $fontflavour = $bytesHdrFlavour;
    }
//echo ("Font flavour = ".$fontflavour."<br/>");
    
    switch ($strHdrSignature)
    {
    case "wOFF":
        fgetBytesFromFile($lf,20,24); // bytes 20 to 43
        list($bytesHdrMajorVersion,$strHdrMajorVersion) = fgetDecimalOfNoofBytes(0,2,false,"major version");
        list($bytesHdrMinorVersion,$strHdrMinorVersion) = fgetDecimalOfNoofBytes(2,2,false,"minor version");
        list($bytesHdrMetaOffset,$strMetaOffset) = fgetDecimalOfNoofBytes(4,4,false,"meta offset");
        list($bytesHdrMetaLength,$strMetaLength) = fgetDecimalOfNoofBytes(8,4,false,"meta length");
        list($bytesHdrMetaOrigLength,$strMetaOrigLength) = fgetDecimalOfNoofBytes(12,4,false,"uncompressed meta length");
        list($bytesHdrPrivOffset,$strPrivOffset) = fgetDecimalOfNoofBytes(16,4,false);
        list($bytesHdrPrivLength,$strPrivLength) = fgetDecimalOfNoofBytes(20,4,false);
        
        $TableDirStartPos = 44;

        if((int)$strMetaLength > 0)
            getWoffMetaData($lf,$strMetaOffset,$strMetaLength,$strMetaOrigLength);
        list($fontname,$cmap) = getWoffTableDirectory($lf,$TableDirStartPos,(int)$strHdrNumTables,$fontflavour);
        break;

        case "wOF2":

            fgetBytesFromFile($lf,20,28); // bytes 20 to 47
            list($bytesHdrTotalCompressedSize,$strHdrTotalCompressedSize) = fgetDecimalOfNoofBytes(0,4,false,"total compressed size");
            list($bytesHdrMajorVersion,$strHdrMajorVersion) = fgetDecimalOfNoofBytes(4,2,false,"major version");
            list($bytesHdrMinorVersion,$strHdrMinorVersion) = fgetDecimalOfNoofBytes(6,2,false,"minor version");
            list($bytesHdrMetaOffset,$strMetaOffset) = fgetDecimalOfNoofBytes(8,4,false,"meta offset");
            list($bytesHdrMetaLength,$strMetaLength) = fgetDecimalOfNoofBytes(12,4,false,"meta length");
            list($bytesHdrMetaOrigLength,$strMetaOrigLength) = fgetDecimalOfNoofBytes(16,4,false,"uncompressed meta length");
            list($bytesHdrPrivOffset,$strPrivOffset) = fgetDecimalOfNoofBytes(20,4);
            list($bytesHdrPrivLength,$strPrivLength) = fgetDecimalOfNoofBytes(24,4);
            
            $TableDirStartPos = 48;
            
            // if((int)$strMetaLength > 0)
            //     getWoffMetaData($lf,$strMetaOffset,$strMetaLength,$strMetaOrigLength);

            list($startOfCompressedFontData) = getWoff2TableDirectory($lf,$TableDirStartPos,(int)$strHdrNumTables,$fontflavour);
            // then fontdata
            $cmap = '';
            //the data for the font tables is compressed in a single data stream comprising all the font tables.
            readWoff2FontData($lf,$startOfCompressedFontData,$strHdrTotalCompressedSize);
   
            break;
    }
//echo("<br/>return :".$strHdrSignature . " " . $strHdrFlavour . "; ver: " . $strHdrMajorVersion . "/" . $strHdrMinorVersion. "; meta: " . $strMetaOffset . " " .$strMetaLength . " ". $fontname);
    return array($fontname,$cmap);
} // end function readWOFFFont


function getWoffTableDirectory($lf,$startPos,$noofTables,$fontflavour)
{
    $fontname = '';
    //echo "No. of tables in WOFF font: " .$noofTables . "<br/>";
    /*
    The table directory is an array of WOFF table directory entries, as defined below. 
    The directory follows immediately after the WOFF file header; 
    therefore, there is no explicit offset in the header pointing to this block. 
    Its size is calculated by multiplying the numTables value in the WOFF header times
    the size of a single WOFF table directory. Each table directory entry specifies the 
    size and location of a single font data table.

    WOFF TableDirectoryEntry
    UInt32	tag	4-byte sfnt table identifier.
    UInt32	offset	Offset to the data, from beginning of WOFF file.
    UInt32	compLength	Length of the compressed data, excluding padding.
    UInt32	origLength	Length of the uncompressed table, excluding padding.
    UInt32	origChecksum	Checksum of the uncompressed table.
    */
    $offsettables = $startPos + ($noofTables+1) * 20;
    //echo "FontTables start: " . $offsettables . "<br/>";
    for ($t= 0; $t < $noofTables; $t++) {
        $tabStartPos = $startPos + ($t * 20);
        fgetBytesFromFile($lf,$tabStartPos,20);
    //echo "Reading Font Table Directory Entry: number: $t from " . $tabStartPos. " to" . ($tabStartPos + 20) . " = ";

        list($bytesTabTag,$strTabTag) = fgetStringOfNoofBytes(0,4,false,"sfnt table identifier");
        list($bytesTabOffset,$strTabOffset) = fgetDecimalOfNoofBytes(4,4,false,"offset");
        list($bytesTabCompLength,$strTabCompLength) = fgetDecimalOfNoofBytes(8,4,false,"compLength");
        list($bytesTabOrigLength,$strTabOrigLength) = fgetDecimalOfNoofBytes(12,4,false,"origLength");
        list($bytesTabChecksum,$strTabChecksum) = fgetDecimalOfNoofBytes(16,4,false,"Checksum");

    //echo $strTabTag . ": offset:" . $strTabOffset. ": complen " . $strTabCompLength . "; origlen: " . $strTabOrigLength . "<br/>";

    //echo ("Font name table found at $strTabOffset<br/>");
        // parse the table
        list($data) = readTrueTypeTable($lf,$strTabTag,(int)$strTabOffset,(int)$strTabCompLength,(int)$strTabOrigLength,(int)$offsettables);
        if($strTabTag == "name")
        {
            $fontname = $data;
        } // end for each table in directory 
        if($strTabTag == "cmap")
        {
            $cmap = $data;
        } 
    } // end for
    //echo ("Font name table found - fontname = " . $fontname . "<br/>");
    return array($fontname,$cmap);
} // end function getWoffTableDirectory



function readWoff2FontData($lf,$startOfCompressedFontData,$TotalCompressedSize)
{
    global $byteArray;

//echo "uncompressing WOFF2 Font Data, from byte " . $startOfCompressedFontData . " for " . $TotalCompressedSize . " bytes<br/>";

    fgetBytesFromFile($lf,$startOfCompressedFontData,$TotalCompressedSize);
    $compressedBin = hex2bin(implode("", $byteArray)); // conveet to binary
    //echo "noof bytes compressd = " . sizeof($byteArray) ."<br/>";
    file_put_contents("c:\\temp\compressed_woff2.txt",$compressedBin); 
    $uncompressedBin = brotli_decode($compressedBin);
//echo "noof bytes uncompressed = " . sizeof($uncompressedBin) ."<br/>";
    file_put_contents("c:\\temp\uncompressed_woff2.txt",$uncompressedBin);  




}



function decodeUIntBase128($octets)
{
    $byteCount = 0;
    $bitsPerOctet = 7;
    $value = gmp_init(0, 10);
    $i = 0;
    $leftShift = function ($number, $positions) {
        return gmp_mul($number, gmp_pow(2, $positions));
    };
    while (true) {
        if (!isset($octets[$i])) {
            throw new InvalidArgumentException(sprintf('Malformed base-128 encoded value (0x%s).', strtoupper(bin2hex($octets)) ?: '0'));
        }
        $byteCount++;
        $octet = gmp_init(ord($octets[$i++]), 10);
        $l1 = $leftShift($value, $bitsPerOctet);
        $r1 = gmp_and($octet, 0x7f);
        $value = gmp_add($l1, $r1);
        if (0 === gmp_cmp(gmp_and($octet, 0x80), 0)) {
            break;
        }
    }
    return array(gmp_strval($value),$byteCount);
}

function getWoff2TableDirectory($lf,$startPos,$noofTables,$fontflavour)
{
    global $byteArray;
//   echo "No. of tables in WOFF2 font: " .$noofTables . "<br/>";
/*
The table directory is an array of WOFF table directory entries, as defined below. 
The directory follows immediately after the WOFF file header; 
therefore, there is no explicit offset in the header pointing to this block. 
Its size is calculated by multiplying the numTables value in the WOFF header times
the size of a single WOFF table directory. Each table directory entry specifies the 
size and location of a single font data table.

WOFF2 TableDirectoryEntry
UInt8  	        flags  	        table type and flags
UInt32  	    tag  	        4-byte tag (optional)
UIntBase128  	origLength  	length of original table
UIntBase128  	transformLength transformed length (if applicable)  

*/
    $tabPos = $startPos;
    for ($t= 0; $t < $noofTables; $t++) {
        fgetBytesFromFile($lf,$tabPos,1);
//echo "<br/>Reading Font Table Directory Entry: $t from " . $tabPos. " bytes<br/>";
        list($bytesFlag,$strFlag) = fgetDecimalOfNoofBytes(0,1,false,"table type and flags");
        $tabPos = $tabPos + 1;

        //list($bytesOrigLength,$bytesOrigLength) = fgetDecimalOfNoofBytes(5,5,false,"origLength");
        //list($bytesTransformLength,$strTransformLength) = fgetDecimalOfNoofBytes(10,5,false,"transformLength");

        // decode flag to identify table type
        $binFlag = substr("00000000",0,8 - strlen(decbin($strFlag))) . decbin($strFlag);
        //echo "flag  = " . $binFlag. "<br/>";
        
        //echo "dec to bin: " . $strFlag . " = " . decbin($strFlag);
        $flagtabletype = bindec($binFlag) & bindec('111111');

        $transformvno = decbin($strFlag >> 5) & bindec('11');
        
        switch ($flagtabletype)
        {
            case 0:
                $tabletype = 'cmap';
 //echo("woff 2 'cmap' table found");
                break;
            case 1:
                $tabletype = 'head';
                break;
            case 2:
                $tabletype = 'hhea';
                break;
            case 3:
                $tabletype = 'hmtx';
                break;
            case 4:
                $tabletype = 'maxp';
                break;
            case 5:
                $tabletype = 'name';
                break;
            case 6:
                $tabletype = 'OS/2';
                break;
            case 7:
                $tabletype = 'post';
                break;    
            case 8:
                $tabletype = 'cvt';
                break;   
            case 9:
                $tabletype = 'fpgm';
                break;
            case 10:
                $tabletype = 'glyf';
                break;  
            case 11:
                $tabletype = 'loca';
                break;  
            case 12:
                $tabletype = 'prep';
                break; 
            case 13:
                $tabletype = 'CFF';
                break;  
            case 14:
                $tabletype = 'VORG';
                break;  
            case 15:
                $tabletype = 'EBDT';
                break;  
            case 16:
                $tabletype = 'ELBC';
                break;  
            case 17:
                $tabletype = 'gasp';
                break;  
            case 18:
                $tabletype = 'hdmx';
                break;
            case 19:
                $tabletype = 'kern';
                break;  
            case 20:
                $tabletype = 'LTSH';
                break;  
            case 21:
                $tabletype = 'PCLT';
                break;  
            case 22:
                $tabletype = 'VDMX';
                break;  
            case 23:
                $tabletype = 'vhea';
                break;  
            case 24:
                $tabletype = 'vmtx';
                break;  
            case 25:
                $tabletype = 'BASE';
                break;  
            case 26:
                $tabletype = 'GDEF';
                break;  
            case 27:
                $tabletype = 'GPOS';
                break;  
            case 28:
                $tabletype = 'GSUB';
                break;  
            case 29:
                $tabletype = 'EBSC';
                break;  
            case 30:
                $tabletype = 'JSTF';
                break;  
            case 31:
                $tabletype = 'MATH';
                break;  
            case 32:
                $tabletype = 'CBDT';
                break;  
            case 33:
                $tabletype = 'CBLC';
                break;  
            case 34:
                $tabletype = 'COLR';
                break;  
            case 35:
                $tabletype = 'CPAL';
                break;  
            case 36:
                $tabletype = 'SVG';
                break;  
            case 37:
                $tabletype = 'sbix';
                break;  
            case 38:
                $tabletype = 'acnt';
                break;  
            case 39:
                $tabletype = 'avar';
                break;  
            case 40:
                $tabletype = 'bdat';
                break;  
            case 41:
                $tabletype = 'bloc';
                break;  
            case 42:
                $tabletype = 'bsln';
                break;  
            case 43:
                $tabletype = 'cvar';
                break;  
            case 44:
                $tabletype = 'fdsc';
                break; 
            case 45:
                $tabletype = 'feat';
                break; 
            case 46:
                $tabletype = 'fmtx';
                break; 
            case 47:
                $tabletype = 'fvar';
                break; 
            case 48:
                $tabletype = 'gvar';
                break; 
            case 49:
                $tabletype = 'hsty';
                break; 
            case 50:
                $tabletype = 'just';
                break; 
            case 51:
                $tabletype = 'lcar';
                break; 
            case 52:
                $tabletype = 'mort';
                break; 
            case 53:
                $tabletype = 'morx';
                break; 
            case 54:
                $tabletype = 'opdb';
                break; 
            case 55:
                $tabletype = 'prop';
                break; 
            case 56:
                $tabletype = 'trak';
                break; 
            case 57:
                $tabletype = 'Zapf';
                break; 
            case 58:
                $tabletype = 'Silf';
                break; 
            case 59:
                $tabletype = 'Glat';
                break; 
            case 60:
                $tabletype = 'Gloc';
                break; 
            case 61:
                $tabletype = 'Feat';
                break; 
            case 62:
                $tabletype = 'Sill';
                break; 

            case 63:
            default:
                $tabletype = 'arbitrary tag follows or unknown';
                fgetBytesFromFile($lf,$tabPos,4);
                list($bytesOptTag,$strOptTag) = fgetStringOfNoofBytes(0,4,false,"optional tag");
                $tabpos = $tabpos + 4;                
                break;
        } // end switch flagtabletype


//echo "tabletype: " . $flagtabletype . " = " . $tabletype. "; transformation version: " . $transformvno .  "<br/>";
//echo ("tabletype: " . $flagtabletype . " getWoff2TableDirectory, tab pos = " . $tabPos."<br/>");
        // decode base 128 fields
        fgetBytesFromFile($lf,$tabPos,5);
        $bytesOrigLength = getBytes(0,5,false,"original length");
        //echo ("str = " . $bytesOrigLength. ";<br/>");
        list($olength,$noofBytesOL) = decodeUIntBase128($bytesOrigLength);
        $tabPos = $tabPos + $noofBytesOL;
//echo "tabletype: " . $flagtabletype . " Font table original length = " . $olength . " - noof bytes = " . $noofBytesOL ."<br/>";

        if($transformvno > 0 or (($tabletype == 'glyf' and $transformvno !=3) or ($tabletype == 'loca' and $transformvno !=3) ))
        {
            fgetBytesFromFile($lf,$tabPos,5);
            $bytesOrigLength = getBytes(0,5,false,"transform length");
            list($olength,$noofBytesTL) = decodeUIntBase128($bytesOrigLength);
            $tabPos = $tabPos + $noofBytesTL;
//echo "tabletype: " . $flagtabletype . " Font table transformed length = " . (int)$olength . " - noof bytes = " . $noofBytesTL ."<br/>";
        }

//echo ("tabletype: " . $flagtabletype . " getWoff2TableDirectory, tab pos = " . $tabPos."<br/>");
        

    } // end for each table in directory 


    // sort table directory entries





    return array($tabPos);
} // end function getWoff2TableDirectory


function readTrueTypeTable($lf,$name,$offset,$length,$olength,$ftoffset)
{
//echo ("Font table '". $name . "' found at $offset - compressed length = $length bytes<br/>");
    /* 
    The Naming Table is organized as follows:
    
    Type	Description
    USHORT	Format selector (=0). 
    USHORT	Number of NameRecords that follow n.
    USHORT	Offset to start of string storage (from start of table).
    n  NameRecords	The NameRecords.
    (Variable)	Storage for the actual string data.
     */
    fgetBytesFromFile($lf,$offset,$length);
    
    list($bytesCompressed,$strCompressed) = fgetStringOfNoofBytes(0,$length,false,$name . " (compressed)");
    
    if($length != $olength)    
    {
        $uncompressedBytes = compressedHex2Hex($name,$bytesCompressed);
//echo ("Font table '". $name . "' found at $offset - uncompressed length = ".$uclength/2 ." bytes<br/>");
    }
    else
    {
        $uncompressedBytes = $bytesCompressed;
//echo ("Font table '". $name . "' found at $offset - (no compression)<br/>");
    }
    $uclength = strlen($uncompressedBytes) / 2; // div by 2 to get noof bytes rather than string
//    file_put_contents("c:\\temp\uncompressed_" . $name . ".txt",$uncompressedBytes);
    //list($bytesNoofNameRecords,$strNoofNameRecords) = fgetDecimalOfNoofBytesCompressed(2,2,true,"no. of name records");
    //list($bytesOffset,$strOffset) = fgetDecimalOfNoofBytesCompressed(4,2,true,"offset");

    switch ($name)
    {
        case "head":
            decodeHead($uncompressedBytes,$ftoffset);
            break;
        case "name":
            list($fontname) = decodeName($lf,$uncompressedBytes,$name,$ftoffset);
            return array($fontname);
            break;
        case "cmap":
//echo("woff->ttf 'cmap' table found"."<br/>");
            list($cmapdata) = decodeTTFCMAP($uncompressedBytes,$uclength);
// echo "readTrueTypeTable<pre>";
// print_r($cmapdata);
// echo "</pre>";
            return array($cmapdata);
            break;
        case "post":
//echo("woff->ttf 'post' table found"."<br/>");
            list($cmapdata) = decodeTTFPOST($uncompressedBytes,$uclength);
            break;
        default:

    }

} // end function readTrueTypeTable

function decodeTTFPOST($uncompressedHex,$uclength)
{
//echo "<br/>TTF POST: length:" . $uclength  , "<br/>" . $uncompressedHex . "<br/>";
    /*
    The 'post' table contains information needed to use a TrueType font on a PostScript printer

    2.2 Fixed	format	Format of this table
    2.2 Fixed	italicAngle	Italic angle in degrees
    2 FWord	underlinePosition	Underline position
    2 FWord	underlineThickness	Underline thickness
    4 uint32	isFixedPitch	Font is monospaced; set to 1 if the font is monospaced and 0 otherwise (N.B., to maintain compatibility with older versions of the TrueType spec, accept any non-zero value as meaning that the font is monospaced)
    4 uint32	minMemType42	Minimum memory usage when a TrueType font is downloaded as a Type 42 font
    4 uint32	maxMemType42	Maximum memory usage when a TrueType font is downloaded as a Type 42 font
    4 uint32	minMemType1	Minimum memory usage when a TrueType font is downloaded as a Type 1 font
    4 uint32	maxMemType1	Maximum memory usage when a TrueType font is downloaded as a Type 1 font
    = 36 bytes
    */

    list($bytesFormat,$decFormat) = fgetDecimalOfNoofBytesLocal($uncompressedHex,0,4,false,"POST format");
    // italic angle 4,2
    // underline position 6, 4
    // underline thickness 10, 4
    // isfixedpitch 14, 4
    // mimmemtype42 18, 4
    // maxmemtype42 22, 4
    // minmemtype1 26, 4
    // maxmemtyp1 30, 4

    
    // startpos = 36;
        $noofglyphs = 0;
        switch($decFormat)
        {
            case 1:
              //  echo "TTF POST Format 1 - standard 268 glyphs<br/>";
                $noofglyphs = 258; // standard
                break;
            case 131072: // format 2.0
               // echo "TTF POST Format 2<br/>";
                list($bytesNumGlyphs,$noofglyphs) = fgetDecimalOfNoofBytesLocal($uncompressedHex,36,2,false,"number of glyphs");
                break;
            case 196608: // format 3.0
               // echo "TTF POST Format 3: ";
               // echo ("This format specifies that no PostScript name information is provided for the glyphs in this font.<br/>");
                break;
            default:
            list($bytesNumGlyphs,$noofglyphs) = fgetDecimalOfNoofBytesLocal($uncompressedHex,36,2,false,"def number of glyphs");

        }


    // }
        return array($noofglyphs);
}

function decodeTTFCMAP($uncompressedHex,$uclength)
{
//echo "<br/>TTF CMAP: length:" . $uclength  , "<br/>" . $uncompressedHex . "<br/>";
    /*
    The Character To Glyph Index Mapping Table is organized as follows:

    Type	Name	    Description
    uint16	version	    Table version number (0).
    uint16	numTables	Number of encoding tables that follow.
    EncodingRecord	    encodingRecords[numTables]
    */

    list($bytesVersion,$decVersion) = fgetDecimalOfNoofBytesLocal($uncompressedHex,0,2,false,"version");
    list($bytesNumTables,$decNumTables) = fgetDecimalOfNoofBytesLocal($uncompressedHex,2,2,false,"number of tables");
    
    //echo "TTF CMAP has " . $decNumTables . " encoding tables<br/>";

    $startPos = 4;
    $subtableNum = 0;
    // read through encoding records, one by one - tables are 8 bytes in length
    for ($t= 0; $t < $decNumTables; $t++) {
        $subtableNum = $t + 1;
        $tabStartPos = $startPos + ($t * 8);
        list($bytesCMAPEncoding,$strNameRecord) = fgetStringOfNoofBytesLocal($uncompressedHex,$tabStartPos,8,false,"name record");
        
    //echo "encoding table: " . $t . "<br/>";
        list($bytesPlatformID,$strPlatformID) = fgetDecimalofNoofBytesLocal($bytesCMAPEncoding,0,2,false,"PlatformID");
        list($bytesPlatformEncoding,$strPlatformEncoding) = fgetDecimalOfNoofBytesLocal($bytesCMAPEncoding,2,2,false,"PlatformEncoding");
        list($bytesOffset,$decOffset) = fgetDecimalOfNoofBytesLocal($bytesCMAPEncoding,4,4,false,"Offset");
        switch ($strPlatformID)
        {
            case 0:
//echo "subtable: " . $subtableNum . ": platform id = " . $strPlatformID . " Unicode; encoding = " . $strPlatformEncoding ."; offset = 0x" . $bytesOffset . " (". $decOffset . ")<br/>";
                switch ($strPlatformEncoding)
                {
                    case 0:	$encid = "Default semantics";
                    break;
                    case 1:	$encid = "Version 1.1 semantics";
                    break;
                    case 2:	$encid = "ISO 10646 1993 semantics (deprecated)";
                    break;
                    case 3:	$encid = "Unicode 2.0 or later semantics (BMP only)";
                    break;
                    case 4:	$encid = "Unicode 2.0 or later semantics (non-BMP characters allowed)";
                    break;
                    case 5:	$encid = "Unicode Variation Sequences";
                    break;
                    case 6:	$encid = "Full Unicode coverage (used with type 13.0 cmaps by OpenType)";
                    break;
                }
                break;
            case 1:
//echo "subtable: " . $subtableNum. ": platform id = " . $strPlatformID . " Macintosh encoding = " . $strPlatformEncoding ."; offset = 0x" . $bytesOffset . " (". $decOffset . ")<br/>";
                break;
            case 3:
                switch ($strPlatformEncoding)
                {
                    case 0:	$encid = "Symbol";
                    break;
                    case 1:	$encid = "Unicode BMP-only (UCS-2)";
                    break;
                    case 2:	$encid = "Shift-JIS";
                    break;
                    case 3:	$encid = "PRC";
                    break;
                    case 4:	$encid = "BigFive";
                    break;
                    case 5:	$encid = "Johab";
                    break;
                    case 10:	$encid = "Unicode UCS-4";
                    break;
                }
//echo "subtable: " . $subtableNum. ": platform id = " . $strPlatformID . " Windows; encoding = " . $strPlatformEncoding ."; offset = 0x" . $bytesOffset . " (". $decOffset . ")<br/>";

                list($arraySubTables) = decodeCMAPSubTable($uncompressedHex,$strPlatformID,$strPlatformEncoding,$decOffset,$subtableNum);
                

                break;
            case 4:
//echo "subtable: " . $subtableNum . ": platform id = " . $strPlatformID . " Custom; encoding = " . $strPlatformEncoding ."; offset = 0x" . $bytesOffset . " (". $decOffset . ")<br/>";
                break;
        }
    }
    return array($arraySubTables);
} // end function decodeTTFCMAP

function decodeCMAPSubTable($uncompressedHex,$strPlatformID,$strPlatformEncoding,$decOffset,$subtableNum)
{
    list($bytesSubTableFormat,$decSubTableFormat) = fgetDecimalOfNoofBytesLocal($uncompressedHex,$decOffset,2,false,"cmap subtable format");
    //echo "subtable: " . $subtableNum. ": format:  " . $decSubTableFormat . "<br/>";

    if($strPlatformID == 3)
    {
        switch ($decSubTableFormat)
        {
            case 0:
                break;
            case 1:
                break;
            case 2:
                break;
            case 4:
                list($bytesSubTableLength,$decSubTableLength) = fgetDecimalOfNoofBytesLocal($uncompressedHex,$decOffset+2,2,false,"cmap subtable length");
                list($bytesSubTableLangCode,$decSubTableLangCode) = fgetDecimalOfNoofBytesLocal($uncompressedHex,$decOffset+4,2,false,"cmap subtable language code");
                list($bytesSubTableSegCountX2,$decSubTableSegCountX2) = fgetDecimalOfNoofBytesLocal($uncompressedHex,$decOffset+6,2,false,"cmap subtable segCountx2");
                $decSubTableSegCount = $decSubTableSegCountX2 / 2;
                list($bytesSubTableSearchRange,$decSubTableSearchRange) = fgetDecimalOfNoofBytesLocal($uncompressedHex,$decOffset+8,2,false,"cmap subtable searchRange");
                $searchRange = 2 * (pow(2,FLOOR(log($decSubTableSegCount,2))));
                list($bytesSubTableEntrySelector,$decSubTableEntrySelector) = fgetDecimalOfNoofBytesLocal($uncompressedHex,$decOffset+10,2,false,"cmap subtable entrySelector");
                $entrySelector = $decSubTableEntrySelector;
                list($bytesSubTableRangeShift,$decSubTableRangeShift) = fgetDecimalOfNoofBytesLocal($uncompressedHex,$decOffset+12,2,false,"cmap subtable rangeShift");
                $rangeShift = (2 * $decSubTableSegCount) - $decSubTableSearchRange;
//echo ("segCount: " . $decSubTableSegCount  . "; searchRange: " . $searchRange . "; entrySelector: " . $entrySelector . "; rangeShift: " . $rangeShift  ."<br/>");

                // segments
                /* Each segment is described by a startCode, an endCode, an idDelta and an idRangeOffset.
                 These are used for mapping the character codes in the segment. 
                 The segments are sorted in order of increasing endCode values.
                */
                $seglength = 10;
                // create array of length of number of segments
                $arraySubTables = array();


                $segPos = $decOffset+14;
                // read segment details - end codes
                for ($s= 0; $s < $decSubTableSegCount; $s++) {
                    $segmentNum = $s + 1;
                    
                    list($bytesSegEndCode,$strSegEndCode) = fgetStringOfNoofBytesLocal($uncompressedHex,$segPos,2,false,"seg endCode");
                    $segPos = $segPos + 2;
                    $arraySubTables[] = array('',$bytesSegEndCode,0,0);
                }
               
                list($bytesSegReservePad,$decSegReservePad) = fgetDecimalOfNoofBytesLocal($uncompressedHex,$segPos+2,2,false,"seg reservePad");
                
                $segPos = $segPos + 2;
                // read segment details - start codes
                for ($s= 0; $s < $decSubTableSegCount; $s++) {
                    $segmentNum = $s + 1;
                    
                    list($bytesSegStartCode,$strSegStartCode) = fgetStringOfNoofBytesLocal($uncompressedHex,$segPos,2,false,"seg startCode");
                    $segPos = $segPos + 2;
                    $arraySubTables[$s][0] = $bytesSegStartCode;
                }
                
                // read segment details - iddelta
                for ($s= 0; $s < $decSubTableSegCount; $s++) {
                    $segmentNum = $s + 1;
                    
                    list($bytesSegIdDelta,$decSegIdDelta) = fgetDecimalOfNoofBytesLocal($uncompressedHex,$segPos,2,false,"seg idDelta");
                    $segPos = $segPos + 2;
                    $arraySubTables[$s][2] = $decSegIdDelta;
                }

                // read segment details - iddelta
                for ($s= 0; $s < $decSubTableSegCount; $s++) {
                    $segmentNum = $s + 1;
                    
                    list($bytesSegRangeOffset,$decSegRangeOffset) = fgetDecimalOfNoofBytesLocal($uncompressedHex,$segPos,2,false,"seg RangeOffset");
                    $segPos = $segPos + 2;
                    $arraySubTables[$s][3] = $decSegRangeOffset;
                }
// echo "decodeCMAPSubTable<pre>";
// print_r($arraySubTables);
// echo "</pre>";
    
                return array($arraySubTables);

                    //list($bytesSegGlyphArrayIndex,$decSegGlyphArrayIndex) = fgetDecimalOfNoofBytesLocal($uncompressedHex,$segPos+10,2,false,"seg Glyph Array Index");

                    //echo ("Seg " . $segmentNum . ": St = " . $bytesSegStartCode. ", En = " . $bytesSegEndCode. ", D = " . $decSegIdDelta . ", RO = " . $decSegRangeOffset . ", gID# = " . $decSegGlyphArrayIndex . "<br/>");
                

                break;
            default:
                break;

        } // end switch table format

    } // emd if platform 3 windows
} // end function decodeCMAPSubTable


function decodeHead($uncompressedHex)
{
//echo "TTF HEAD<br/>";
    list($bytesMajorVersion,$strMajorVersion) = fgetStringOfNoofBytesLocal($uncompressedHex,0,2,false,"major version");
    list($bytesMinorVersion,$strMinorVersion) = fgetStringOfNoofBytesLocal($uncompressedHex,2,2,false,"minor version");
    list($bytesFontRevision,$strFontRevision) = fgetStringOfNoofBytesLocal($uncompressedHex,4,4,false,"FontRevision");
    list($bytesCheckSumAdjustment,$strCheckSumAdjustment) = fgetDecimalOfNoofBytesLocal($uncompressedHex,8,4,false,"CheckSumAdjustment");
    list($bytesmagicNumber,$strmagicNumber) = fgetDecimalOfNoofBytesLocal($uncompressedHex,12,4,false,"magicNumber");

//echo "Head magic number bytes: " . $bytesmagicNumber . "<br/>";

    list($bytesFlags,$strFlags) = fgetDecimalOfNoofBytesLocal($uncompressedHex,16,2,false,"flags");
    list($bytesunitsperem,$strunitsperem) = fgetDecimalOfNoofBytesLocal($uncompressedHex,18,2,false,"unitsperem");
    list($bytesdatecreated,$strdatecreated) = fgetDecimalOfNoofBytesLocal($uncompressedHex,20,8,false,"datecreated");
    $date = new DateTime('1904-01-01');
    $date->add(new DateInterval('PT'.$strdatecreated .'S'));
//echo "date created: " . $date->format('Y-m-d H:i:s') . "<br/>";
    list($bytesdatemodified,$strdatemodified) = fgetDecimalOfNoofBytesLocal($uncompressedHex,20,8,false,"datemodified");
    $date = new DateTime('1904-01-01');
    $date->add(new DateInterval('PT'.$strdatemodified .'S'));
//echo "date modified: " . $date->format('Y-m-d H:i:s') . "<br/>";
}


function decodeName($lf,$uncompressedHex,$name,$ftoffset)
{
//echo "TTF NAME<br/>";
    list($bytesFormatSelector,$strFormatSelector) = fgetDecimalOfNoofBytesLocal($uncompressedHex,0,2,false,"FormatSelector");
    list($bytesNumNameRecords,$strNumNameRecords) = fgetDecimalOfNoofBytesLocal($uncompressedHex,2,2,false,"NumNameRecords");
    list($bytesStorageOffset,$strStorageOffset) = fgetDecimalOfNoofBytesLocal($uncompressedHex,4,2,false,"Offset to String Storage");

    // start of NameRecords = 6
    $startPos = 6;
//echo "FontTable offset: " . $ftoffset . "<br/>";
//echo "NameRecord start: " . $startPos . "<br/>";
//echo "NameRecord Storage offset: " . $strStorageOffset . "<br/>";
    $nrtotalsize = ($strNumNameRecords * 12);
//echo "NameRecord count: " . $strNumNameRecords . "<br/>";    
//echo "NameRecord total size: " . $nrtotalsize . "<br/>";    

    for ($t= 0; $t < $strNumNameRecords; $t++) {
        $tabStartPos = $startPos + ($t * 12);
//echo "<br/>NameRecord " . ($t+1) . ":<br/>";
        list($bytesNameRecord,$strNameRecord) = fgetStringOfNoofBytesLocal($uncompressedHex,$tabStartPos,12,false,"name record");

        list($bytesPlatformID,$strPlatformID) = fgetDecimalofNoofBytesLocal($bytesNameRecord,0,2,false,"PlatformID");
        list($bytesPlatformEncoding,$strPlatformEncoding) = fgetDecimalOfNoofBytesLocal($bytesNameRecord,2,2,false,"PlatformEncoding");
        list($bytesLanguageID,$strLaguageID) = fgetDecimalOfNoofBytesLocal($bytesNameRecord,4,2,false,"LanguageID");
        list($bytesNameID,$strNameID) = fgetDecimalOfNoofBytesLocal($bytesNameRecord,6,2,false,"NameID");
        list($bytesStrLength,$strStrLength) = fgetDecimalOfNoofBytesLocal($bytesNameRecord,8,2,false,"String Length");
        list($bytesStrOffset,$strStrOffset) = fgetDecimalOfNoofBytesLocal($bytesNameRecord,10,2,false,"StrOffset");


        // get the string for the name record

        $strOffset = $strStorageOffset + $strStrOffset;
//echo "NameRecord string start: " . $strStorageOffset . " + " . $strStrOffset . " = " . $strOffset . "<br/>";  
        list($bytes,$str) = fgetStringOfNoofBytesLocal($uncompressedHex,$strOffset,$strStrLength,false,"String");
        
        
        switch ((int)$strNameID)
        {
            case 0:
                $name = "Copyright Notice";
                break;
            case 1:
                $name = "Font Family";
                break;
            case 2:
                $name = "Font Subfamily";
                break;
            case 3:
                $name = "Unique Font Identifier";
                break;
            case 4:
                $name = "Full Font Name";
                if($str[0]== "&" and $str[1] == "")
                {
//echo "2 bytes:'".$str[0] . $str[1]." '<br/>" ; stripping Unicode Character 'WHITE RIGHT POINTING INDEX' (U+261E)
                    $str = str_replace("&","",$str);
                }
                $fontname = $str;
//echo "'name:' fontname = " . $fontname."<br/>";
                break;
            case 5:
                $name = "Version String";
                break;
            case 6:
                $name = "Postscript Name";
                break;
            case 7:
                $name = "Trademark Notice";
                break;
            case 8:
                $name = "Manufacturer Name";
                break;
            case 9:
                $name = "Font Designer";
                break;
            case 10:
                $name = "Description";
                break;
            case 11:
                $name = "URL of Font Vendor";
                break;
            case 12:
                $name = "URL of Font Designer";
                break;
            case 13:
                $name = "License Description";
                break;
            case 14:
                $name = "License Information URL";
                break;
            case 15:
                $name = "reserved";
                break;
            case 16:
                $name = "Preferred Family";
                break;
            case 17:
                $name = "Preferred Subfamily";
                break;
            case 18:
                $name = "Compatible Full";
                break;
            case 19:
                $name = "Sample Text";
                break;
             default:
                $name = "$strNameID = unknown";
                break;
        } 

//echo "NameRecord " . ($t + 1) . ": " . $name . ": " . htmlentities($str, ENT_QUOTES, "UTF-8") . "<br/>";
    }


    return array($fontname);
  }

function fgetStringOfNoofBytesLocal($byteArray,$pos,$noofBytes,$debug = false,$field = '')
{
    //$byteArray = bin2hex($bin) . "<br/>";
    //echo $byteArray;
    
    $opos = $pos;
    $onoofBytes = $noofBytes;
    $pos = $pos * 2;
    $noofBytes = $noofBytes * 2;

    $str = '';
	$hexstr = '';
	$endbyte = $pos + $noofBytes - 1;
	for ($i = $pos; $i <= $endbyte; $i++)
	 {
    	@$hexstr .= $byteArray[$i];
	}	
    $str = hex2str($hexstr);
    if($debug)
    {
        //echo($pos . " for " .$noofBytes . " bytes; ". $field . " Hex: $hexstr = string: '$str'<br/>");
        echo($opos . " for " .$onoofBytes . " bytes:". $field . " Hex: $hexstr <br/>");
    }
        return array($hexstr,$str);
}

function fgetDecimalOfNoofBytesLocal($byteArray,$pos,$noofBytes,$debug = false,$field = '')
{
   // $byteArray = bin2hex($bin) . "<br/>";

    $opos = $pos;
    $onoofBytes = $noofBytes;
    $pos = $pos * 2;
    $noofBytes = $noofBytes * 2;

	$str = '';
	$hexstr = '';
	$endbyte = $pos + $noofBytes - 1;
	for ($i = $pos; $i <= $endbyte; $i++)
	 {
    	$hexstr .= $byteArray[$i];
	
	}	
	$str = hexdec($hexstr);
    //echo("Hex: $hexstr = Dec: $str<br/>");
    if($debug)
        echo($opos . " for " .$onoofBytes . " bytes; ". $field . "  Hex: $hexstr = Decimal: $str<br/>");
	return array($hexstr,$str);
}

function compressedHex2str($name,$compressedHex)
{
    global $strHdrSignature;

    /*
    zlib magic headers
    
    78 01 - No Compression/low
    78 9C - Default Compression
    78 DA - Best Compression 
*/
    $compressedBin = hex2bin($compressedHex);

    if($strHdrSignature == "wOFF")
        $uncompressed = zlib_decode($compressedBin);
    else
        $uncompressed = brotli_decode($compressedBin);

 //   file_put_contents("c:\\temp\compressed_" . $name . ".txt",$compressed);
//echo ("<br/><br/>".$uncompressed . " of " . strlen($uncompressed) . " in length<br/>");
}

function compressedHex2Hex($name,$compressedHex)
{
    global $strHdrSignature;
    /*
    zlib magic headers
    
    78 01 - No Compression/low
    78 9C - Default Compression
    78 DA - Best Compression 
*/  
    if($compressedHex[0] == 7 && $compressedHex[1] ==8)
    {
        $compressedBin = hex2bin($compressedHex);
        if($strHdrSignature == "wOFF")
            $uncompressed = zlib_decode($compressedBin);
        else
            $uncompressed = brotli_decode($compressedBin);
 //       file_put_contents("c:\\temp\compressed_" . $name . ".txt",$compressedBin);        
        $newhex = bin2hex($uncompressed);
    }
    else
    $newhex = $compressedHex;
//echo ("<br/><br/>".$uncompressed . " of " . strlen($uncompressed) . " in length<br/>");
    
    return $newhex;
}
function compressedHex2Bin($name,$compressedHex)
{
    global $strHdrSignature;
    /*
    zlib magic headers
    
    78 01 - No Compression/low
    78 9C - Default Compression
    78 DA - Best Compression 
*/
    $compressedBin = hex2bin($compressedHex);

    if($strHdrSignature == "wOFF")
        $uncompressed = zlib_decode($compressedBin);
    else
        $uncompressed = brotli_decode($compressedBin);

//    file_put_contents("c:\\temp\compressed_" . $name . ".txt",$compressedBin);
//    file_put_contents("c:\\temp\uncompressed_" . $name . ".txt",$uncompressed);
    //echo ("<br/><br/>".$uncompressed . " of " . strlen($uncompressed) . " in length<br/>");
    
    return $uncompressed;
}



function getWoffMetaData($lf,$strMetaOffset,$strMetaLength,$strMetaOrigLength)
{
    global $strHdrSignature;
    // Extended Metadata Block
    /*The metadata block consists of XML data compressed by zlib;
     the file header specifies both the size of the actual compressed
     and the original uncompressed size in order to facilitate memory allocation.
     */
    fgetBytesFromFile($lf,$strMetaOffset,$strMetaLength);
//echo ("looking for metadata from pos " . $strMetaOffset . " for " . $strMetaLength . " bytes<br/>");
    list($bytesMetadataBlock,$strMetadataBlock) = fgetStringOfNoofBytes(0,$strMetaLength,false,"metadata");
//echo "metadatablock bytes...<br/>";
//print_r($bytesMetadataBlock);
    if($strMetaLength != $strMetaOrigLength)
    {
    //     // decompress medatablock - use $bytesMetadataBlock
        $compressedBin = hex2bin($bytesMetadataBlock);
        //file_put_contents("c:\\temp\compressed.txt",$compressed);
        if($strHdrSignature == "wOFF")
            $uncompressed = zlib_decode($compressedBin);
        else
            $uncompressed = brotli_decode($compressedBin);
        //file_put_contents("c:\\temp\zlib_decode.txt",$uncompressed);
    }
    else
        $uncompressed = $strMetadataBlock;
    $xml = simplexml_load_string($uncompressed);
// echo ("<pre>");
// print_r($xml);
// echo ("</pre>");
} // end function getWoffMetaData


function fgetBytesFromFile($lf,$pos,$noofBytes)
{
	global $byteArray;
    $byteArray = array();
    	
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


function getBytes($pos,$noofBytes,$debug = false,$field = '')
{
    global $byteArray;
    $bytes = array();
    $p = 0;
    for ($i = $pos; $i < $noofBytes; $i++)
    {
        $bytes[$p] = $byteArray[$i];
        $p++;
   }
    return($bytes);
}


function fgetStringOfNoofBytes($pos,$noofBytes,$debug = false,$field = '')
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
    if($debug)
        //echo($pos . " for " .$noofBytes . " bytes; ". $field . " Hex: $hexstr = string: '$str'<br/>");
        echo($pos . " for " .$noofBytes . " bytes;<br/>". $field . ": $hexstr <br/>");
	return array($hexstr,$str);
}


function fgetDecimalOfNoofBytes($pos,$noofBytes,$debug = false,$field = '')
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
    if($debug)
        echo($pos . " for " .$noofBytes . " bytes; ". $field . "  Hex: $hexstr = Decimal: $str<br/>");
	return array($hexstr,$str);
}

function fgetDecimalOfNoofBytesCompressed($pos,$noofBytes,$debug = false,$field = '')
{
    global $byteArray;
    

	$str = '';
	$hexstr = '';
	$endbyte = $pos + $noofBytes - 1;
	for ($i = $pos; $i <= $endbyte; $i++)
	 {
    	$hexstr .= $byteArray[$i];
     }	
     


	$str = hexdec($uncompressed);
    //echo("Hex: $hexstr = Dec: $str<br/>");
    if($debug)
        echo($pos . " for " .$noofBytes . " bytes; ". $field . "  Hex: $hexstr = Decimal: $str<br/>");
	return array($uncompressed,$str);
}


function brotli_decode($bytes)
{
    global $OS;
    //debug('Brotli content encoding detected', "brotli");
//echo ("Decoding Brotli content<br/>");
    $brotli = true;
    $body = '';
    $str = '';
// decompress for brotl
    $res = array();
    if ($OS == "Windows")
    {
        $fni = 'c:\\Temp\\bri';
        $fno = 'c:\\Temp\\bro';
//$tempi = tempnam("c:\\temp\\", "bri");
//$tempo = tempnam("c:\\temp\\", "bro");
        file_put_contents($fni, $bytes);
        exec('win_tools\bro64 -d -f -i ' . $fni . " -o " . escapeshellarg($fno), $res);
        $body = file_get_contents($fno);
        //unlink($fni);
        //unlink($fno);

        $str = hexdec($body);

    }
    else
    {
        // add brotli linux deocder
        //exec('exiftool -xmp -b ' . escapeshellarg($lf),$res);
    }
    return array($body,$str);
}



?>