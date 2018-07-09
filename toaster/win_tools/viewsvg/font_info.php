<?php
// get parameter of font file
if($_GET["fontfile"] === "")
    echo "fontfile is an empty string</br>";
else
{
 //   echo "the font file to get is '" . $_GET["fontfile"] . "</br>";
}
// convert fontfile to JS var to be loaded below

?>


<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="robots" content="noindex, nofollow">
  <meta name="googlebot" content="noindex, nofollow">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" type="text/css" href="viewsvg.css">
  <title>SVG Font Viewer by iegik</title>
</head>
<body>
<div id="svgout"></div>
<p id="DisplayText"></p>
<script>
var jsfontfile = '<?php echo $_GET["fontfile"]; ?>';
</script>
<script type="text/javascript" src="svgfontinfo.js"></script>
</body>

</html>

