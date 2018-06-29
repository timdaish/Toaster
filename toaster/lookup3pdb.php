<?php
include "dbconn_toaster3p.php";
//header('Content-Type: text/html; charset=utf-8');
//echo("<html><body>");
header('Content-type: application/json');

$subject =  $_GET['host'];
$matchedpriority = 0;

// check for Adobe Analytics override
if((strpos($subject,"metrics.") !== false or strpos($subject,"metric.") !== false))
{
    // check for exceptions
    $found = false;
    $arrayMetricsToKeep = array ("coremetrics","responsetap");
    foreach ($arrayMetricsToKeep as &$value) {
        if(strpos($subject,$value) !== false)
            // keep original
            $found = true;
    }
    if($found == false)
        //echo ("Adobe Analytics override</br>");
        $subject = "//sc.omtrdc.net/";
}



        // lookup domain
        $sql = 'SELECT * FROM domain';
//echo $sql . "<br/>";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
               // echo "domain id: " . $row["iddomain"]. " - regex: " . $row["regex"] ."<br>";

                $pattern = "$" . $row["regex"] . "$";

                if(preg_match($pattern,$subject)){
                    //echo "domain matched to priority " . $row["priority"]  . ": " . $row["domain"    ]. "<br/>";
                    // set match if a higher priority
                    if($row["priority"] > $matchedpriority)
                    {
                        $matcheddomainid = $row["iddomain"];
                        $matchedcompanyid = $row["product_company_idcompany"];
                        $matchedproductid = $row["product_idproduct"];
                        $matchedcategoryid = $row["product_category_idcategory"];
                        $matchedpriority = $row["priority"];
                        $matchedregex = $row["regex"];
                        $matcheddomain = $row["domain"];
                    }
                }else{
                   // echo "fail to match";   
                }
            }
        //    echo ( "domain " . $domain ." found: " . $used_id. "</br>"); 
        } else {
            echo ("0 results found for domainurl " . $domainurl . "</br>");
        }

        if ($matchedpriority > 0)
        {
            $companyname = lookupCompany($matchedcompanyid);
            list($productname,$desc) = lookupProduct($matchedproductid);
            list($categoryname,$groupid) = lookupCategory($matchedcategoryid);
            list($groupname,$groupcolor) = lookupGroup($groupid);
            //$groupname = 'test';

            //echo ($matchedpriority . " result:" . $companyname . " " . $productname . " " . $groupname . " " . $categoryname);
            // create return JSON
            $companyarr = array ('name' => $companyname);
            $grouparr = array ('name' => $groupname, 'color' => $groupcolor);
            $catarr = array ('name' => $categoryname, 'group' => $grouparr);
            $prodarr = array ('name' => $productname, 'description' => $desc,'category' => $catarr);
            $arr = array('domain' => $matcheddomain, 'product' => $prodarr,'company' => $companyarr);

            echo json_encode($arr);

            // $domain3P = $objjson->domain;
            // $objproduct = $objjson->product;
       	    // $objcompany= $objproduct->company;
            // $domainprovider = $objjson->company->name;
    		// $domaindesc = html_entity_decode($objproduct->description);
			// $domaindesc = str_replace('"','',$domaindesc);
			// $domaindesc = str_replace("'","",$domaindesc);
            // $objcat = $objproduct->category;
            // $objgroup = $objcat->group;
            // @$domainproduct  = html_entity_decode($objproduct->name);
    		// $domaingroup = html_entity_decode($objgroup->name);
       		// $domaincat = html_entity_decode($objcat->name);

        }
        else
        {
            // return blank JSON
            $arr = array();

            echo json_encode($arr);
        }

// close connection to new db
$conn->close();



//echo("</body></html>");

function lookupCompany($idcompany)
{
    global $conn;
    // lookup
    $sql = 'SELECT companyname FROM company
    WHERE idcompany = '. $idcompany; 
//echo $sql . "<br/>";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $companyname = $row["companyname"];
            //echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
        }
    //    echo ( "group " . $groupname ." found: " . $used_id. "</br>"); 
    } else {
    //    echo ("0 results found for group " . $groupname . "</br>");
    }
    return $companyname;
}

function lookupProduct($idproduct)
{
    global $conn;
    // lookup
    $sql = 'SELECT productname,product_description FROM product
    WHERE idproduct = '. $idproduct; 
//echo $sql . "<br/>";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $productname = $row["productname"];
            $productdescription = $row["product_description"];
            //echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
        }
    //    echo ( "group " . $groupname ." found: " . $used_id. "</br>"); 
    } else {
    //    echo ("0 results found for group " . $groupname . "</br>");
    }
    return array ($productname,$productdescription);
}

function lookupCategory($idcategory)
{
    global $conn;
    // lookup
    $sql = 'SELECT categoryname, group_idgroup FROM category
    WHERE idcategory = '. $idcategory; 
//echo $sql . "<br/>";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $categoryname = $row["categoryname"];
            $groupid = $row["group_idgroup"];
            //echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
        }
    //    echo ( "group " . $groupname ." found: " . $used_id. "</br>"); 
    } else {
    //    echo ("0 results found for group " . $groupname . "</br>");
    }
    return array ($categoryname,$groupid);
}

function lookupGroup($idgroup)
{
    global $conn;
    // lookup
    $sql = 'SELECT groupname, groupcolor FROM `group`
    WHERE idgroup = '. $idgroup; 
//echo $sql . "<br/>";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $groupname = $row["groupname"];
            $groupcolor = $row["groupcolor"];
            //echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
        }
    //    echo ( "group " . $groupname ." found: " . $used_id. "</br>"); 
    } else {
    //    echo ("0 results found for group " . $groupname . "</br>");
    }
    return array ($groupname,$groupcolor);
}
?>