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
    public $green = 0, $blue = 0;

    public function __construct($red, $green, $blue) {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    public static function colorFromHex($hexString){
        if (strlen($hexString) != 6) {
            output_error('Color string must be 6 characters long.');
        }
        $red = substr($hexString, 0, 2);
        $green = substr($hexString, 2, 2);
        $blue = substr($hexString, 4, 2);
        if (!ctype_xdigit($red) || !ctype_xdigit($green) || !ctype_xdigit($blue)) {
            output_error('Error parsing color value');
        }
        return new Color(hexdec($red), hexdec($green), hexdec($blue));
    }

    public function colorAllocate($image) {
        return imagecolorallocate($image, $this->red, $this->green, $this->blue);
    }
}

?>
