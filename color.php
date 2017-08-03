<?php
/**
 * Copyright 2017 Geofabrik GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Michael Reichert <michael.reichert@geofabrik.de>
 */ 

Class Color {
    public $red = 255;
    public $green = 0;
    public $blue = 0;
    public $alpha = 255;

    public function __construct($red, $green, $blue, $alpha) {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
        $this->alpha = $alpha;
    }

    public static function colorFromHex($hexString){
        if (strlen($hexString) < 6 || strlen($hexString) == 7 || strlen($hexString) > 8) {
            output_error('Color string must be 6 or 8 characters long. ' . $hexString . ' is not.');
        }
        $red = substr($hexString, 0, 2);
        $green = substr($hexString, 2, 2);
        $blue = substr($hexString, 4, 2);
        $alpha = 'FF';
        if (strlen($hexString) == 8) {
            $alpha = substr($hexString, 6, 2);
        }
        if (!ctype_xdigit($red) || !ctype_xdigit($green) || !ctype_xdigit($blue) || !ctype_xdigit($alpha)) {
            output_error('Error parsing color value');
        }
        return new Color(hexdec($red), hexdec($green), hexdec($blue), hexdec($alpha));
    }

    public function allocate($image) {
        // adjust alpha to the range allowed by GD library
        // first invert the scale (0:transparent..255:opaque) to (0:opaque..255:transparent)
        $alphaGD = 255 - $this->alpha;
        $alphaGD = $alphaGD * 0.49;
        if ($this->alpha == 0) {
            $alphaGD = 127;
        }
        error_log('transp: ' . $this->alpha . ' ' . $alphaGD);
        return imagecolorallocatealpha($image, $this->red, $this->green, $this->blue, $alphaGD);
    }

    public function isTransparent() {
        return ($this->alpha == 0);
    }
}

?>
