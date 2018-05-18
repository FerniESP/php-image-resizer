<?php

Class resize
{
    private $image;
    private $width;
    private $height;
    private $imageResized;

    function __construct($fileName)
    {
      //*** Open up the file
      $this->image = $this->openImage($fileName);

      //*** Get width and height
      $this->width = imagesx($this->image);
      $this->height = imagesy($this->image);
    }

    private function openImage($file)
    {
        //*** Get extension
        $extension = strtolower(strrchr($file,'.'));

        switch($extension)
        {
          case '.jpg':
          case '.jpeg':
            $img = @imagecreatefromjpeg($file);
            break;
          case '.gif':
            $img = @imagecreatefromgif($file);
            break;
          case '.png':
            $img = @imagecreatefrompng($file);
            break;
          default:
            $img = false;
            break;
        }
        return $img;
    } //openImage end

    public function resizeImage($newWidth, $newHeight, $option="auto")
    {
      //*** Get optimal width and height - based on $option
      $optionArray = $this->getDimensions($newWidth, $newHeight, strtolower($option));

      $optimalWidth = $optionArray['optimalWidth'];
      $optimalHeight = $optionArray['optimalHeight'];

      //*** Resample - create image canvas of x, y size
      $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
      imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);

      //*** if option is 'crop', then crop //
      if ($option == 'crop') {
        $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
      }
    } //resuzeImage end

    private function getDimensions($newWidth, $newHeight, $option)
    {
      switch ($option)
      {
        case 'exact':
          $optimalWidth = $newWidth;
          $optimalHeight = $newHeight;
          break;
        case 'portrait':
          $optimalWidth = $this->getSizeByFixedHeight($newHeight);
          $optimalHeight = $newHeight;
        case 'landscape':
          $optimalWidth = $newWidth;
          $optimalHeight = $this->getSizeByFixedWidth($newWidth);
          break;
        case 'auto':
          $optionArray = $this->getSizeByAuto($newWidth, $newHeight);
          $optimalWidth = $optionArray['optimalWidth'];
          $optimalHeight = $optionArray['optimalHeight'];
          break;
        case 'crop':
          $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
          $optimalWidth = $optionArray['optimalWidth'];
          $optimalHeight = $optionArray['optimalHeight'];
          break;
      }
      return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    } //getDimensions end

    private function getSizeByFixedHeight($newHeight)
    {
      $ratio = $this->width / $this->height;
      $newWidth = $newheight * $ratio;
      return $newWidth;
    }

    private function getSizeByFixedWidth($newWidth)
    {
      $ratio = $this->height / $this->width;
      $newHeight = $newWidth * $ratio;
      return $newHeight;
    }

    private function getSizeByAuto($newWidth, $newHeight)
    {
      //*** Image to be resized is wider (landscape)
      if ($this->height < $this->width) {
        $optimalWidth = $newWidth;
        $optimalHeight = $this->getSizeByFixedWidth($newWidth);
      }
      //*** Image to be resized is taller (portrait)
      else if ($this->height > $this->width) {
        $optimalWidth = $this->getSizeByFixedHeight($newHeight);
        $optimalHeight = $newHeight;
      }
      //*** Image to be resized is a square
      else {
        if ($newHeight < $newWidth) {
          $optimalWidth = $newWidth;
          $optimalHeight = $this->getSizeByFixedWidth($newWidth);
        }
        else if ($newHeight > $newWidth) {
          $optimalWidth = $this->getSizeByFixedHeight($newHeight);
          $optimalHeight = $newHeight;
        }
        //*** Square is being resiex to a square
        else {
          $optimalWidth = $newWidth;
          $optimalHeight = $newHeight;
        }
      }
      return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    } //getSizeByAuto end

    private function getOptimalCrop($newWidth, $newHeight)
    {
        $heightRatio = $this->height / $newHeight;
        $widthRatio = $this->width / $newWidth;

        if ($heightRatio < $widthRatio) {
          $optimalRatio = $heightRatio;
        }
        else {
          $optimalRatio = $widthRatio;
        }

        $optimalHeight = $this->height / $optimalRatio;
        $optimalWidth = $this->width / $optimalRatio;

        return array ('optimalWidth' => $optimalWidth, 'optimalHeight'=> $optimalHeight);
    } //getOptimalCrop end

    private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight)
    {
      //***Finx center - this will be used for the crop
      $cropStartX = ( $optimalWidth / 2 ) - ( $newWidth /2);
      $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2);

      $crop = $this->imageResized;
      //imagedestroy($this->imageResized);

      //*** Now crop from center to exact requeted size
      $this->imageResized = imagecreatetruecolor($newWidth, $newHeight);
      imagecopyresampled($this->imageResized, $crop, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight, $newWidth, $newHeight);
    } //crop end

    public function saveImage($savePath, $imageQuality="100")
    {
      //***Get extension
      $extension = strrchr($savePath,'.');
      $extension = strtolower($extension);

      switch($extension)
      {
        case '.jpg':
        case '.jpeg':
          if (imagetypes() & IMG_JPG) {
            imagejpeg($this->imageResized, $savePath, $imageQuality);
          }
          break;
        case '.gif':
          if (imagetypes() & IMG_GIF) {
            imagegif($this->imageResized, $savePath);
          }
          break;
        case '.png':
          //***Scale quality from 0-100 to 0-9
          $scaleQuality = round(($imageQuality/100) * 9);

          //*** Invert quality setting as 0 is best, not 9
          $invertScaleQuality = 9 - $scaleQuality;

          if (imagetypes() & IMG_PNG) {
            imagepng($this->imageResized, $savePath, $invertScaleQuality);
          }
          break;

          default:
          // *** No extension - No save.
            break;
      }

      imagedestroy($this->imageResized);
    } //saveImage end
}

?>
