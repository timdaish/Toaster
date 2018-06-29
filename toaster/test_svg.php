<?php
include 'simple_html_dom.php';
$val = '';
$filepathnameofsaveobject = "";
$docSVG = new DOMDocument();
$res = $docSVG->load(realpath($filepathnameofsaveobject));
if($res == true)
{
    setAllId($docSVG);
    switch($plainsvgelement)
    {
        case 'font':
            $objtype = "Font";
            $boolTextType = true;
    //echo($lfn . ": force setting object type = " . $objtype . "<br/>");
            break;

        default:
            $objtype = "Image";
            break;       
    }
}

echo ("SVG is: " . $objtype . " '" . $val . "'<br/><br/>");

$plainsvgelement = '';
$val = '';
$filepathnameofsaveobject = "c:\\temp\\visit-data_sv5.json";
$docSVG = new DOMDocument();
@$res = $docSVG->load(realpath($filepathnameofsaveobject));
if($res == true)
    {
    setAllId($docSVG);
    switch($plainsvgelement)
    {
        case 'font':
            $objtype = "Font";
            $boolTextType = true;
    //echo($lfn . ": force setting object type = " . $objtype . "<br/>");
            break;

        default:
            $objtype = "Image";
            break;       
    }

    echo ("SVG is: " . $objtype . " '" . $val . "'" );
}
else
echo ("not an SVG");
exit;


function setAllId($DOMNode){
    global $plainsvgid, $plainsvgelement,$tagname,$val;



    if($DOMNode->hasChildNodes()){
      foreach ($DOMNode->childNodes as $DOMElement) {
print_r($DOMElement);
        if($DOMElement->hasAttributes())
        {

          $id=$DOMElement->getAttribute("id");
          if($id)
          {
            $DOMElement->setIdAttribute("id",$id);
//echo $DOMElement->tagName . " " . $id . $DOMElement->setIdAttribute("id",$id) . "<br/>";
              $plainsvgid = $id;
            $tagname = $DOMElement->tagName;
            if($id != '' and $val == '')
                $val = $id;
              $plainsvgelement = $DOMElement->tagName;
          }
        }
        setAllId($DOMElement); // recursive
      }
    
    }

//echo $tagname . " " . $val . "<br/>";
  }
  
?>