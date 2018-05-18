<?php
    // *** Include the class
    include("resize-class.php");

    // *** 1) Initialize / load image
    $resizeObj = new resize('sampleimg.jpg');

    // *** 2) Resize image (options: exact, portrait, landscape, auto, crop)
    $resizeObj -> resizeImage(600, 200, 'crop');

    // *** 3) Save image
    $resizeObj -> saveImage('sample-resized.png', 100);


?>
