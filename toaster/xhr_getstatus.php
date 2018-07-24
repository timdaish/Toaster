<?php
session_start();
if(isset($_SESSION['status']))
{
  $sdata = $_SESSION['status'];
  $mdata = $_SESSION['mimetype'];
  $mdata = str_replace('/','_',$mdata);
  $odata = $_SESSION['object'];
  $idata = $_SESSION['imagepath'];
  $fdata = $_SESSION['toastedfile'];
  header('Content-Type: application/json');
  $arr = array('status' => $sdata,  'mimetype' => $mdata, 'object' => $odata, 'imagepath' => $idata, 'toastedfile' => $fdata);
  echo json_encode( $arr);
}
?>
