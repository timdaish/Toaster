<?php
/**
 * Copyright (c) 2008, David R. Nadeau, NadeauSoftware.com.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *	* Redistributions of source code must retain the above copyright
 *	  notice, this list of conditions and the following disclaimer.
 *
 *	* Redistributions in binary form must reproduce the above
 *	  copyright notice, this list of conditions and the following
 *	  disclaimer in the documentation and/or other materials provided
 *	  with the distribution.
 *
 *	* Neither the names of David R. Nadeau or NadeauSoftware.com, nor
 *	  the names of its contributors may be used to endorse or promote
 *	  products derived from this software without specific prior
 *	  written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY
 * WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY
 * OF SUCH DAMAGE.
 */

/*
 * This is a BSD License approved by the Open Source Initiative (OSI).
 * See:  http://www.opensource.org/licenses/bsd-license.php
 */



/**
 * Extract URLs from a web page.
 *
 * URLs are extracted from a long list of tags and attributes as defined
 * by the HTML 2.0, HTML 3.2, HTML 4.01, and draft HTML 5.0 specifications.
 * URLs are also extracted from tags and attributes that are common
 * extensions of HTML, from the draft Forms 2.0 specification, from XHTML,
 * and from WML 1.3 and 2.0.
 *
 * The function returns an associative array of associative arrays of
 * arrays of URLs.  The outermost array's keys are the tag (element) name,
 * such as "a" for <a> or "img" for <img>.  The values for these entries
 * are associative arrays where the keys are attribute names for those
 * tags, such as "href" for <a href="...">.  Finally, the values for
 * those arrays are URLs found in those tags and attributes throughout
 * the text.
 *
 * Parameters:
 * 	text		the UTF-8 text to scan
 *
 * Return values:
 * 	an associative array where keys are tags and values are an
 * 	associative array where keys are attributes and values are
 * 	an array of URLs.
 *
 * See:
 * 	http://nadeausoftware.com/articles/2008/01/php_tip_how_extract_urls_web_page
 */
