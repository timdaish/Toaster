<?php
/**
 * TTF table structure
 * Name Id	Meaning
 * 0	Copyright notice
 * 1	Font Family name.
 * 2	Font Subfamily name. Font style (italic, oblique) and weight (light, bold, black, etc.). A font with no particular differences in weight or style (e.g. medium weight, not italic) should have the string "Regular" stored in this position.
 * 3	Unique font identifier. Usually similar to 4 but with enough additional information to be globally unique. Often includes information from Id 8 and Id 0.
 * 4	Full font name. This should be a combination of strings 1 and 2. Exception: if the font is “Regular” as indicated in string 2, then use only the family name contained in string 1. This is the font name that Windows will expose to users.
 * 5	Version string. Must begin with the syntax ‘Version n.nn ‘ (upper case, lower case, or mixed, with a space following the number).
 * 6	Postscript name for the font.
 * 7	Trademark. Used to save any trademark notice/information for this font. Such information should be based on legal advice. This is distinctly separate from the copyright.
 * 8	Manufacturer Name.
 * 9	Designer. Name of the designer of the typeface.
 * 10	Description. Description of the typeface. Can contain revision information, usage recommendations, history, features, etc.
 * 11	URL Vendor. URL of font vendor (with protocol, e.g., http://, ftp://). If a unique serial number is embedded in the URL, it can be used to register the font.
 * 12	URL Designer. URL of typeface designer (with protocol, e.g., http://, ftp://).
 * 13	License description. Description of how the font may be legally used, or different example scenarios for licensed use. This field should be written in plain language, not legalese.
 * 14	License information URL. Where additional licensing information can be found.
  * 15	Reserved; Set to zero.
 * 16	Preferred Family (Windows only). In Windows, the Family name is displayed in the font menu. The Subfamily name is presented as the Style name. For historical reasons, font families have contained a maximum of four styles, but font designers may group more than four fonts to a single family. The Preferred Family and Preferred Subfamily IDs allow font designers to include the preferred family/subfamily groupings. These IDs are only present if they are different from IDs 1 and 2.
 * 17	Preferred Subfamily (Windows only). See above.
 * 18	Compatible Full (Mac OS only). On the Mac OS, the menu name is constructed using the FOND resource. This usually matches the Full Name. If you want the name of the font to appear differently than the Full Name, you can insert the Compatible Full Name here.
 * 19	Sample text. This can be the font name, or any other text that the designer thinks is the best sample text to show what the font looks like.
 * 20	PostScript CID findfont name.
 * 21-255	Reserved for future expansion.
 * 256-32767	F
 * 
 * 
 * 
 * 
 * ttfInfo class
 * Retrieve data stored in a TTF files 'name' table
 *
 * @original author Unknown
 * found at http://www.phpclasses.org/browse/package/2144.html
 *
 * @ported for used on http://www.nufont.com
 * @author Jason Arencibia
 * @version 0.2
 * @copyright (c) 2006 GrayTap Media
 * @website http://www.graytap.com
 * @license GPL 2.0
 * @access public
 *
 * @todo: Make it Retrieve additional information from other tables
 *
 */
class ttfInfo {
    /**
    * variable $_dirRestriction
    * Restrict the resource pointer to this directory and above.
    * Change to 1 for to allow the class to look outside of it current directory
    * @protected
    * @var int
    */
    protected $_dirRestriction = 1;
    /**
    * variable $_dirRestriction
    * Restrict the resource pointer to this directory and above.
    * Change to 1 for nested directories
    * @protected
    * @var int
    */
    protected $_recursive = 0;

    /**
    * variable $fontsdir
    * This is to declare this variable as protected
    * don't edit this!!!
    * @protected
    */
    protected $fontsdir;
    /**
    * variable $filename
    * This is to declare this varable as protected
    * don't edit this!!!
    * @protected
    */
    protected $filename;

