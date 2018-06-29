<?php
require_once("class.GifDecoder.php");


if(file_exists($FIC))
{
  //echo ($FIC.  "exists");
  $GIF_frame = fread (fopen ($FIC,'rb'), filesize($FIC));
  $decoder = new A2_GIF_Decoder ($GIF_frame);

    // count frames
   $framecnt = $decoder->GetFrameCount();

    echo('image is animated: no. of frames:' . $framecnt);
  //extract frames
  //$frames = $decoder->GetFrames();

  //for ( $i = 0; $i < count ( $frames ); $i++ )
  //{
  //  $fname = ( $i < 10 ) ? $FIC."_0$i.gif" : $FIC."_$i.gif";
  //  $hfic=fopen ( $fname, "wb" );
  //  fwrite ($hfic , $frames [ $i ] );
  //  fclose($hfic);
  //}
}
?>