function extract_html_urls( $text )
{
	$match_elements = array(
		// HTML
		array('element'=>'a',		'attribute'=>'href'),		// 2.0
		array('element'=>'a',		'attribute'=>'urn'),		// 2.0
		array('element'=>'base',	'attribute'=>'href'),		// 2.0
		array('element'=>'form',	'attribute'=>'action'),		// 2.0
		array('element'=>'img',		'attribute'=>'src'),		// 2.0
		array('element'=>'link',	'attribute'=>'href'),		// 2.0

		array('element'=>'applet',	'attribute'=>'code'),		// 3.2
		array('element'=>'applet',	'attribute'=>'codebase'),	// 3.2
		array('element'=>'area',	'attribute'=>'href'),		// 3.2
		array('element'=>'body',	'attribute'=>'background'),	// 3.2
		array('element'=>'img',		'attribute'=>'usemap'),		// 3.2
		array('element'=>'input',	'attribute'=>'src'),		// 3.2

		array('element'=>'applet',	'attribute'=>'archive'),	// 4.01
		array('element'=>'applet',	'attribute'=>'object'),		// 4.01
		array('element'=>'blockquote',	'attribute'=>'cite'),		// 4.01
		array('element'=>'del',		'attribute'=>'cite'),		// 4.01
		array('element'=>'frame',	'attribute'=>'longdesc'),	// 4.01
		array('element'=>'frame',	'attribute'=>'src'),		// 4.01
		array('element'=>'head',	'attribute'=>'profile'),	// 4.01
		array('element'=>'iframe',	'attribute'=>'longdesc'),	// 4.01
		array('element'=>'iframe',	'attribute'=>'src'),		// 4.01
		array('element'=>'img',		'attribute'=>'longdesc'),	// 4.01
		array('element'=>'input',	'attribute'=>'usemap'),		// 4.01
		array('element'=>'ins',		'attribute'=>'cite'),		// 4.01
		array('element'=>'object',	'attribute'=>'archive'),	// 4.01
		array('element'=>'object',	'attribute'=>'classid'),	// 4.01
		array('element'=>'object',	'attribute'=>'codebase'),	// 4.01
		array('element'=>'object',	'attribute'=>'data'),		// 4.01
		array('element'=>'object',	'attribute'=>'usemap'),		// 4.01
		array('element'=>'q',		'attribute'=>'cite'),		// 4.01
		array('element'=>'script',	'attribute'=>'src'),		// 4.01

		array('element'=>'audio',	'attribute'=>'src'),		// 5.0
		array('element'=>'command',	'attribute'=>'icon'),		// 5.0
		array('element'=>'embed',	'attribute'=>'src'),		// 5.0
		array('element'=>'event-source','attribute'=>'src'),		// 5.0
		array('element'=>'html',	'attribute'=>'manifest'),	// 5.0
		array('element'=>'source',	'attribute'=>'src'),		// 5.0
		array('element'=>'video',	'attribute'=>'src'),		// 5.0
		array('element'=>'video',	'attribute'=>'poster'),		// 5.0

		array('element'=>'bgsound',	'attribute'=>'src'),		// Extension
		array('element'=>'body',	'attribute'=>'credits'),	// Extension
		array('element'=>'body',	'attribute'=>'instructions'),	// Extension
		array('element'=>'body',	'attribute'=>'logo'),		// Extension
		array('element'=>'div',		'attribute'=>'href'),		// Extension
		array('element'=>'div',		'attribute'=>'src'),		// Extension
		array('element'=>'embed',	'attribute'=>'code'),		// Extension
		array('element'=>'embed',	'attribute'=>'pluginspage'),	// Extension
		array('element'=>'html',	'attribute'=>'background'),	// Extension
		array('element'=>'ilayer',	'attribute'=>'src'),		// Extension
		array('element'=>'img',		'attribute'=>'dynsrc'),		// Extension
		array('element'=>'img',		'attribute'=>'lowsrc'),		// Extension
		array('element'=>'input',	'attribute'=>'dynsrc'),		// Extension
		array('element'=>'input',	'attribute'=>'lowsrc'),		// Extension
		array('element'=>'table',	'attribute'=>'background'),	// Extension
		array('element'=>'td',		'attribute'=>'background'),	// Extension
		array('element'=>'th',		'attribute'=>'background'),	// Extension
		array('element'=>'layer',	'attribute'=>'src'),		// Extension
		array('element'=>'xml',		'attribute'=>'src'),		// Extension

		array('element'=>'button',	'attribute'=>'action'),		// Forms 2.0
		array('element'=>'datalist',	'attribute'=>'data'),		// Forms 2.0
		array('element'=>'form',	'attribute'=>'data'),		// Forms 2.0
		array('element'=>'input',	'attribute'=>'action'),		// Forms 2.0
		array('element'=>'select',	'attribute'=>'data'),		// Forms 2.0

		// XHTML
		array('element'=>'html',	'attribute'=>'xmlns'),

		// WML
		array('element'=>'access',	'attribute'=>'path'),		// 1.3
		array('element'=>'card',	'attribute'=>'onenterforward'),	// 1.3
		array('element'=>'card',	'attribute'=>'onenterbackward'),// 1.3
		array('element'=>'card',	'attribute'=>'ontimer'),	// 1.3
		array('element'=>'go',		'attribute'=>'href'),		// 1.3
		array('element'=>'option',	'attribute'=>'onpick'),		// 1.3
		array('element'=>'template',	'attribute'=>'onenterforward'),	// 1.3
		array('element'=>'template',	'attribute'=>'onenterbackward'),// 1.3
		array('element'=>'template',	'attribute'=>'ontimer'),	// 1.3
		array('element'=>'wml',		'attribute'=>'xmlns'),		// 2.0
	);

	$match_metas = array(
		'content-base',
		'content-location',
		'referer',
		'location',
		'refresh',
	);

	// Extract all elements
	if ( !preg_match_all( '/<([a-z][^>]*)>/iu', $text, $matches ) )
		return array( );
	$elements = $matches[1];
	$value_pattern = '=(("([^"]*)")|([^\s]*))';

	// Match elements and attributes
	foreach ( $match_elements as $match_element )
	{
		$name = $match_element['element'];
		$attr = $match_element['attribute'];
		$pattern = '/^' . $name . '\s.*' . $attr . $value_pattern . '/iu';
		if ( $name == 'object' )
			$split_pattern = '/\s*/u';	// Space-separated URL list
		else if ( $name == 'archive' )
			$split_pattern = '/,\s*/u';	// Comma-separated URL list
		else
			unset( $split_pattern );	// Single URL
		foreach ( $elements as $element )
		{
			if ( !preg_match( $pattern, $element, $match ) )
				continue;
			$m = empty($match[3]) ? $match[4] : $match[3];
			if ( !isset( $split_pattern ) )
				$urls[$name][$attr][] = $m;
			else
			{
				$msplit = preg_split( $split_pattern, $m );
				foreach ( $msplit as $ms )
					$urls[$name][$attr][] = $ms;
			}
		}
	}

	// Match meta http-equiv elements
	foreach ( $match_metas as $match_meta )
	{
		$attr_pattern    = '/http-equiv="?' . $match_meta . '"?/iu';
		$content_pattern = '/content'  . $value_pattern . '/iu';
		$refresh_pattern = '/\d*;\s*(url=)?(.*)$/iu';
		foreach ( $elements as $element )
		{
			if ( !preg_match( '/^meta/iu', $element ) ||
				!preg_match( $attr_pattern, $element ) ||
				!preg_match( $content_pattern, $element, $match ) )
				continue;
			$m = empty($match[3]) ? $match[4] : $match[3];
			if ( $match_meta != 'refresh' )
				$urls['meta']['http-equiv'][] = $m;
			else if ( preg_match( $refresh_pattern, $m, $match ) )
				$urls['meta']['http-equiv'][] = $match[2];
		}
	}

	// Match style attributes
	$urls['style'] = array( );
	$style_pattern = '/style' . $value_pattern . '/iu';
	foreach ( $elements as $element )
	{
		if ( !preg_match( $style_pattern, $element, $match ) )
			continue;
		$m = empty($match[3]) ? $match[4] : $match[3];
		$style_urls = extract_css_urls( $m );
		if ( !empty( $style_urls ) )
			$urls['style'] = array_merge_recursive(
				$urls['style'], $style_urls );
	}

	// Match style bodies
	if ( preg_match_all( '/<style[^>]*>(.*?)<\/style>/siu', $text, $style_bodies ) )
	{
		foreach ( $style_bodies[1] as $style_body )
		{
			$style_urls = extract_css_urls( $style_body );
			if ( !empty( $style_urls ) )
				$urls['style'] = array_merge_recursive(
					$urls['style'], $style_urls );
		}
	}
	if ( empty($urls['style']) )
		unset( $urls['style'] );

	return $urls;
}


