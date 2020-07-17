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

    public function __construct($lat, $lon, $image, $color, $fontColor, $label, $font = null) {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->image = $image;
        $this->color = $color;
        $this->fontColor = $fontColor;
        $this->label = $label;
        if ($font != null) {
            $this->font = $font . '.ttf';
        } else {
            $this->font = null;
        }
    }
}
?>
