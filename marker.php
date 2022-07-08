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

Class Marker {
    public $lat;
    public $lon;
    public $image;
    public $color;
    public $fontColor;
    public $label;
    public $font;
    public $lineHeight;

    public function __construct($lat, $lon, $image, $color, $fontColor, $label, $popup, $labelFont, $popupFont, $popupFontSize, $lineHeight) {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->image = $image;
        $this->color = $color;
        $this->fontColor = $fontColor;
        $this->label = $label;
        $this->popup = explode("\n", $this->unescapeComma($popup));
        $this->font = $this->appendTTF($labelFont);
        $this->popupFont = $this->appendTTF($popupFont);
        $this->popupFontSize = $popupFontSize;
        $this->lineHeight = $lineHeight;
    }

    private function unescapeComma($escaped) {
        if ($escaped == null) {
            return null;
        }
        $len = strlen($escaped);
        $result = "";
        $lastEscape = -99;
        for ($i = 0; $i < $len; $i++) {
            if ($escaped[$i] === '\\' && $i + 1 < $len && $escaped[$i + 1] === ',') {
                $result = $result . ',';
                $lastEscape = $i;
            } else if ($escaped[$i] === '\\' && $i + 1 < $len && $escaped[$i + 1] === '\\') {
                $result = $result . '\\';
                $lastEscape = $i;
            } else if ($lastEscape + 1 < $i){
                $result = $result . $escaped[$i];
            }
        }
        return $result;
    }

    private function appendTTF($fontName) {
        if ($fontName != null) {
            return $fontName . '.ttf';
        }
        return null;
    }

    private function setAbsFontPath($font, $fontBaseDir, $fallbackFont) {
        if ($font == null) {
            $font = $fallbackFont;
        }
        $font = $fontBaseDir . '/' . $font;
        // Make font path absolute
        if ($font[0] != '/') {
            $font = dirname(__FILE__) . '/' . $font;
        }
        return $font;
    }

    /**
     * Set absolute path for fonts.
     */
    public function setAbsFontPaths($fontBaseDir, $fallbackFont) {
        $this->font = $this->setAbsFontPath($this->font, $fontBaseDir, $fallbackFont);
        $this->popupFont = $this->setAbsFontPath($this->popupFont, $fontBaseDir, $fallbackFont);
    }

    /**
     * Return size of popup box.
     */
    public function getPopupBoxSize() {
        $width = 0;
        $height = 0;
        foreach($this->popup as $line) {
            $arr = imagettfbbox($this->popupFontSize, 0, $this->popupFont, $line);
            $width = max($width, $arr[2] - $arr[0]);
            $height += $arr[3] - $arr[5];
        }
        return array($width, $height);
    }

    /**
     * Render a rectangle with rounded corners.
     *
     * img: image resource
     * x1: bottom left X coordinate
     * y1: bottom left Y coordinate
     * x2: top right X coodinate
     * y2: top right Y coordinate
     * radius: corner radius
     */
    private function renderRoundedRect($img, $x1, $y1, $x2, $y2, $radius) {
        $white = imagecolorallocate($img, 255, 255, 255);
        $red = imagecolorallocate($img, 255, 0, 0);
        $padding = 5;
        imagefilledarc($img, $x1+$radius, $y2+$radius, $radius*2, $radius*2, 180, 270, $white, IMG_ARC_PIE);
        imagefilledarc($img, $x2-$radius, $y2+$radius, $radius*2, $radius*2, 270, 0, $white, IMG_ARC_PIE);
        imagefilledarc($img, $x2-$radius, $y1-$radius, $radius*2, $radius*2, 0, 90, $white, IMG_ARC_PIE);
        imagefilledarc($img, $x1+$radius, $y1-$radius, $radius*2, $radius*2, 90, 180, $white, IMG_ARC_PIE);
        imagefilledrectangle($img, $x1+$radius, $y1, $x2-$radius, $y2, $white);
        imagefilledrectangle($img, $x1, $y1-$radius, $x2, $y2+$radius, $white);
    }

    private function hasPopup() {
        return !(count($this->popup) == 0 || (count($this->popup) == 1 && $this->popup[0] === ''));
    }

    private function renderFilledPolygon($imageResource, $points, $color) {
        $version = explode('.', phpversion());
        if (intval($version[0]) < 8) {
            imagefilledpolygon($imageResource, $points, count($points), $color);
        } else {
            imagefilledpolygon($imageResource, $points, $color);
        }
    }

    /**
     * Render popup box on the map.
     *
     * Arguments:
     * tipX: X coordinate of the tip
     * tipY: Y coordinate of the tip
     */
    public function placePopupBox($imageResource, $tipX, $tipY) {
        if (!$this->hasPopup()) {
            return;
        }
        $textDimensions = $this->getPopupBoxSize();
        $padding = 5;
        $textDimensions[0] += 2 * $padding;
        $textDimensions[1] += 2 * $padding;
        $tipHeight = 10;
        $radius = $tipHeight;
        $lineCount = count($this->popup);
        $lineDiff = $this->lineHeight - $this->popupFontSize;
        $this->renderRoundedRect(
            $imageResource,
            $tipX,
            $tipY - $tipHeight,
            $tipX + $textDimensions[0],
            $tipY - $tipHeight - $textDimensions[1] - 2 * $padding - ($lineCount - 1) * $lineDiff,
            $radius
        );
        $tipPoints = array($tipX, $tipY, $tipX + $radius, $tipY - $tipHeight, $tipX, $tipY - $tipHeight - $radius);
        $white = imagecolorallocate($imageResource, 255, 255, 255);
        $this->renderFilledPolygon($imageResource, $tipPoints, $white);
        $i = 0;
        $y = $tipY - $tipHeight - $textDimensions[1] - $padding - ($lineCount - 1) * $lineDiff + $this->popupFontSize;
        $x = $tipX + $padding;
        $black = imagecolorallocate($imageResource, 0, 0, 0);
        for ($i = 0; $i < $lineCount; $i++) {
            $line = $this->popup[$i];
            imagettftext($imageResource, $this->popupFontSize, 0, $x, $y, $black, $this->popupFont, $line);
            $y = $y + $textDimensions[1] / $lineCount + $lineDiff;
        }
    }
}
?>
