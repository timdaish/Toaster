<?php
include "dbconn_toaster3p.php";
header('Content-Type: text/html; charset=utf-8');
global $conn;
$string = file_get_contents("toaster_tools/3P_desc.json");
$json_data = json_decode($string, true);

// init counts
$entrycount = 0;

//print_r($json_data);
echo("<html><body>");
//Traverse array and get the data 
foreach ($json_data as $key1 => $value1) {
    $entrycount++;

    if($json_data[$key1]["priority"] > 0)
    {
        //print_r($json_data[$key1]);

        $domain = $json_data[$key1]["domain"];
        $priority = $json_data[$key1]["priority"];
        $regex = $json_data[$key1]["regex"];
        $companyarray = $json_data[$key1]["company"];
        $productarray = $json_data[$key1]["product"];
        $categoryarray = $json_data[$key1]["product"]["category"];
        $party = $json_data[$key1]["party"];
        $user = $json_data[$key1]["user"];
        $versionarray = $json_data[$key1]["version"];
        $companyname = $companyarray['name'];
        $productname = $productarray['name'];
        $productcompanyname = $productarray['company'];
        $productdescription = $productarray['description'];
        $category = $categoryarray['name'];
        $group = $categoryarray['group']['name'];
        $groupcolour = $categoryarray['group']['color'];
        $author = $versionarray['author'];
        $date = $versionarray['date'];

        // echo "<br/>";
        // echo "domain: " . $domain . "<br/>";
        // echo "priority: " . $priority . "<br/>";
        // echo "regex:" . $regex . "<br/>";
        // echo "party: " . $party . "<br/>";
        // echo "company: " . $companyname . "<br/>";
        // echo "product: " . $productname . "<br/>";
        // echo "product company: " . $productcompanyname . "<br/>";
        // echo "product description: " . $productdescription . "<br/>";
        // echo "product category: " . $category . "<br/>";
        // echo "product group: " . $group . "<br/>";
        // echo "product group colour: " . $groupcolour . "<br/>";
        // echo "author: " . $author . "<br/>";
        // echo "date: " . $date . "<br/>";

        //echo "<br/><br/>";

        // add group
        $idgroup = mysql_insertGroup($group,$groupcolour);
//echo "group id: " . $group . " = " . $idgroup;
        
        // add category
        $idcategory = mysql_insertCategory($category,$idgroup);

//        echo "adding company: " . $companyname . "<br/>";
        $idcompany = mysql_insertCompany($companyname);

//        echo "adding product: " . $productname . "<br/>";
        $idproduct = mysql_insertProduct($productname,$idcompany,$idcategory,$productdescription);

        //echo "adding domain: " . $domain . "<br/>";
        $iddomain = mysql_insertDomain($domain,$priority,$regex,$idcompany,$idproduct,$idcategory);

		}
}
// close connection to new db
$conn->close();

echo "Number of entries: " . $entrycount."<br/>";
echo("</body></html>");

function mysql_insertDomain($domain,$priority,$regex,$idcompany,$idproduct,$idcategory)
{
    global $conn;
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $sql = 'INSERT domain (domain, priority, regex,product_idproduct,product_company_idcompany,product_category_idcategory)
    VALUES ("'.$domain.'",'. $priority . ',"'.$regex.'",'.$idproduct.','.$idcompany.','.$idcategory.')';

    if ($conn->query($sql) === TRUE) {
     //   echo "New domain record created or updated successfully for " . $domain . "<br/>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error."<br/>";
    }

    $used_id = $conn->insert_id;
    return $used_id;
}

function mysql_insertCompany($companyname)
{
    global $conn;
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $sql = 'INSERT IGNORE INTO company (companyname)
    VALUES ("'.$companyname.'")';

    if ($conn->query($sql) === TRUE) {
        //echo "New company record created for " . $companyname . "<br/>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error."<br/>";
    }

    $used_id = $conn->insert_id;

    if($used_id == 0)
    {
        // lookup
        $sql = 'SELECT idcompany FROM company
        WHERE companyname = "' . $companyname . '"';
//echo $sql . "<br/>";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $used_id = $row["idcompany"];
                //echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
            }
        //    echo ( "company " . $companyname ." found: " . $used_id. "</br>"); 
        } else {
        //    echo ("0 results found for company " . groupname . "</br>");
        }
    }
    return $used_id;
}

function mysql_insertProduct($productname,$companyid,$categoryid,$desc)
{
    global $conn;
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    // sanitise description:
    // remove double quotes
    $desc = str_replace('\"','',$desc);
    $desc = str_replace('"','',$desc);
    // decode any html entities already present and then replace all applicable chars with htmlentities
    $desc = html_entity_decode ($desc);
    $desc = htmlentities($desc);

    $sql = 'INSERT IGNORE INTO product (productname,company_idcompany,category_idcategory,product_description)
    VALUES ("'.$productname.'",'.$companyid.','. $categoryid . ',"'.$desc.'")';

    if ($conn->query($sql) === TRUE) {
     //   echo "New product record created for " . $productname . "<br/>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error."<br/>";
    }

    $used_id = $conn->insert_id;


    if($used_id == 0)
    {
        // lookup
        $sql = 'SELECT idproduct FROM product
        WHERE productname = "' . $productname . '"';
//echo $sql . "<br/>";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $used_id = $row["idproduct"];
                //echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
            }
        //    echo ( "producy " . $productname ." found: " . $used_id. "</br>"); 
        } else {
        //    echo ("0 results found for product " . $productname . "</br>");
        }
    }

    return $used_id;
}

function mysql_insertGroup($groupname, $groupcolor)
{
    global $conn;
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $sql = 'INSERT IGNORE INTO `group` (groupname,groupcolor)
    VALUES ("'.$groupname.'","'.$groupcolor.'")';

    if ($conn->query($sql) === TRUE) {
      //  echo "New group record created for " . $groupname . "<br/>";
    } else {
      //  echo "Error: " . $sql . "<br>" . $conn->error."<br/>";
    }

    $used_id = $conn->insert_id;

    if($used_id == 0)
    {
        // lookup
        $sql = 'SELECT idgroup FROM `group`
        WHERE groupname = "' . $groupname . '"';
//echo $sql . "<br/>";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $used_id = $row["idgroup"];
                //echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
            }
        //    echo ( "group " . $groupname ." found: " . $used_id. "</br>"); 
        } else {
        //    echo ("0 results found for group " . $groupname . "</br>");
        }
    }

    return $used_id;
}

function mysql_insertCategory($categoryname,$groupid)
{
    global $conn;
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = 'INSERT IGNORE INTO category (categoryname,group_idgroup)
    VALUES ("'.$categoryname.'",'. $groupid. ')';

    if ($conn->query($sql) === TRUE) {
    //    echo ("New category record created for " . $categoryname . "<br/>");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error."<br/>";
    }

    $used_id = $conn->insert_id;

    if($used_id == 0)
    {
        // lookup
        $sql = 'SELECT idcategory FROM category
        WHERE categoryname = "' . $categoryname . '"';
//echo $sql . "<br/>";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                $used_id = $row["idcategory"];
              //  echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
            }
           // echo ( "category " . $categoryname ." found: " . $used_id. "</br>"); 
        } else {
        //    echo ("0 results found for category " . categoryname . "</br>");
        }
    }


    return $used_id;
}


?>