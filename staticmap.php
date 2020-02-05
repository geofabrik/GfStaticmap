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
 * @author Gerhard Koch <gerhard.koch AT ymail.com>
 */ 

/*error_reporting(0);
ini_set('display_errors','off');
*/

require 'config.php';
require 'marker.php';
require 'linestring.php';

/**
 * implementation of the staticMap
 */
Class staticMapLite extends configuredStaticMap {
    /** zoom level of the map */
    protected $zoom;

    /** latitude of the center of the map */
    protected $lat;

    /** longitude of the center of the map */
    protected $lon;

    /** width of the map */
    protected $width;

    /** height of the map */
    protected $height;

    /** markers to be added to the map */
    protected $markers;

    /** lines/areas to be added to the map */
    protected $lines = array();

    /** the map image */
    protected $image;

    /** base map style to be used */
    protected $maptype;

    protected $centerX, $centerY, $offsetX, $offsetY;

    /** size of the tiles of the basemap */
    protected $tileSize = 0;

    /** API key used to retrieve tiles from the tile server */
    protected $apiKey = '';

    /** MD5 sum of the query parameters, used as key in the map cache */
    protected $mapCacheID = '';
    protected $mapCacheFile = '';
    protected $mapCacheExtension = 'png';

    /**
     * Shold the image not be written to the map cache.
     * This property will be set to true if the retrieval of a tile fails and
     * no useful image could be produced.
     */
    protected $doNotWriteMapCache = false;

    /**
     * HTTP status code to use for the response.
     */
    protected $statusCode = 200;

    public function __construct(){
        $this->zoom = 0;
        $this->lat = 0;
        $this->lon = 0;
        $this->width = 500;
        $this->height = 350;
        $this->markers = array();
        $this->lines = array();
        $this->maptype = $this->tileDefaultSrc;
    }

    /**
     * Parse parameters (query string)
     */
    public function parseParams(){
        global $_GET;

        // get zoom from GET paramter
        $this->zoom = $_GET['zoom']?intval($_GET['zoom']):0;
        if($this->zoom>18)$this->zoom = 18;

        // get lat and lon from GET paramter
        list($this->lat,$this->lon) = explode(',',$_GET['center']);
        $this->lat = floatval($this->lat);
        $this->lon = floatval($this->lon);

        // get width and height from GET paramters
        if($_GET['size']){
            list($this->width, $this->height) = explode('x',$_GET['size']);
            $this->width = intval($this->width);
            $this->height = intval($this->height);
        }
        if ($this->width > $this->maxSize || $this->height > $this->maxSize) {
            output_error('The requested map image exceeds the maximum size.', 413);
        }

        // markers parameter
        if(isset($_GET['markers'])){
            $markerCount = 1;
            // split up into markers
            $markers = preg_split('/%7C|\|/',$_GET['markers']);
            foreach($markers as $marker){
                // build array of keys and values
                $params = array();
                // First check if the marker definition contains a comma.
                // If it does not, it is the legacy version which we support here.
                $colon_pos = strpos($marker, ':', 1);
                if (!$colon_pos or $colon_pos >= strlen($marker) - 1) {
                    // No colon in the string and the colon is not used as label character.
                    // This is parsing of the legacy syntax.
                    $parts = explode(',', $marker);
                    if (count($parts) < 3) {
                        output_error('The marker definition was interpreted using the legacy syntax but it contained less than 4 elements for one or multiple markers.');
                    }
                    $params['lat'] = $parts[0];
                    $params['lon'] = $parts[1];
                    $params['image'] = $parts[2];
                    if (count($parts) > 3) {
                        $params['label'] = $parts[3];
                    }
                } else {
                    // New syntax.
                    // split up by , into key:value pairs
                    $kvPairs = explode(',', $marker);
                    foreach($kvPairs as $pair) {
                        list($key, $value) = explode(':', $pair, 2);
                        $params[trim($key)] = trim($value);
                    }
                }
                // parse parameters lat, lon and image – they are mandatory
                if (isset($params['lat']) && isset($params['lon']) && isset($params['image'])) {
                    $markerLat = floatval($params['lat']);
                    $markerLon = floatval($params['lon']);
                    $markerImage = basename($params['image']);
                    $markerColor = new Color(255, 0, 0, 255);
                    // parse color
                    if (isset($params['color'])) {
                        $markerColor = Color::colorFromHex($params['color']);
                    }
                    $fontColor = new Color(0, 0, 0, 255);
                    if (isset($params['fontcolor'])) {
                        $fontColor = Color::colorFromHex($params['fontcolor']);
                    }
                    // parse parameter label or use index of the marker if label is not set
                    $markerLabel = (string)$markerCount;
                    if (isset($params['label'])) {
                        if (strlen($params['label']) > 1) {
                            output_error('Labels of markers must be zero or one character long.');
                        }
                        $markerLabel = $params['label'];
                    } else if ($markerCount > 9) {
                        output_error('More than 9 unlabelled markers.');
                    }
                    // label font
                    if (isset($params['font'])) {
                        $font = $params['font'];
                        if (!is_readable($this->fontBaseDir . $font . '.ttf')) {
                            output_error('Font ' . $font . ' is not available. Please provide a ' .
                                'font which accessiable for the staticmap API.');
                        }
                        $this->markers[] = new Marker($markerLat, $markerLon, $markerImage,
                            $markerColor, $fontColor, $markerLabel, $font);
                    } else {
                        $this->markers[] = new Marker($markerLat, $markerLon, $markerImage,
                            $markerColor, $fontColor, $markerLabel);
                    }
                    $markerCount++;
                } else {
                    output_error('One of the mandatory marker arguments is missing: lat, lon, ' .
                        'image');
                }
            }
        }

        // path parameter
        if (isset($_GET['path'])) {
            // split up into single paths
            $paths = preg_split('/%7C|\|/', $_GET['path']);
            foreach ($paths as $path) {
                // split up by , into key:value pairs
                $kvPairs = explode(',', $path);
                $params = array();
                foreach ($kvPairs as $pair) {
                    list($key, $value) = explode(':', $pair, 2);
                    $params[trim($key)] = trim($value);
                }
                if (!isset($params['points'])) {
                    output_error('Mandatory argument points for path is missing.');
                }
                $lineColor = new Color(255, 0, 0, 255);
                if (isset($params['color'])) {
                    $lineColor = Color::colorFromHex($params['color']);
                }
                $fillColor = new Color(255, 255, 0, 255);
                if (isset($params['fillcolor'])) {
                    $fillColor = Color::colorFromHex($params['fillcolor']);
                }
                $lineWidth = 3;
                if (isset($params['width']) && is_numeric($params['width'])) {
                    $lineWidth = intVal($params['width']);
                }
                $ls = buildLineString($params['points'], $lineColor, $lineWidth, $fillColor);
                array_push($this->lines, $ls);
            }
        }

        // maptype parameter
        if(array_key_exists('maptype', $_GET)) {
            $this->maptype = $_GET['maptype'];
        }
        if(array_key_exists($this->maptype, $this->tileSources)) {
            $this->tileSrcUrl = $this->tileSources[$this->maptype]['url'];
	    $this->tileSize = $this->tileSources[$this->maptype]['tileSize'];
            // If useTileCache is set for this source, overwrite global setting.
	    if (array_key_exists('useTileCache', $this->tileSources[$this->maptype])) {
                $this->useTileCache = $this->tileSources[$this->maptype]['useTileCache'];
            }
        } else {
            output_error('Unknown maptype ' . $this->maptype);
        }

        // mapcache parameter
        if(isset($_GET['nocache']) && !$this->ignoreNoCacheProperty){
            $this->doNotReadMapCache = true;
        }

        // attribution parameter
        if(isset($_GET['attribution'])) {
           if ($_GET['attribution'] == 'false'){
               $this->attribution = false;
           } else if ($_GET['attribution'] != 'true') {
               output_error('Illegal option for parameter \'attribution\'');
           }
        }

        // attribution-font parameter
        if (isset($_GET['attribution-font'])) {
            $this->attributionFont = $_GET['attribution-font'];
            if (strlen($this->attributionFont) == 0) {
                output_error('Missing font name for attribution text.');
            }
            if (!is_readable($this->fontBaseDir . $this->attributionFont . '.ttf')) {
                output_error('Font ' . $font . ' is not available. Please provide a font which ' .
                   'accessiable for the staticmap API.');
            }
        }

        // parse API key
        $this->apiKey = $this->getApiKey();
    }


    public function initCoords(){
        $this->centerX = lonToTile($this->lon, ($this->zoom));
        $this->centerY = latToTile($this->lat, ($this->zoom));
        $this->offsetX = floor((floor($this->centerX)-$this->centerX)*$this->tileSize);
        $this->offsetY = floor((floor($this->centerY)-$this->centerY)*$this->tileSize);
    }

    /**
     * Create the base map (fetching tiles and placing them at the correct locations)
     */
    public function createBaseMap(){
        $this->image = imagecreatetruecolor($this->width, $this->height);
        // Allow to leave the image partially transparent if it exceeds the Web Mercator
        // definition area towards north/south.
        imagealphablending($this->image, FALSE);
        imagesavealpha($this->image, TRUE);
        $startX = floor($this->centerX-($this->width/$this->tileSize)/2);
        $startY = floor($this->centerY-($this->height/$this->tileSize)/2);
        $endX = ceil($this->centerX+($this->width/$this->tileSize)/2);
        $endY = ceil($this->centerY+($this->height/$this->tileSize)/2);
        $this->offsetX = -floor(($this->centerX-floor($this->centerX))*$this->tileSize);
        $this->offsetY = -floor(($this->centerY-floor($this->centerY))*$this->tileSize);
        $this->offsetX += floor($this->width/2);
        $this->offsetY += floor($this->height/2);
        $this->offsetX += floor($startX-floor($this->centerX))*$this->tileSize;
        $this->offsetY += floor($startY-floor($this->centerY))*$this->tileSize;

        $xTiles = $endX - $startX + 1;
        $yTiles = $endY - $startY + 1;
        if ($xTiles * $yTiles > $this->maxTileCount && $this->maxTileCount > 0) {
            output_error('The map you requested covers too much tiles. A map'
                . ' must only cover ' . $this->maxTileCount . ' tiles.', 413);
        }

        $added_tile = false;
        for($x=$startX; $x<=$endX; $x++){
            // normalise x index
            $xNorm = $x % (2 ** $this->zoom);
            for($y=$startY; $y<=$endY; $y++){
                $destX = ($x-$startX)*$this->tileSize+$this->offsetX;
                $destY = ($y-$startY)*$this->tileSize+$this->offsetY;
                // skip this tile if it is too far in the north or south
                if ($y < 0 || $y >= (2 ** $this->zoom)) {
                    // Make the area transparent.
                    imagefilledrectangle($this->image, $destX, $destY, $destX + $this->tileSize, $destY + $this->tileSize, 0xff000000);
                    continue;
                }
                $url = str_replace(array('{P}', '{Z}','{X}','{Y}'),array($this->apiKey, $this->zoom,
                    $xNorm, $y), $this->tileSrcUrl);
                try {
                    $tileData = $this->fetchTile($url);
                    $tileImage = imagecreatefromstring($tileData);
                } catch (Exception $ex) {
                    error_log('Tile request exception: ' . $ex->getMessage());
                    output_error('Failed to build your map image. Do you use the correct API key? Please email info@geofabrik.de if this error persists.', $this->statusCode);
                }
                imagecopy($this->image, $tileImage, $destX, $destY, 0, 0, $this->tileSize,
                    $this->tileSize);
                $added_tile = true;
            }
        }
        if (!$added_tile) {
            error_log('Background map composition error: No tiles found within the requested bounding box.');
            output_error('Failed to build your map image. No tiles of the background map found within the requested bounding box. Please email info@geofabrik.de if you sure that your map request was right.');
        }
        // Enable alpha blending now because we want to add the attribution
        imagealphablending($this->image, TRUE);
    }

    /**
     * Create an image which contains marker or a colorized marker mask.
     *
     * This method does not add the marker to the map image.
     *
     * @param markerFilename path to the marker
     *
     * @param markerLookupResult marker returned from $this->markerLookup array
     *
     * @param colorize Is the image to be added a mask to be colorized?
     *
     * @param marker array with the parameters describing the marker
     *
     * @return array with the X coordinate of the upper left corner as first
     * element and the Y coordinate of the upper left corner as second element.
     */
    protected function addMarkerOrMask($markerFilename, $markerLookupResult, $colorize, $marker) {
        if (!file_exists($markerFilename)) return;
        $markerImg = imagecreatefrompng($markerFilename);
        $destX = floor(($this->width/2)-$this->tileSize*($this->centerX - lonToTile($marker->lon,
            $this->zoom)));
        $destY = floor(($this->height/2)-$this->tileSize*($this->centerY -
            latToTile($marker->lat, $this->zoom)));
        $destY = $destY - $markerLookupResult['hoty'];
        $destX = $destX - $markerLookupResult['hotx'];
        // fill all black pixels of the image with the color of the marker
        if ($colorize) {
            imagefilter($markerImg, IMG_FILTER_COLORIZE, $marker->color->red, $marker->color->green,
                $marker->color->blue);
        }
        // add the marker to the map image
        imagecopy($this->image, $markerImg, $destX, $destY, 0, 0, imagesx($markerImg),
            imagesy($markerImg)); return array($destX, $destY);
    }

    /**
     * Render the markers on the map.
     */
    public function placeMarkers() {
        foreach($this->markers as $marker){
            $markerLat = $marker->lat;
            $markerLon = $marker->lon;
            $markerImage = $marker->image;
            // retrieve the marker details from the array of the available marker icons
            $mlu = $this->markerLookup[$this->maptype.'/'.$marker->image];
            $markerFilename = $this->markerBaseDir.'/'.$mlu['filename'];
            $markerMaskname = $this->markerBaseDir.'/'.$mlu['maskname'];
            list($destX, $destY) = $this->addMarkerOrMask($markerMaskname, $mlu, true, $marker);
            $this->addMarkerOrMask($markerFilename, $mlu, false, $marker);

            // determine label width
            $font = $this->fontBaseDir.'/'.$marker->font;
            $size = imagettfbbox($mlu['textsize'], 0, $font, $marker->label);
            $width = $size[4] - $size[0];

            // place label (1st marker=1 etc)
            $fontColor = $marker->fontColor->allocate($this->image);
            imagettftext($this->image, $mlu['textsize'], 0, $destX + $mlu['textx'] - $width/2,
                $destY + $mlu['texty'], $fontColor, $font, $marker->label); };
    }

    /**
     * Render the lines/polygons on the map.
     */
    public function placeLines() {
        foreach($this->lines as $line) {
            // build array of points transformed to pixel coordinates
            list($gdPArray, $numPoints) = $line->gdPointsArray($this->width, $this->height,
                $this->centerX, $this->centerY, $this->zoom, $this->tileSize);

            if (!($line->fillColor->isTransparent()) && $line->isClosed()) {
                imagefilledpolygon($this->image, $gdPArray, $numPoints,
                    $line->fillColor->allocate($this->image));
            }
            for ($i = 0; $i < $line->length() - 1; $i++) {
                imagesetthickness($this->image, $line->width);
                imageline($this->image, $gdPArray[$i * 2], $gdPArray[$i * 2 + 1],
                    $gdPArray[$i * 2 + 2], $gdPArray[$i * 2 + 3],
                    $line->lineColor->allocate($this->image));
            }
        }
    }

    /**
     * Build a path where to store a tile to be cached based on its URL.
     *
     * @return the destination path
     */
    public function tileUrlToFilename($url){
        return $this->tileCacheBaseDir."/".str_replace(array('http://'),'',$url);
    }

    /**
     * Check if a tile is available in the cache.
     *
     * @param url URL the tile was retrieved from.
     *
     * @return true if found in the cache, false otherwise
     */
    public function checkTileCache($url){
        $filename = $this->tileUrlToFilename($url);
        if(file_exists($filename)){
            return file_get_contents($filename);
        }
    }

    /**
     * Check if a file is already stored in the map cache.
     *
     * The cache is file based and uses the MD5 hash of the query parameters as
     * key (filename). The MD5 hash is generated from a serialization of the
     * parsed parameters, not from the original query string.
     *
     * There is no expiry mechanism.
     *
     * This method returns false if the cache is disabled.
     *
     * @param true if the map was found in the cache, false otherwise
     */
    public function checkMapCache(){
        if ($this->doNotReadMapCache) return false;
        $this->mapCacheID = md5($this->serializeParams());
        $filename = $this->mapCacheIDToFilename();
        if(file_exists($filename)) return true;
        return false;
    }

    /**
     * Helper function for checkMapCache() to provide a string which will be
     * hashed and used as key for the cache.
     *
     * @return MD5 hash of the serialized parameters
     */
    public function serializeParams(){		
        return join("&",array($this->zoom, $this->lat, $this->lon, $this->width, $this->height,
            serialize($this->markers), $this->maptype, $this->getApiKey()));
    }

    /**
     * Convert a cache key (MD5 hash) into the path where the image is store.
     */
    public function mapCacheIDToFilename(){
        //TODO make this a function which receives the hash as a parameter
        //instead of using a property of the instance of the class.
        if(!$this->mapCacheFile){
            $this->mapCacheFile = $this->mapCacheBaseDir . "/" . substr($this->mapCacheID,0,2) . "/"
                . substr($this->mapCacheID,2,2) . "/" . substr($this->mapCacheID,4);
        }
        return $this->mapCacheFile.".".$this->mapCacheExtension;
    }

    /**
     * Create a directory and its parents if they do not exist.
     *
     * @param pathname path
     *
     * @param mode file mode bits (Unix file permissions), e.g. 770, as integer
     */
    public function mkdir_recursive($pathname, $mode){
        is_dir(dirname($pathname)) || $this->mkdir_recursive(dirname($pathname), $mode);
        return is_dir($pathname) || @mkdir($pathname, $mode);
    }

    /**
     * Write a tile to be cached to the disk.
     *
     * @param url URL the tile was downloaded from
     *
     * @param data binary data of the tile
     */
    public function writeTileToCache($url, $data){
        $filename = $this->tileUrlToFilename($url);
        $this->mkdir_recursive(dirname($filename),0777);
        file_put_contents($filename, $data);
    }

    /**
     * Fetch a tile from a URL.
     *
     * @param url URL the tile should be downloaded from
     */
    public function fetchTile($url){
        if($this->useTileCache && ($cached = $this->checkTileCache($url))) return $cached;
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); 
        curl_setopt($ch, CURLOPT_USERAGENT, "staticmaps.php");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        if ($tile = curl_exec($ch))
        {
            $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            if($statusCode == 200 && $this->useTileCache){
                $this->writeTileToCache($url,$tile);
            }
        }
        else
        {
            $this->doNotWriteMapCache = 1;
        }
        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($statusCode != 200 && $statusCode != 304) {
            $this->statusCode = $statusCode;
            curl_close($ch);
            throw new Exception('Error: HTTP status code ' . $statusCode . ' returned for tile request ' . $url);
        }
        curl_close($ch); 
        return $tile;
    }

    /**
     * Add the copyright notice to the lower right corner of the image.
     *
     * @param font truetype font file to be used, has to be located in the `fonts/` subdirectory
     */
    public function copyrightNotice(){
        $attributionText = '© OpenStreetMap contributors';
        $font = $this->fontBaseDir.'/' . $this->attributionFont . '.ttf';
        $bbox = imagettfbbox(8, 0, $font, $attributionText);
        $length = abs($bbox[4] - $bbox[0]);
        $height = abs($bbox[5] - $bbox[1]);
        $black = imagecolorallocate($this->image, 0, 0, 0);
        $transparentWhite = imagecolorallocatealpha($this->image, 255, 255, 255, 60);
        imagefilledrectangle($this->image, imagesx($this->image) - $length - 2,
            imagesy($this->image) - $height - 4, imagesx($this->image), imagesy($this->image),
            $transparentWhite);
        imagettftext($this->image, 8, 0, imagesx($this->image) - $length - 1,
            imagesy($this->image) - 4, $black, $font, $attributionText);
    }

    /**
     * Send HTTP headers to the client.
     */
    public function sendHeader(){
        header('Content-Type: image/png');
        $expires = 60*60*24*14;
        header("Pragma: public");
        header("Cache-Control: maxage=".$expires);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
    }

    public function makeMap(){
        $this->initCoords();		
        $this->createBaseMap();
        if (count($this->lines)) {
            $this->placeLines();
        }
        if(count($this->markers))$this->placeMarkers();
        if($this->attribution) $this->copyrightNotice();
    }

    public function showMap(){
        $this->parseParams();
        if($this->useMapCache){
            // use map cache, so check cache for map
            if(!$this->checkMapCache()){
                // map is not in cache, needs to be built
                $this->makeMap();
                $this->sendHeader();	
                if (!$this->doNotWriteMapCache) 
                {
                    $this->mkdir_recursive(dirname($this->mapCacheIDToFilename()),0777);
                    imagepng($this->image,$this->mapCacheIDToFilename(),9);
                }
                if(file_exists($this->mapCacheIDToFilename())){
                    return file_get_contents($this->mapCacheIDToFilename());
                } else {
                    imagepng($this->image);		
                }
            } else {
                // map is in cache
                $this->sendHeader();	
                return file_get_contents($this->mapCacheIDToFilename());
            }

        } else {
        // no cache, make map, send headers and deliver png
            $this->makeMap();
            $this->sendHeader();	
            return imagepng($this->image);		

        }
    }

}

$map = new staticMapLite();
print $map->showMap();

?>