    /**
    * function setFontFile()
    * set the filename
    * @public
    * @param string $data the new value
    * @return object reference to this
    */
    public function setFontFile($data)
    {
        if ($this->_dirRestriction && preg_match('[\.\/|\.\.\/]', $data))
        {
            $this->exitClass('Error: Directory restriction is enforced!');
        }



        $this->filename = $data;
        return $this;
    } // public function setFontFile

    /**
    * function setFontsDir()
    * set the Font Directory
    * @public
    * @param string $data the new value
    * @return object referrence to this
    */
    public function setFontsDir($data)
    {
        if ($this->_dirRestriction && preg_match('[\.\/|\.\.\/]', $data))
        {
            $this->exitClass('Error: Directory restriction is enforced!');
        }

        $this->fontsdir = $data;
        return $this;
    } // public function setFontsDir

    /**
    * function readFontsDir()
    * @public
    * @return information contained in the TTF 'name' table of all fonts in a directory.
    */
    public function readFontsDir()
    {
        if (empty($this->fontsdir)) { $this->exitClass('Error: Fonts Directory has not been set with setFontsDir().'); }
        if (empty($this->backupDir)){ $this->backupDir = $this->fontsdir; }

        $this->array = array();
        $d = dir($this->fontsdir);

        while (false !== ($e = $d->read()))
        {
            if($e != '.' && $e != '..')
            {
                $e = $this->fontsdir . $e;
                if($this->_recursive && is_dir($e))
                {
                    $this->setFontsDir($e);
                    $this->array = array_merge($this->array, readFontsDir());
                }
                else if ($this->is_ttf($e) === true)
                {
                    $this->setFontFile($e);
                    $this->array[$e] = $this->getFontInfo();
                }
            }
        }

        if (!empty($this->backupDir)){ $this->fontsdir = $this->backupDir; }

        $d->close();
        return $this;
    } // public function readFontsDir