/**
 * Extract URLs from UTF-8 CSS text.
 *
 * URLs within @import statements and url() property functions are extracted
 * and returned in an associative array of arrays.  Array keys indicate
 * the use context for the URL, including:
 *
 * 	"import"
 * 	"property"
 *
 * Each value in the associative array is an array of URLs.
 *
 * Parameters:
 * 	text		the UTF-8 text to scan
 *
 * Return values:
 * 	an associative array of arrays of URLs.
 *
 * See:
 * 	http://nadeausoftware.com/articles/2008/01/php_tip_how_extract_urls_css_file
 */
function extract_css_urls( $text )
{

    $urls = array( );
 
    $url_pattern     = '(([^\\\\\'", \(\)]*(\\\\.)?)+)';
    $urlfunc_pattern = 'url\(\s*[\'"]?' . $url_pattern . '[\'"]?\s*\)';
    $pattern         = '/(' .
         '(@import\s*[\'"]' . $url_pattern     . '[\'"])' .
        '|(@import\s*'      . $urlfunc_pattern . ')'      .
        '|('                . $urlfunc_pattern . ')'      .  ')/iu';
    if ( !preg_match_all( $pattern, $text, $matches ) )
        return $urls;
 
    // @import '...'
    // @import "..."
    foreach ( $matches[3] as $match )
        if ( !empty($match) )
            $urls['import'][] =
                preg_replace( '/\\\\(.)/u', '\\1', $match );
 
    // @import url(...)
    // @import url('...')
    // @import url("...")
    foreach ( $matches[7] as $match )
        if ( !empty($match) )	
            $urls['import'][] = 
                preg_replace( '/\\\\(.)/u', '\\1', $match );
 
    // url(...)
    // url('...')
    // url("...")
    foreach ( $matches[11] as $match )
        if ( !empty($match) )
            $urls['property'][] = 
                preg_replace( '/\\\\(.)/u', '\\1', $match );
 
    return $urls;
}

