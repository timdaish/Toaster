<?php
require_once 'class.GifDecoder.php';

        $lf = file_get_contents("c:\\jq\\slideshow.gif");

        $animsavedirfile = "c:\\temp\\";
        // split out the frames
        $decoder = new A2_GIF_Decoder ($lf);
        $frames = $decoder->getFrames();

        for ( $i = 0; $i < count ( $frames ); $i++ ) {
            $fname = ( $i < 10 ) ? $animsavedirfile."_frame0$i.gif" : $animsavedirfile."_frame$i.gif";
            fwrite ( fopen ( $fname, "wb" ), $frames [ $i ] );
        }

?>