    /**
    * function setProtectedVar()
    * @public
    * @param string $var the new variable
    * @param string $data the new value
    * @return object reference to this

    * DISABLED, NO REAL USE YET

    public function setProtectedVar($var, $data)
    {
        if ($var == 'filename')
        {
            $this->setFontFile($data);
        } else {
            //if (isset($var) && !empty($data))
            $this->$var = $data;
        }
        return $this;
    }
    */
    /**
    * function getFontInfo()
    * @public
    * @return information contained in the TTF 'name' table.
    */
    public function getFontInfo()
    {
        //error_log($this->filename);
        $fd = fopen ($this->filename, "r+");
        if($fd == false)
        {
            error_log($this->filename ." exited");
            return;
        }
        $this->text = fread ($fd, filesize($this->filename));
        fclose ($fd);

        $font_tags = array();
        $number_name_records_dec = 0;


        // get font header and use it to identify the font format
        // BIG ENDIAN
        $fonthdr_BIGENDIAN_4chars = ($this->dec2ord($this->text[0]).$this->dec2ord($this->text[1]).$this->dec2ord($this->text[2]).$this->dec2ord($this->text[3]));
// echo($this->filename . "; font hdr:". $fonthdr_BIGENDIAN_4chars  );
// echo("<pre>");
// echo bin2hex($this->text);
// echo("/<pre>");
        if($fonthdr_BIGENDIAN_4chars  == "774F4646")
        {
//echo($this->filename . "; format:". "WOFF"."<br/>");
            // BIG ENDIAN
            // Table Header
            $font_woff_flavor = hexdec($this->dec2ord($this->text[4]).$this->dec2ord($this->text[5]).$this->dec2ord($this->text[6]).$this->dec2ord($this->text[7]));
            $font_woff_length = hexdec($this->dec2ord($this->text[8]).$this->dec2ord($this->text[9]).$this->dec2ord($this->text[10]).$this->dec2ord($this->text[11]));
            $number_of_tables = hexdec($this->dec2ord($this->text[12]).$this->dec2ord($this->text[13]));
            // 2bytes reserved
            $totalSfntSize = hexdec($this->dec2ord($this->text[16]).$this->dec2ord($this->text[17]).$this->dec2ord($this->text[18]).$this->dec2ord($this->text[19]));
            $majorVersion = hexdec($this->dec2ord($this->text[20]).$this->dec2ord($this->text[21]));
            $minorVersion = hexdec($this->dec2ord($this->text[22]).$this->dec2ord($this->text[23]));

            $metaOffset = hexdec($this->dec2ord($this->text[24]).$this->dec2ord($this->text[25]).$this->dec2ord($this->text[26]).$this->dec2ord($this->text[27]));
            $metaLength = hexdec($this->dec2ord($this->text[28]).$this->dec2ord($this->text[29]).$this->dec2ord($this->text[30]).$this->dec2ord($this->text[31]));
            $metaOrigLength = hexdec($this->dec2ord($this->text[32]).$this->dec2ord($this->text[33]).$this->dec2ord($this->text[34]).$this->dec2ord($this->text[35]));
            $privOffset = hexdec($this->dec2ord($this->text[36]).$this->dec2ord($this->text[37]).$this->dec2ord($this->text[38]).$this->dec2ord($this->text[39]));
            $privLength = hexdec($this->dec2ord($this->text[40]).$this->dec2ord($this->text[41]).$this->dec2ord($this->text[42]).$this->dec2ord($this->text[43]));
//echo($this->filename . "; no. of tables:". $number_of_tables."<br/>");


            // Table Directory
            // 43 to 46
            // 47 to 50
            // 51 to 54
            // 55 to 58
            // 59 to 62


            //return; // this class cannot decode WOFF fonts

            $start = 63;
        }
        else
        {
            // TTF, OTF
//echo($this->filename . "; format:". "TTF or OTF"."<br/>");
            $number_of_tables = hexdec($this->dec2ord($this->text[4]).$this->dec2ord($this->text[5]));
            $start = 0;
//echo ($this->filename . "; no. of tables:". $number_of_tables);
        }

$offset_storage_dec = 0;
$number_name_records_dec = 0;
$this->ntOffset = 0;
// /error_log($this->filename . "; no. of tables:". $number_of_tables);

        for ($i=$start;$i<$number_of_tables;$i++)
        {
            if(strlen($this->text) < 12+$i*16)
            {
//error_log(__FUNCTION__. " TTF Class " . ': table length exceeded<br/>');
                break;
            }
            $tag = $this->text[12+$i*16].$this->text[12+$i*16+1].$this->text[12+$i*16+2].$this->text[12+$i*16+3];

            if ($tag == 'name')
            {
                $this->ntOffset = hexdec(
                    $this->dec2ord($this->text[12+$i*16+8]).$this->dec2ord($this->text[12+$i*16+8+1]).
                    $this->dec2ord($this->text[12+$i*16+8+2]).$this->dec2ord($this->text[12+$i*16+8+3]));

                $offset_storage_dec = hexdec($this->dec2ord($this->text[$this->ntOffset+4]).$this->dec2ord($this->text[$this->ntOffset+5]));
                $number_name_records_dec = hexdec($this->dec2ord($this->text[$this->ntOffset+2]).$this->dec2ord($this->text[$this->ntOffset+3]));
            }
        }
//echo($this->filename . ' number_name_records_dec ' .$number_name_records_dec);


        $storage_dec = $offset_storage_dec + $this->ntOffset;
        $storage_hex = strtoupper(dechex($storage_dec));
        for ($j=0;$j<$number_name_records_dec;$j++)
        {
//echo ($j);
            if(strlen($this->dec2ord($this->text[$this->ntOffset+6+$j*12+0])) < $this->ntOffset+6+$j*12+0)
            {
//error_log(__FUNCTION__. " TTF Class " . ': table length exceeded<br/>');
       //         break;
            }
            $platform_id_dec    = hexdec($this->dec2ord($this->text[$this->ntOffset+6+$j*12+0]).$this->dec2ord($this->text[$this->ntOffset+6+$j*12+1]));
            $name_id_dec        = hexdec($this->dec2ord($this->text[$this->ntOffset+6+$j*12+6]).$this->dec2ord($this->text[$this->ntOffset+6+$j*12+7]));
            $string_length_dec    = hexdec($this->dec2ord($this->text[$this->ntOffset+6+$j*12+8]).$this->dec2ord($this->text[$this->ntOffset+6+$j*12+9]));
            $string_offset_dec    = hexdec($this->dec2ord($this->text[$this->ntOffset+6+$j*12+10]).$this->dec2ord($this->text[$this->ntOffset+6+$j*12+11]));
            if (!empty($name_id_dec) and empty($font_tags[$name_id_dec]))
            {
                for($l=0;$l<$string_length_dec;$l++)
                {
                    if (ord($this->text[$storage_dec+$string_offset_dec+$l]) == '0') { continue; }
                    else
                    {
                        if(!isset($font_tags[$name_id_dec]))
                            $font_tags[$name_id_dec] = ($this->text[$storage_dec+$string_offset_dec+$l]);
                        else
                            $font_tags[$name_id_dec] .= ($this->text[$storage_dec+$string_offset_dec+$l]);
                     }
                }
            }
        }
        $ft = implode($font_tags);
//echo($this->filename . ' imploded font tags '. $ft );
        return $font_tags;
    } // public function getFontInfo

