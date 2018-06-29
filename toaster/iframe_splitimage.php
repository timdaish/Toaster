<?php
//echo $_GET["path"];
//echo $_GET["originalimg"];
//echo $_GET["fn"];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" title="default demosheet" media="screen" href="css/splitimage2.css" type="text/css" />
    <title>Webpage Toaster Image Optimsation Comparison</title>
    <script async src="js/bpgdec-0.9.4.js"></script>
    <script async src="js/pica.min.js"></script>
    <script src="js/jquery.min.js"></script>
</head>

<body style="max-width: 100%;">





    <div class="demo" style="margin-left: auto; margin-right: auto; overflow:hidden;">
        <table>
            <tr>
                <td class="title" colspan=3>Image Name:
                    <select id="fileSel" style="font-size: 1.1em; margin-bottom: 1em;" multiple="multiple">
                        <option value="default" type="hidden" selected>default</option>
                    </select>
                    &nbsp;&nbsp;&nbsp;Magnification:
                    <select id="scaleSel" style="width:5em ;font-size: 1em;text-align:center;">
                        <option ratio="1:3" value="0.5773502692">1/3</option>
                        <option ratio="1:2" value="0.7071067812">1/2</option>
                        <option ratio="1:1" value="1" selected>---</option>
                        <option ratio="2:1" value="1.414213562">2x</option>
                        <option ratio="3:1" value="1.732050808">3x</option>
                        <option ratio="4:1" value="2.000000000">4x</option>
                        <option ratio="5:1" value="2.236067977">5x</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="title" style="width:16em;padding-left:4em;text-align:right;">
                    <select id="leftSel" style="font-size: 1.1em;">
                        <option value="jpg"  folder="Original" selected>Original</option>
                        <option value="jpg"  folder="jpg">JPEG</option>
                        <option value="png"  folder="png">PNG</option>
                        <option value="webp" folder="webp">WebP</option>
                        <option value="bpg"  folder="bpg">BPG</option>

                    </select>
                    <select id="leftQual" style="font-size: 1.1em;">
                        <option value="jt">jpeg_tinyjpg</option>
                        <option value="j85">jpeg_quality_85</option>
                        <option value="j85p">jpeg_quality_85_progressive</option>
                        <option value="j75">jpeg_quality_75</option>
                        <option value="j75p">jpeg_quality_75_progressive</option>
                        <option value="j65">jpeg_quality_65</option>
                        <option value="j5p">jpeg_quality_65_progressive</option>
                        <option value="j55">jpeg_quality_55</option>
                        <option value="j55p">jpeg_quality_55_progressive</option>
                        <option value="jt">jpeg_jpegtran</option>
                        <option value="jtp">jpeg_jpegtran_progressive</option>
                        <option value="p">png</option>
                        <option value="pq">pngquant</option>
                        <option value="pc">pngcrush</option>
                        <option value="pcb">pngcrush_brute</option>
                        <option value="po">png_optipng</option>
                        <option value="pnq">png_pngnq-s9</option>
                        <option value="pou">pngout</option>
                        <option value="df" selected></option>
                    </select>
                </td>
                <td class="center-head" id="center-head">
                    compared to
                </td>
                <td class="title" style="width:16em;padding-right:4em;text-align:left;">
                    <select id="rightSel" style="font-size: 1.1em;">
                        <option value="jpg"  folder="jpg" selected>JPEG</option>
                        <option value="png"  folder="png">PNG</option>
                        <option value="webp" folder="webp">WebP</option>
                        <option value="bpg"  folder="bpg">BPG</option>
                        <option value="jpg"  folder="Original">Original</option>
                    </select>
                    <select id="rightQual" style="font-size: 1.1em;">
                        <option value="jt">jpeg_tinyjpg</option>
                        <option value="j85">jpeg_quality_85</option>
                        <option value="j85p">jpeg_quality_85_progressive</option>
                        <option value="j75" selected>jpeg_quality_75</option>
                        <option value="j75p">jpeg_quality_75_progressive</option>
                        <option value="j65">jpeg_quality_65</option>
                        <option value="j5p">jpeg_quality_65_progressive</option>
                        <option value="j55">jpeg_quality_55</option>
                        <option value="j55p">jpeg_quality_55_progressive</option>
                        <option value="jt">jpeg_jpegtran</option>
                        <option value="jtp">jpeg_jpegtran_progressive</option>
                        <option value="p">png</option>
                        <option value="pq">pngquant</option>
                        <option value="pc">pngcrush</option>
                        <option value="pcb">pngcrush_brute</option>
                        <option value="po">png_optipng</option>
                        <option value="pnq">png_pngnq-s9</option>
                        <option value="pou">pngout</option>
                        <option value="df"></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan=3 style="padding-top: 1em; padding-bottom: 1em;">
                    <div id="rightContainer" style="margin-left: auto; margin-right: auto; position: relative; width:800px; height:800px;">
                        <div id="leftContainer" style="border-right: 1px dotted white; width:800px; height:800px;"></div>
                        <div id="leftText" style="position: absolute; color: white; padding:.2em .5em .2em .5em;"></div>
                        <div id="rightText" style="position: absolute; color: white; padding:.2em .5em .2em .5em;"></div>
                    </div>
                </td>
            </tr>

        </table>
    </div>
    <div id="descCli" class="caption" style="border-bottom: 1px solid; font-family: 'Courier New', Courier, monospace;">
        Comparisons between different image formats, optimisers and quality levels.
        <p>JPEG (non-interlaced and progressive): IJG cjpeg v9 (quality levels 55-85%); jpegtran v9; tinyjpg</p>
        <p>PNG: PNGQuant v2 (quality 65-80%); PNGCrush v1.7.85 (normal and Brute); OptiPNG v0.7.5; PNGnq-s9 v2.0.1 </p>
        <p>GIF: Animations: Gifsicle v1.87</p>
        <p>WEBP: cwebp v0.4.3</p>
        <p>BPG: bpgenc v0.9.5</p>
    </div>
    <div id="descEnc" class="caption">
        <p>BPG and WebP decoded in javascript when needed. Rescaling is through Lanczos2.</p>
        <p>This page is based on <a href="http://xooyoozoo.github.io/yolo-octo-bugfixes" target="_blank">Fabrice Bellard's</a> BPG and <a href="http://people.xiph.org/~xiphmont/demo/daala/update1-tool2b.shtml" target="_blank">Xiph.org's</a> Daala comparison pages.</p>
    </div>



<script type="text/javascript">
$( document ).ready(function() {
  //console.log( "iframe splitimage ready!" );
  var toasturlfolder = '<?php echo $_REQUEST ["path"];?>';
  var originalimage  = '<?php echo $_REQUEST ["originalimg"];?>';
  var imgfn          = '<?php echo $_REQUEST ["fn"];?>';
  console.log('toasterurlfolder =' + toasturlfolder );
  console.log('original image = ' + originalimage);
  console.log('img filename = ' + imgfn);
  $("#fileSel").delay(500)
  $('#fileSel option[value="default"]').text(imgfn);  // changes text
  $('#fileSel option[value="default"]').val(imgfn);

  getWindowsOptions(toasturlfolder,originalimage,imgfn);
});



$.wait = function(ms) {
    var defer = $.Deferred();
    setTimeout(function() { defer.resolve(); }, ms);
    return defer;
};

</script>
<script defer src="js/splitimage2.js"></script>
</body>

</html>
