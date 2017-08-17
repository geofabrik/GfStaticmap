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

Class staticMapLite extends myStaticMap {

    protected $zoom, $lat, $lon, $width, $height, $markers, $lines, $image, $maptype;
    protected $centerX, $centerY, $offsetX, $offsetY;

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

    public function parseParams(){
        global $_GET;

        // get zoom from GET paramter
        $this->zoom = $_GET['zoom']?intval($_GET['zoom']):0;
        if($this->zoom>18)$this->zoom = 18;

        // get lat and lon from GET paramter
        list($this->lat,$this->lon) = explode(',',$_GET['center']);
        $this->lat = floatval($this->lat);
        $this->lon = floatval($this->lon);

        // get zoom from GET paramter
        if($_GET['size']){
            list($this->width, $this->height) = explode('x',$_GET['size']);
            $this->width = intval($this->width);
            $this->height = intval($this->height);
        }
        if(isset($_GET['markers'])){
            $markerCount = 1;
            // split up into markers
            $markers = preg_split('/%7C|\|/',$_GET['markers']);
            foreach($markers as $marker){
                // split up by , into key:value pairs
                $kvPairs = explode(',', $marker);
                // build array of keys and values
                $params = array();
                foreach($kvPairs as $pair) {
                    list($key, $value) = explode(':', $pair, 2);
                    $params[trim($key)] = trim($value);
                }
                if (isset($params['lat']) && isset($params['lon']) && isset($params['image'])) {
                    $markerLat = floatval($params['lat']);
                    $markerLon = floatval($params['lon']);
                    $markerImage = basename($params['image']);
                    $markerColor = new Color(255, 0, 0, 255);
                    if (isset($params['color'])) {
                        $markerColor = Color::colorFromHex($params['color']);
                    }
                    $fontColor = new Color(0, 0, 0, 255);
                    if (isset($params['fontcolor'])) {
                        $fontColor = Color::colorFromHex($params['fontcolor']);
                    }
                    $markerLabel = (string)$markerCount;
                    if (isset($params['label'])) {
                        if (strlen($params['label']) > 1) {
                            output_error('Labels of markers must be zero or one character long.');
                        }
                        $markerLabel = $params['label'];
                    } else if ($markerCount > 9) {
                        output_error('More than 9 unlabelled markers.');
                    }
                    if (isset($params['font'])) {
                        $font = $params['font'];
                        if (!is_readable($this->fontBaseDir . $font . '.ttf')) {
                            output_error('Font ' . $font . ' is not available. Please provide a font which accessiable for the staticmap API.');
                        }
                        $this->markers[] = new Marker($markerLat, $markerLon, $markerImage, $markerColor, $fontColor, $markerLabel, $font);
                    } else {
                        $this->markers[] = new Marker($markerLat, $markerLon, $markerImage, $markerColor, $fontColor, $markerLabel);
                    }
                    $markerCount++;
                } else {
                    output_error('One of the mandatory marker arguments is missing: lat, lon, image');
                }
            }
        }
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
                $this->lines[] = buildLineString($params['points'], $lineColor, $lineWidth, $fillColor);
            }
        }
        if($_GET['maptype']){
            if(array_key_exists($_GET['maptype'],$this->tileSrcUrl)) $this->maptype = $_GET['maptype'];
            if ($_GET['maptype'] == 'print') $this->tileSize = 1024;
            if ($_GET['maptype'] == 'print150') $this->tileSize = 512;
        }
        if(isset($_GET['nocache'])){
            $this->doNotReadMapCache = true;
        }
        if(isset($_GET['attribution'])) {
           if ($_GET['attribution'] == 'false'){
               $this->attribution = false;
           } else if ($_GET['attribution'] != 'true') {
               output_error('Illegal option for parameter \'attribution\'');
           }
        }
        if (isset($_GET['attribution-font'])) {
            $this->attributionFont = $_GET['attribution-font'];
            if (strlen($this->attributionFont) == 0) {
                output_error('Missing font name for attribution text.');
            }
            if (!is_readable($this->fontBaseDir . $this->attributionFont . '.ttf')) {
                output_error('Font ' . $font . ' is not available. Please provide a font which accessiable for the staticmap API.');
            }
        }
    }


    public function initCoords(){
        $this->centerX = lonToTile($this->lon, ($this->zoom));
        $this->centerY = latToTile($this->lat, ($this->zoom));
        $this->offsetX = floor((floor($this->centerX)-$this->centerX)*$this->tileSize);
        $this->offsetY = floor((floor($this->centerY)-$this->centerY)*$this->tileSize);
    }

    public function createBaseMap(){
        $this->image = imagecreatetruecolor($this->width, $this->height);
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

        for($x=$startX; $x<=$endX; $x++){
            for($y=$startY; $y<=$endY; $y++){
                $url = str_replace(array('{P}', '{Z}','{X}','{Y}'),array($_SERVER['PATH_INFO'],$this->zoom, $x, $y), $this->tileSrcUrl[$this->maptype]);
                $tileImage = imagecreatefromstring($this->fetchTile($url));
                $destX = ($x-$startX)*$this->tileSize+$this->offsetX;
                $destY = ($y-$startY)*$this->tileSize+$this->offsetY;
                imagecopy($this->image, $tileImage, $destX, $destY, 0, 0, $this->tileSize, $this->tileSize);
            }
        }
    }

    protected function addMarkerOrMask($markerFilename, $markerLookupResult, $colorize, $marker) {
        if (!file_exists($markerFilename)) return;
        $markerImg = imagecreatefrompng($markerFilename);
        $destX = floor(($this->width/2)-$this->tileSize*($this->centerX - lonToTile($marker->lon, $this->zoom)));
        $destY = floor(($this->height/2)-$this->tileSize*($this->centerY - latToTile($marker->lat, $this->zoom)));
        $destY = $destY - $markerLookupResult['hoty'];
        $destX = $destX - $markerLookupResult['hotx'];
        if ($colorize) {
            imagefilter($markerImg, IMG_FILTER_COLORIZE, $marker->color->red, $marker->color->green, $marker->color->blue);
        }
        imagecopy($this->image, $markerImg, $destX, $destY, 0, 0, imagesx($markerImg), imagesy($markerImg));
        return array($destX, $destY);
    }

    public function placeMarkers() {
        foreach($this->markers as $marker){
            $markerLat = $marker->lat;
            $markerLon = $marker->lon;
            $markerImage = $marker->image;
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
            imagettftext($this->image, $mlu['textsize'], 0, $destX + $mlu['textx'] - $width/2, $destY + $mlu['texty'], $fontColor, $font, $marker->label);
        };
    }

    public function placeLines() {
        foreach($this->lines as $line) {
            // build array of points transformed to pixel coordinates
            list($gdPArray, $numPoints) = $line->gdPointsArray($this->width, $this->height, $this->centerX, $this->centerY, $this->zoom, $this->tileSize);

            if (!($line->fillColor->isTransparent()) && $line->isClosed()) {
                imagefilledpolygon($this->image, $gdPArray, $numPoints, $line->fillColor->allocate($this->image));
            }
            for ($i = 0; $i < $line->length() - 1; $i++) {
                imagesetthickness($this->image, $line->width);
                imageline($this->image, $gdPArray[$i * 2], $gdPArray[$i * 2 + 1], $gdPArray[$i * 2 + 2], $gdPArray[$i * 2 + 3], $line->lineColor->allocate($this->image));
            }
        }
    }


    public function tileUrlToFilename($url){
        return $this->tileCacheBaseDir."/".str_replace(array('http://'),'',$url);
    }

    public function checkTileCache($url){
        $filename = $this->tileUrlToFilename($url);
        if(file_exists($filename)){
            return file_get_contents($filename);
        }
    }

    public function checkMapCache(){
        if ($this->doNotReadMapCache) return false;
        $this->mapCacheID = md5($this->serializeParams());
        $filename = $this->mapCacheIDToFilename();
        if(file_exists($filename)) return true;
        return false;
    }

    public function serializeParams(){		
        return join("&",array($this->zoom,$this->lat,$this->lon,$this->width,$this->height, serialize($this->markers),$this->maptype));
    }

    public function mapCacheIDToFilename(){
        if(!$this->mapCacheFile){
            $this->mapCacheFile = $this->mapCacheBaseDir."/".substr($this->mapCacheID,0,2)."/".substr($this->mapCacheID,2,2)."/".substr($this->mapCacheID,4);
        }
        return $this->mapCacheFile.".".$this->mapCacheExtension;
    }



    public function mkdir_recursive($pathname, $mode){
        is_dir(dirname($pathname)) || $this->mkdir_recursive(dirname($pathname), $mode);
        return is_dir($pathname) || @mkdir($pathname, $mode);
    }
    public function writeTileToCache($url, $data){
        $filename = $this->tileUrlToFilename($url);
        $this->mkdir_recursive(dirname($filename),0777);
        file_put_contents($filename, $data);
    }

    public function fetchTile($url){
        if($this->useTileCache && ($cached = $this->checkTileCache($url))) return $cached;
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); 
        curl_setopt($ch, CURLOPT_USERAGENT, "staticmaps.php");
        curl_setopt($ch, CURLOPT_URL, $url); 
        if ($tile = curl_exec($ch))
        {
            if($this->useTileCache){
                $this->writeTileToCache($url,$tile);
            }
        }
        else
        {
            $this->doNotWriteMapCache = 1;
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
        imagefilledrectangle($this->image, imagesx($this->image) - $length - 2, imagesy($this->image) - $height - 4, imagesx($this->image), imagesy($this->image), $transparentWhite);
        imagettftext($this->image, 8, 0, imagesx($this->image) - $length - 1, imagesy($this->image) - 4, $black, $font, $attributionText);
    }

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
