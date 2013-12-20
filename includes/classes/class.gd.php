<?PHP
    // Simple wrapper class for GD
    class GD
    {
        public $im     = null;
        public $width  = null;
        public $height = null;
        public $type   = null;
        public $mime   = null;
        public $images = array();


        public function __construct($data = null, $ext = null)
        {
            if(is_resource($data) && get_resource_type($data) == 'gd')
                return $this->loadResource($data);
            elseif(@file_exists($data) && is_readable($data))
                return $this->loadFile($data);
            elseif(is_string($data))
                return $this->loadString($data);
            else
                return false;
        }


        private function loadResource($im)
        {
            if(!is_resource($im) || !get_resource_type($im) == 'gd') return false;

            $this->im     = $im;
            $this->width  = imagesx($im);
            $this->height = imagesy($im);

            return true;
        }


        private function loadFile($filename)
        {
            if(!file_exists($filename) || !is_readable($filename)) return false;

            $info = getimagesize($filename);
            $this->width  = $info[0];
            $this->height = $info[1];
            $this->type   = image_type_to_extension($info[2], false);
            $this->mime   = $info['mime'];

            if($this->type == 'jpeg' && (imagetypes() & IMG_JPG))
                $this->im = imagecreatefromjpeg($filename);
            elseif($this->type == 'png' && (imagetypes() & IMG_PNG))
                $this->im = imagecreatefrompng($filename);
            elseif($this->type == 'gif' && (imagetypes() & IMG_GIF))
                $this->im = imagecreatefromgif($filename);
            else
                return false;

            return true;
        }


        private function loadString($str)
        {
            $im = @imagecreatefromstring($str);
            return ($im === false) ? false : $this->loadResource($im);
        }


        public function saveAs($filename, $type = 'jpg', $quality = 75)
        {
            if($type == 'jpg' && (imagetypes() & IMG_JPG))
            {
                imageinterlace($this->im, true);
                return imagejpeg($this->im, $filename, $quality);
            }
            elseif($type == 'png' && (imagetypes() & IMG_PNG))
                return imagepng($this->im, $filename);
            elseif($type == 'gif' && (imagetypes() & IMG_GIF))
                return imagegif($this->im, $filename);
            else
                return false;
        }


        // Output file to browser
        public function output($type = 'jpg', $quality = 75)
        {
            if($type == 'jpg' && (imagetypes() & IMG_JPG))
            {
                header("Content-Type: image/jpeg");
                imagejpeg($this->im, null, $quality);
                return true;
            }
            elseif($type == 'png' && (imagetypes() & IMG_PNG))
            {
                header("Content-Type: image/png");
                imagepng($this->im);
                return true;
            }
            elseif($type == 'gif' && (imagetypes() & IMG_GIF))
            {
                header("Content-Type: image/gif");
                imagegif($this->im);
                return true;
            }
            else
                return false;
        }


        // Return image data as a string.
        // Is there a way to do this without using output buffering?
        public function toString($type = 'jpg', $quality = 75)
        {
            ob_start();

            if($type == 'jpg' && (imagetypes() & IMG_JPG))
                imagejpeg($this->im, null, $quality);
            elseif($type == 'png' && (imagetypes() & IMG_PNG))
                imagepng($this->im);
            elseif($type == 'gif' && (imagetypes() & IMG_GIF))
                imagegif($this->im);

            return ob_get_clean();
        }


        // Resizes an image and maintains aspect ratio.
        public function scale($new_width = null, $new_height = null)
        {
            if(!is_null($new_width) && is_null($new_height))
                $new_height = $new_width * $this->height / $this->width;
            elseif(is_null($new_width) && !is_null($new_height))
                $new_width = $this->width / $this->height * $new_height;
            elseif(!is_null($new_width) && !is_null($new_height))
            {
                if($this->width < $this->height)
                    $new_width = $this->width / $this->height * $new_height;
                else
                    $new_height = $new_width * $this->height / $this->width;
            }
            else
                return false;

            return $this->resize($new_width, $new_height);
        }


        // Resizes an image to an exact size
        public function resize($new_width, $new_height)
        {
            $dest = imagecreatetruecolor($new_width, $new_height);

            // Transparency fix contributed by Google Code user 'desfrenes'
            imagealphablending($dest, false);
            imagesavealpha($dest, true);

            if(imagecopyresampled($dest, $this->im, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height))
            {
                $this->im = $dest;
                $this->width = imagesx($this->im);
                $this->height = imagesy($this->im);
                return true;
            }

            return false;
        }


        public function crop($x, $y, $w, $h)
        {
            $dest = imagecreatetruecolor($w, $h);

            if(imagecopyresampled($dest, $this->im, 0, 0, $x, $y, $w, $h, $w, $h))
            {
                $this->im = $dest;
                $this->width = $w;
                $this->height = $h;
                return true;
            }

            return false;
        }


        public function cropCentered($w, $h)
        {
            $cx = $this->width / 2;
            $cy = $this->height / 2;
            $x = $cx - $w / 2;
            $y = $cy - $h / 2;
            if($x < 0) $x = 0;
            if($y < 0) $y = 0;
            return $this->crop($x, $y, $w, $h);
        }


        public function availableWallpapers() // returns an array of possible resolutions
        {
            $sizes = array();
            $sizes['width'] = array('2560', '2560', '1920', '1680', '1600', '1440', '1280', '1024', '800', '480', '360');
            $sizes['height'] = array('1920','1600', '1200', '1050', '1200', '960', '1024', '768', '600', '360', '480');
            $possible = array();

            foreach($sizes['width'] as $key=>$width)
            {
                $height = $sizes['height'][$key];

                if (($this->width >= $width) OR ($this->height >= $height))
                {
                   $possible[] = array('width'=>$width,'height'=>$height);
                }
            }

            if (count($possible) > 0)
            {
                return $possible;
            }

            else
            {
                return false;
            }
        }


        public function resizeToResolution($width, $height, $quality=85, $save=false)
        {
            # Ensure that the image is large enough to scale
            if (($this->width >= $width) AND ($this->height >= $height))
            {
                # Get the aspect ratios
                $resolution_aspect  = round($width / $height, 2);
                $image_aspect       = round($this->width / $this->height, 2);

                # Scale according to the height
                if ($resolution_aspect <= $image_aspect)
                {
                    $this->scale(null, $height);
                }

                # Scale according to width
                else
                {
                    $this->scale($width, null);
                }

                # Crop the center
                $this->cropCentered($width, $height);

                # We are not saving the image, so let's output it directly to the browser
                if ($save == false)
                {
                    $this->output('jpg', $quality);
                }
            }
            return false;
        }


        // Build a collage using the GD function
        public function collage($width, $height, $quality=85)
        {
            if (count($this->images) == 0) return false;

            $count          = count($this->images);
            $vert_parts     = 1;
            $horz_parts     = 1;
            $max_width      = 3;
            $found          = false;

            // Up to three images we won't be able to tile really, so lets tile them vertically
            if ($count<=$max_width)
            {
                $vert_parts = 1;
                $horz_parts = $count;
            }

            else
            {
                for($x = 20; $x >= 2; $x--)
                {
                    for($y = 20; $y >= $x; $y--)
                    {
                        if (($x * $y) <= $count)
                        {
                            $vert_parts = $y;
                            $horz_parts = $x;
                            $found = true;
                        }
                        if ($found == true) break;
                    }
                    if ($found == true) break;
                }
            }

            $imHeight       = ceil(round($height / $vert_parts, 2));
            $imWidth        = ceil(round($width / $horz_parts, 2));
            $canvas         = imagecreatetruecolor($width, $height);
            $offsetX        = 0;
            $offsetY        = 0;
            $c              = 0;
            $row            = 0;

            for($i=0; $i<($horz_parts * $vert_parts); $i++)
            {
                if ($c == $horz_parts) { $c = 0; $row++; }
                $offsetX = $c;
                $offsetY = $row;
                $this->loadFile($this->images[$i]);
                $this->resizeToResolution($imWidth, $imHeight, 80, true);
                imagecopyresampled($canvas, $this->im, ($offsetX * $this->width), ($offsetY * $this->height), 0, 0, $this->width, $this->height, $this->width, $this->height);
                $c++;
            }
            $this->loadResource($canvas);
            return true;

            // Printing something pretty to confirm the output
            echo "<div class='container'>";
            echo "Horizontal Parts: " . $horz_parts . ", Vertical Parts: " . $vert_parts . ", TOTAL: " . $horz_parts * $vert_parts;
            echo '<table class="table table-bordered" style="width: ' . $width . 'px;">';
            for ($i = 1; $i <= $vert_parts; $i++)
            {
                echo "<tr valign='middle' align='center' style='height: " . $imHeight . "px;'>";
                for ($x = 1; $x <= $horz_parts; $x++)
                {
                    echo "<td style='width: " . $imWidth . "px;'>";
                    echo "<h2><small>" . $imWidth . " X " . $imHeight . "</small></h2>";
                    echo "</td>";
                }
                echo "</tr>";
            }
            echo "</table>
                </div>";
            return true;
        }


        // Bluid a collage using the GD function, This adds to the images array which we will use to build the collage
        public function collageLoadFile($filename)
        {
            if(!file_exists($filename) || !is_readable($filename)) return false;
            $this->images[] .= $filename;
            return true;
        }
    }