<?php

$dom = new DOMDocument();
$dom->loadHTMLFile('http://www.bbc.co.uk');

$xpath = new DOMXPath($dom);
$body = $xpath->query('//body')->item(0);

recursePrintStyles($body);

function recursePrintStyles($node)
{
    if ($node->nodeType !== XML_ELEMENT_NODE)
    {
        return;
    }

    echo $node->tagName;
    echo "\t";
    echo $node->getAttribute('style');
    echo "\n";

    foreach ($node->childNodes as $childNode)
    {
        recursePrintStyles($childNode);
    }
}

?>