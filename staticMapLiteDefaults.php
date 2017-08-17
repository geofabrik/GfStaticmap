<?php

/**
 * staticMapLite 0.02
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
 *
 * @author Michael Reichert <michael.reichert@geofabrik.de>
 */ 

/**
 * Default configuration
 */
Class staticMapLiteDefaults {

    protected $tileSize = 256;

    /**
     * Available map tile sources.
     *
     * The parameters `{X}`, `{Y}` and `{Z}` must be used in the URL
     * and represent the X, Y and Z index of the map tiles.
     *
     * Following parameters are optional and only have to be set if the tile server
     * needs them:
     *
     * * `{P}` API key. If the URL contains a variable string like an API key, insert `{P}` at
     *   the location where the API key would be inserted.
     */
    protected $tileSrcUrl = array(
            'print' => 'http://print.tile.geofabrik.de{P}/{Z}/{X}/{Y}.png',
            'print150' => 'http://print.tile.geofabrik.de{P}/{Z}/{X}/{Y}.png',
            'default' => 'http://tile.geofabrik.de{P}/{Z}/{X}/{Y}.png'
            );
/**
     * Available markers.
     *
     * Properties:
     *
     * * `filename`: filename relative to the `$markerBaseDir`
     * * `width`: width of the marker
     * * `height`: height of the marker
     * * `hotx`: x coordinate of the "tip" of the marker
     * * `hoty`: y coordinate of the tip of the marker
     * * `textx`: x coordinate of the text label
     * * `texty`: y coordinate of the text label
     * * `textsize`: size of the text
     * * `font`: font file to be used. This must be TTF font located in the fonts/ directory.
     */
    protected $markerLookup = array (
            'default/redpin' => array (
                'filename' => 'default/redpin.png',
                'width' => 39,
                'height' => 39,
                'hotx' => 19,
                'hoty' => 39,
                'textx' => 19,
                'texty' => 16,
                'textsize' => 12,
                'font' => 'LiberationSans-Bold.ttf',
                ),
            'print/redpin' => array (
                'filename' => 'print/redpin.png',
                'width' => 154,
                'height' => 154,
                'hotx' => 77,
                'hoty' => 154,
                'textx' => 75,
                'texty' => 65,
                'textsize' => 45,
                'font' => 'LiberationSans-Bold.ttf',
                ),
            'print150/redpin' => array (
                'filename' => 'print150/redpin.png',
                'width' => 77 ,
                'height' => 77,
                'hotx' => 38,
                'hoty' => 77,
                'textx' => 38,
                'texty' => 33,
                'textsize' => 23,
                'font' => 'LiberationSans-Bold.ttf',
                ),
            );

    protected $tileDefaultSrc = 'mapnik';

    /** Marker directory */
    protected $markerBaseDir = 'images';

    /** Font directory */
    protected $fontBaseDir = 'fonts/';

    protected $useTileCache = false;

    /** Directory of the tile cache */
    protected $tileCacheBaseDir = 'cache/tiles';

    protected $useMapCache = true;
    protected $doNotWriteMapCache = false;
    protected $doNotReadMapCache = false;

    /** Directory of the cache of composed maps */
    protected $mapCacheBaseDir = 'cache/maps';
    protected $mapCacheID = '';
    protected $mapCacheFile = '';
    protected $mapCacheExtension = 'png';

    /** Should an attribution text being added at the lower right corner of the image by default? */
    protected $attribution = true;

    /** Font of the attribution text */
    protected $attributionFont = 'NotoSansUI-Regular';
}
?>
