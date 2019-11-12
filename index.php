<?php

function backgroundMasking($path)
{
    $imagick = new \Imagick(realpath($path));
 
    $backgroundColor = "rgb(255, 255, 255)";
    $fuzzFactor = 0.6;

    $outlineImagick = clone $imagick;
    $outlineImagick->transparentPaintImage(
        $backgroundColor, 0, $fuzzFactor * \Imagick::getQuantum(), false);

    $mask = clone $imagick;
    $mask->setImageAlphaChannel(\Imagick::ALPHACHANNEL_DEACTIVATE);
    $mask->transformImageColorSpace(\Imagick::COLORSPACE_GRAY);

    $mask->compositeImage(
        $outlineImagick,
        \Imagick::COMPOSITE_DSTOUT,
        0, 0
    );

    $mask->negateImage(false);
 
    $fillPixelHoles = false;
     
    if ($fillPixelHoles == true) {

        $mask->blurimage(2, 1);
        $mask->whiteThresholdImage("rgb(10, 10, 10)");
        $mask->blurimage(2, 1);
        $mask->blackThresholdImage("rgb(255, 255, 255)");
    }

    $mask->blurimage(2, 2);

    $contrast = 15;
    $midpoint = 0.7 * \Imagick::getQuantum();
    $mask->sigmoidalContrastImage(true, $contrast, $midpoint);
 

    $imagick->compositeimage(
        $mask,
        \Imagick::COMPOSITE_COPYOPACITY,
        0, 0
    );
 

    $canvas = new \Imagick();
    $canvas->newPseudoImage(
        $imagick->getImageWidth(),
        $imagick->getImageHeight(),
        "pattern:checkerboard"
    );
    $canvas->compositeimage($imagick, \Imagick::COMPOSITE_ATOP, -60, -220);

    $canvas->setImageFormat('png');
    $to = 'result.png';
    file_put_contents($to,$canvas->getImageBlob());
    return $to;
}



if (isset($_POST['send'])) {
    if (!empty($_FILES['image']['name'])) {
        $image = basename($_FILES['image']['name']);
        move_uploaded_file($_FILES["image"]["tmp_name"],$image);
        $result = backgroundMasking($image);
    }
}


?>


<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<img src="result.png">
	<form method="POST" enctype="multipart/form-data">
		<input type="file" name="image">
		<input type="submit" name="send" value="send">
	</form>
</body>
</html>