<?php


function get3PtagsAll()
{
global $url;
$parse = parse_url($url);
$host = $parse['host'];

    // retrieves all 3P domain tags (promoted to _root) from NCC Group tag database on Heroku

    global $array3pDescriptions;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_URL, 'https://tagdb.nccgroup-webperf.com/2/export');

    $result = curl_exec($ch);

    curl_close($ch);



    // check if result is empty

    if(count(json_decode($result,1))==0)

    {

        echo ("The DB could not be found");

        exit;

    }



    $i = 0;



    $json = json_decode($result,true);



    foreach ($json as $key => $value) {

        $i = $i + 1;



        // each domain data is an array

        foreach ($value as $key => $val)

        {

            //echo $key . '=>' . $val . '<br />';





            switch ($key)

            {

                case "domain":

    //                echo $key . '=>' . $val . '<br />';

                    $domain = $val;

                    // new domain so save details of the last one



                    break;







                //case "priority":

                case "regex":
                    $regex = $val;
                    break;

                case "party":

    //                echo $key . '=>' . $val . '<br />';

                    $party = $val;

                    // last detail of this domain so save details

                    //override for NCC Group RUM
//                    if(strpos($domain,"nccgroup-webperf.com") !=false)
//                    {
//                    $productdesc = "Our Real User Monitoring service tells the story of your website�s performance from the perspective of the people who use it";
//                    $productname = "Real User Montoring from NCC Group Web Performance";
//                    $groupname = "Analytics";
//                    $categoryname = "Performance";
//                    }


                    $arr =  array($domain,$companyname,$categoryname,$productdesc,$productname,$groupname,$regex);

                    $array3pDescriptions[] = $arr;


                    // reset vars
                    $productdesc = "";
                    $productname = "";
                    $groupname = "";
                    $categoryname = "";
                    $regex = "";


                    break;

                //case "company":

                case "product":

                    foreach ($val as $kkey => $kval)

                    {

                        switch ($kkey)

                        {

                            case "name":

    //                            echo $key ." " . $kkey . '=>' . $kval . '<br />';

                                $productname = $kval;

                                break;

                            case "company":

    //                            echo $key ." " . $kkey . '=>' . $kval . '<br />';

                                $companyname = $kval;

                                break;

                            case "description":

    //                            echo $key ." " . $kkey . '=>' . $kval . '<br />';

                                $productdesc = $kval;

                                break;

                            case "category":

                                foreach ($kval as $ckey => $cval)

                                {

                                    switch ($ckey)

                                    {

                                         case "name":

    //                                        echo $kkey ." " . $ckey . '=>' . $cval . '<br />';

                                            $categoryname = $cval;

                                            break;

                                          case "group":

                                            foreach ($cval as $gkey => $gval)

                                            {

    //                                           echo $ckey ." " . $gkey . '=>' . $gval . '<br />';

                                               $groupname = $gval;

                                               break;

                                            }



                                     }





                                }

                                break;

                        }

                    }

                    break;





            } // end switch





        } // end for each domain

    //    if($i > 5)

    //        break;

    } // end for each domain set

//    echo "number of domain records: " . sizeof($array3pDescriptions) . "<br/>";
//    echo "<pre>";
//    print_r($array3pDescriptions);
//    echo "</pre>";



// get supplemental tags for the domain being tested
// get3PtagsDomain();

}


function get3PtagsDomain()
{
global $url;
$parse = parse_url($url);
$host = $parse['host'];

    // retrieves all 3P domain tags (promoted to _root) from NCC Group tag database on Heroku

    global $array3pDescriptions;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_URL, 'http://ncctagdb.herokuapp.com/2/' . $host .  '/tag');
echo("derived host: ".$host);

    $result = curl_exec($ch);

    curl_close($ch);



    // check if result is empty

    if(count(json_decode($result,1))==0)

    {

        echo ("The DB could not be found");

        exit;

    }



    $i = 0;



    $json = json_decode($result,true);



    foreach ($json as $key => $value) {

        $i = $i + 1;



        // each domain data is an array

        foreach ($value as $key => $val)

        {

            echo $key . '=>' . $val . '<br />';



            switch ($key)

            {

                case "domain":

    //                echo $key . '=>' . $val . '<br />';

                    $domain = $val;

                    // new domain so save details of the last one



                    break;







                //case "priority":

                //case "regex":

                case "party":

    //                echo $key . '=>' . $val . '<br />';

                    $party = $val;

                    // last detail of this domain so save details

                    //override for NCC Group RUM
                    if(strpos($domain,"nccgroup-webperf.com") !=false)
                    {
                    $productdesc = "Our Real User Monitoring service tells the story of your website�s performance from the perspective of the people who use it";
                    $productname = "Real User Montoring from NCC Group Web Performance";
                    $groupname = "Analytics";
                    $categoryname = "Performance";
                    }


                    $arr =  array($domain,$companyname,$categoryname,$productdesc,$productname,$groupname);

                    $array3pDescriptions[] = $arr;


                    // reset vars
                    $productdesc = "";
                    $productname = "";
                    $groupname = "";
                    $categoryname = "";


                    break;

                //case "company":

                case "product":

                    foreach ($val as $kkey => $kval)

                    {

                        switch ($kkey)

                        {

                            case "name":

    //                            echo $key ." " . $kkey . '=>' . $kval . '<br />';

                                $productname = $kval;

                                break;

                            case "company":

    //                            echo $key ." " . $kkey . '=>' . $kval . '<br />';

                                $companyname = $kval;

                                break;

                            case "description":

    //                            echo $key ." " . $kkey . '=>' . $kval . '<br />';

                                $productdesc = $kval;

                                break;

                            case "category":

                                foreach ($kval as $ckey => $cval)

                                {

                                    switch ($ckey)

                                    {

                                         case "name":

    //                                        echo $kkey ." " . $ckey . '=>' . $cval . '<br />';

                                            $categoryname = $cval;

                                            break;

                                          case "group":

                                            foreach ($cval as $gkey => $gval)

                                            {

    //                                           echo $ckey ." " . $gkey . '=>' . $gval . '<br />';

                                               $groupname = $gval;

                                               break;

                                            }



                                     }





                                }

                                break;

                        }

                    }

                    break;





            } // end switch





        } // end for each domain

    //    if($i > 5)

    //        break;

    } // end for each domain set

//    echo "number of domain records: " . sizeof($array3pDescriptions) . "<br/>";
//    echo "<pre>";
//    print_r($array3pDescriptions);
//    echo "</pre>";

}


?>