function extract_css_bg_urls($cssFileContent)
{
	 $urls = array( );
	// matches all url definitions inside the css file as follows:
	// url("*")  URL definitions with double quotes
	// url('*')  URL definitions with single quotes
	// url(*)    URL definitions without quotes
	preg_match_all('/url\(([\s])?([\"|\'])?(.*?)([\"|\'])?([\s])?\)/i', $cssFileContent, $matches, PREG_PATTERN_ORDER);
	 
	// in case of found matches, the multi-dimensional array $matches contains following
	// important entries:
	// $matches[0]  (array)  List containing each string matching the full pattern, e. g. url("images/bg.gif")
	// $matches[3]  (array)  List containing matched url definitions, e. g. images/bg.gif
	if ($matches) {
	   foreach($matches[3] as $match) {
		  // do whatever you want with those matches, adapt paths by changing them to absolute or CDN paths
		  // - "images/bg.gif" -> "/path_to_css_module/images/bg.gif"
		  // - "images/bg.gif" -> "http://cdn.domain.tld/path_to_css_module/images/bg.gif"
	 
	 	if($match != '')
	 		$urls['property'][] = $match;
		  //echo $match . "<br/>";
	   }
	}
	return $urls;	
}

function findStyleforImage($imgurl,$text)
{
	//echo('findStyleforImage: looking up style for: '.$imgurl);
	$exp = explode('}',$text);
	$lookup = false;
	//echo 'CSS styles<pre>';
	//print_r($exp);
	//echo '</pre>';
	
	foreach ($exp as $key => $style) {
		//echo( "css Key: $key; Value: $style<br/>");
		// look for $imgurl in $text and get position, then work backwords to find the id or class it belongs to
		$ipos = strpos($style,$imgurl);	
		if($ipos > 0)
		{
			// img applies to this style
			$expstyle = explode('{',$style);
			$stylename = $expstyle[0];
			$stype = '';
			
			$pattern = '!/\*[^*]*\*+([^/][^*]*\*+)*/!'; 
			$str = preg_replace($pattern, '', $stylename);
			//echo( "STYLE: ".$str."<br/>");
			$splitstyle = explode(' ',$str);
			foreach ($splitstyle as $sstyle) {
				//echo( "style Key: $skey; Value: $sstyle.: 1st char: ".$char1."<br/>");
				$sstyle = trim($sstyle);
				$char1 = substr($sstyle,0,1);	
				if($char1 == '#')
				{
					//echo("ID: ".substr($sstyle,0)."<br/>");
					$stype = 'id';
				}
				if($char1 == '.')
				{
					//echo("CLASS: ".substr($sstyle,0)."<br/>");
					$stype = 'class';
				}
				addStyle($stype,$sstyle,$imgurl);
				//echo( "style: ".$sstyle."; type: ". $stype."<br/>");
				$lookup = lookupStyleForImage($stype,$sstyle);
				if($lookup == true)
					break;
			}
		}
	
	}

	//echo("STYLE end: ".substr($sstyle,0)." = ".$lookup ."<br/>");
	return $lookup;
}

function addStyle($stype,$style,$imgurl)
{
	global $rootStyles;
	
	$boolFound = false;
	foreach ($rootStyles as $savedstyle) {
		//echo 'CSS styles KEY<pre>';
		//print_r($key);
		//echo '</pre>';

		if(substr($style,1) == $savedstyle[1])
		{
			$boolFound = true;
			break;
		}
	}
	
	if($boolFound == false)
	{
		//echo($stype.": ".substr($style,1) . "= " . $imgurl."<br/>");
		$arr = array($stype,substr($style,1),$imgurl);
		$rootStyles[] = $arr;
	}
	return true;
}

function lookupStyleForImage($stype,$imgstyle)
{
	global $rootStyleID, $rootStyleClass;
	$boolFound = false;
	if(substr($imgstyle,0,1) == '.')
		$imgstyle = substr($imgstyle,1);
	if(substr($imgstyle,0,1) == '#')
		$imgstyle = substr($imgstyle,1);
	
	if($stype == 'id')
	{
		//echo("lookupstyle: wanting id ".$imgstyle."<br/>");
		foreach ($rootStyleID as $savedstyle) {
			//echo("id savedstyle: ".$savedstyle." - ".$imgstyle."<br/>");		
			if($imgstyle == $savedstyle)
			{
				//echo("id savedstyle: ".$savedstyle." = ".$imgstyle."<br/>");
				$boolFound = true;
				break;
			}
		}
	}
	else
	{
		//echo("lookupstyle: wanting class ".$imgstyle."<br/>");
		foreach ($rootStyleClass as $savedstyle) {
			//echo("class savedstyle: ".$savedstyle."<br/>");				
			if(($imgstyle) == $savedstyle)
			{
				//echo("class savedstyle: ".$savedstyle." = ".$imgstyle."<br/>");
				$boolFound = true;
				break;
			}
		}
	}
	//echo("boolfound: ".$imgstyle . "= " . $boolFound."<br/>");
	return $boolFound;
}

?>