    /**
    * function getCopyright()
    * @public
    * @return 'Copyright notice' contained in the TTF 'name' table at index 0
    */
    public function getCopyright()
    {
        $this->info = $this->getFontInfo();
        return $this->info[0];
    } // public function getCopyright

    /**
    * function getFontFamily()
    * @public
    * @return 'Font Family name' contained in the TTF 'name' table at index 1
    */
    public function getFontFamily()
    {
        $this->info = $this->getFontInfo();
        return $this->info[1];
    } // public function getFontFamily

    /**
    * function getFontSubFamily()
    * @public
    * @return 'Font Subfamily name' contained in the TTF 'name' table at index 2
    */
    public function getFontSubFamily()
    {
        $this->info = $this->getFontInfo();
        return $this->info[2];
    } // public function getFontSubFamily

    /**
    * function getFontId()
    * @public
    * @return 'Unique font identifier' contained in the TTF 'name' table at index 3
    */
    public function getFontId()
    {
        $this->info = $this->getFontInfo();
        return $this->info[3];
    } // public function getFontId

    /**
    * function getFullFontName()
    * @public
    * @return 'Full font name' contained in the TTF 'name' table at index 4
    */
    public function getFullFontName()
    {
        $this->info = $this->getFontInfo();
        return $this->info[4];
    } // public function getFullFontName

    /**
    * function dec2ord()
    * Used to lessen redundant calls to multiple functions.
    * @protected
    * @return object
    */
    protected function dec2ord($dec)
    {
        return $this->dec2hex(ord($dec));
    } // protected function dec2ord

    /**
    * function dec2hex()
    * private function to perform Hexadecimal to decimal with proper padding.
    * @protected
    * @return object
    */
    protected function dec2hex($dec)
    {
        return str_repeat('0', 2-strlen(($hex=strtoupper(dechex($dec))))) . $hex;
    } // protected function dec2hex

    /**
    * function dec2hex()
    * private function to perform Hexadecimal to decimal with proper padding.
    * @protected
    * @return object
    */
    protected function exitClass($message)
    {
        echo $message;
        exit;
    } // protected function dec2hex

    /**
    * function dec2hex()
    * private helper function to test in the file in question is a ttf.
    * @protected
    * @return object
    */
    protected function is_ttf($file)
    {
        $ext = explode('.', $file);
        $ext = $ext[count($ext)-1];
        return preg_match("/ttf$/i",$ext) ? true : false;
    } // protected function is_ttf
} // class ttfInfo

function getFontInfo($resource)
{
    $ttfInfo = new ttfInfo;
    $ttfInfo->setFontFile($resource);
    return $ttfInfo->getFontInfo();
}
?>
