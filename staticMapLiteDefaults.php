<?php

/**
 * GFStaticMapLite 
 *
 * Copyright 2017 Geofabrik GmbH
 *
 * This software is based on staticmapLite 0.02
 *
 * Copyright 2009 Gerhard Koch
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
 */ 

/**
 * Default configuration
 *
 * Explanation of the properties can be found in config.sample.php file.
 */
Class staticMapLiteDefaults {

    /**
     * Available markers.
     *
     * Properties:
     *
     * * `filename`: filename relative to the `$markerBaseDir` pointing to a
     *   greyscale image of the marker
     * * `maskname`: filename relative to the `$markerBaseDir` pointing to a
     *   image whose black pixels will be filled with the marker color. The
     *   marker image (`filename` property) will be placed on top of it.
     * * `width`: width of the marker
     * * `height`: height of the marker
     * * `hotx`: x coordinate of the "tip" of the marker
     * * `hoty`: y coordinate of the tip of the marker
     * * `textx`: x coordinate of the text label
     * * `texty`: y coordinate of the text label
     * * `textsize`: size of the text
     * * `font`: font file to be used. This must be TTF font located in the
     *   fonts/ directory.
     */
    protected $markerLookup = array (
            'default/marker' => array(
                'filename' => 'default/marker.png',
                'maskname' => 'default/marker_colorize.png',
                'width' => 24,
                'height' => 40,
                'hotx' => 12,
                'hoty' => 40,
                'textx' => 12,
                'texty' => 18,
                'textsize' => 12,
                'font' => 'LiberationSans-Bold.ttf',
                ),
            'print150/marker' => array(
                'filename' => 'print150/marker.png',
                'maskname' => 'print150/marker_colorize.png',
                'width' => 48,
                'height' => 40,
                'hotx' => 24,
                'hoty' => 80,
                'textx' => 24,
                'texty' => 36,
                'textsize' => 24,
                'font' => 'LiberationSans-Bold.ttf',
                ),
            'print/marker' => array(
                'filename' => 'print/marker.png',
                'maskname' => 'print/marker_colorize.png',
                'width' => 96,
                'height' => 80,
                'hotx' => 48,
                'hoty' => 160,
                'textx' => 48,
                'texty' => 72,
                'textsize' => 48,
                'font' => 'LiberationSans-Bold.ttf',
                ),
            'default/ol-marker' => array (
                'filename' => 'default/ol-marker.png',
                'maskname' => 'default/ol-marker_colorize.png',
                'width' => 21,
                'height' => 25,
                'hotx' => 10.5,
                'hoty' => 25,
                'textx' => 21,
                'texty' => 0,
                'textsize' => 12,
                'font' => 'LiberationSans-Bold.ttf',
                ),
            'print150/ol-marker' => array (
                'filename' => 'print150/ol-marker.png',
                'maskname' => 'print150/ol-marker_colorize.png',
                'width' => 84,
                'height' => 100,
                'hotx' => 42,
                'hoty' => 100,
                'textx' => 100,
                'texty' => 0,
                'textsize' => 48,
                'font' => 'LiberationSans-Bold.ttf',
                ),
            'print/ol-marker' => array (
                'filename' => 'print/ol-marker.png',
                'maskname' => 'print/ol-marker_colorize.png',
                'width' => 84,
                'height' => 100,
                'hotx' => 42,
                'hoty' => 100,
                'textx' => 100,
                'texty' => 0,
                'textsize' => 48,
                'font' => 'LiberationSans-Bold.ttf',
                ),
            'default/pin' => array (
                'filename' => 'default/pin.png',
                'maskname' => 'default/pin_colorize.png',
                'width' => 20,
                'height' => 40,
                'hotx' => 10,
                'hoty' => 40,
                'textx' => 11,
                'texty' => 16,
                'textsize' => 12,
                'font' => 'LiberationSans-Bold.ttf',
                ),
            'print150/pin' => array (
                'filename' => 'print150/pin.png',
                'maskname' => 'print150/pin_colorize.png',
                'width' => 40,
                'height' => 80,
                'hotx' => 20,
                'hoty' => 80,
                'textx' => 21,
                'texty' => 32,
                'textsize' => 24,
                'font' => 'LiberationSans-Bold.ttf',
                ),
            'print/pin' => array (
                'filename' => 'print/pin.png',
                'maskname' => 'print/pin_colorize.png',
                'width' => 80,
                'height' => 160,
                'hotx' => 40,
                'hoty' => 160,
                'textx' => 42,
                'texty' => 64,
                'textsize' => 48,
                'font' => 'LiberationSans-Bold.ttf',
                ),
            );

    protected $markerBaseDir = 'images';

    protected $fontBaseDir = 'fonts/';

    protected $useTileCache = true;

    protected $tileCacheBaseDir = 'cache/tiles';

    protected $useMapCache = true;

    protected $doNotReadMapCache = false;

    protected $ignoreNoCacheProperty = false;

    protected $mapCacheBaseDir = 'cache/maps';

    protected $attribution = true;

    protected $attributionFont = 'NotoSansUI-Regular';

    protected $maxTileCount = 9;

    protected $maxSize = 2048 * 2048;

    protected $tileSources = array();
}
?>
