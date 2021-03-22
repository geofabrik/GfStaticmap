<?php

require 'color.php';
require 'toTile.php';

function output_error($message, $statusCode=400) {
    http_response_code($statusCode);
    header('Content-Type: text/plain');
    $expires = 60*60*24*14;
    header("Pragma: public");
    header("Cache-Control: maxage=".$expires);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
    print($message . "\n");
    exit(0);
}

class Point {
    public $x, $y;

    public function __construct($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }

    public function x_on_map($width, $centerX, $tileSize, $zoom) {
        return floor(($width/2) - $tileSize * ($centerX - lonToTile($this->x, $zoom)));
    }

    public function y_on_map($height, $centerY, $tileSize, $zoom) {
        return floor(($height/2) - $tileSize * ($centerY - latToTile($this->y, $zoom)));
    }
}

class LineString {
    protected $points = array();
    protected $current = 0;
    public $lineColor;
    public $fillColor;
    public $width;

    public function __construct($lineColor, $width, $fillColor) {
        $this->lineColor = $lineColor;
        $this->width = $width;
        $this->fillColor = $fillColor;
    }

    public function addPoint($point) {
        array_push($this->points, $point);
    }

    public function length() {
        return count($this->points);
    }

    public function at($index) {
        return $this->points[$index];
    }

    public function isClosed() {
        return ($this->length() >= 4) && ($this->points[0]->x == end($this->points)->x) && ($this->points[0]->y == end($this->points)->y);
    }

    public function gdPointsArray($width, $height, $centerX, $centerY, $zoom, $tileSize) {
        $gdPArray = array();
        for ($i = 0; $i < count($this->points) - 1; $i++) {
            $x1 = $this->at($i)->x_on_map($width, $centerX, $tileSize, $zoom);
            $y1 = $this->at($i)->y_on_map($height, $centerY, $tileSize, $zoom);
            $gdPArray[] = $x1;
            $gdPArray[] = $y1;
        }
        return array($gdPArray, $this->length() - 1);
    }
}

function buildLineString($pointList, $lineColor, $width, $fillColor) {
    if ($pointList[0] != '(' || $pointList[strlen($pointList) - 1] != ')') {
        output_error('Point list is invalid. Its first character must be an opening round, the last one must be a closing round bracket.');
    }
    // The use of strlen($pointList) - 2 is inteded.
    $points = preg_split('/\)\(/', substr($pointList, 1, strlen($pointList) - 2));
    if (count($points) < 2) {
        output_error('A line must contain at least two points.');
    }
    $linestring = new LineString($lineColor, $width, $fillColor);
    foreach ($points as $point) {
        list($lon, $lat) = explode(' ', $point, 2);
        if (is_numeric($lon) && is_numeric($lat)) {
            $linestring->addPoint(new Point($lon, $lat));
        } else {
            output_error('Could not parse point list. It contains a point coordinate which is not a number.');
        }
    }
    return $linestring;
}

class Arc {
    public $center;
    public $radius;
    public $start = 0;
    public $end = 360;
    public $lineWidth = 3;
    public $fillColor;
    public $lineColor;

    public function __construct($center, $radius, $width, $lineColor, $fillColor, $start=null, $end=null) {
        $this->lineWidth = $width;
        $this->center = $center;
        $this->start = $start;
        $this->end = $end;
        $this->radius = $radius;
        $this->lineColor = $lineColor;
        $this->fillColor = $fillColor;
    }

    public function isCircle() {
        return $this->start === 0 && $this->end === 360;
    }

    /**
     * Return radius in pixel.
     */
    public function radiusInPixel($mapCenterLat, $zoomLevel, $tileSize) {
        return $this->radius * pixelPerMeter($mapCenterLat, $zoomLevel, $tileSize);
    }

    /**
     * Get an approximation of the arc as a polygon.
     *
     * Returns an array containing the x and y coordinates of the polygons vertices consecutively.
     */
    public function getArcPoints($mapCenterX, $mapCenterY, $mapWidth, $mapHeight, $zoom, $tileSize) {
        // Radius intentionally 2 pixel smaller to avoid that the fill appears beyond the outline as well.
        $radiusPx = $this->radiusInPixel($this->center->y, $zoom, $tileSize) - 2;
        $centerPxX = $this->center->x_on_map($mapWidth, $mapCenterX, $tileSize, $zoom);
        $centerPxY = $this->center->y_on_map($mapHeight, $mapCenterY, $tileSize, $zoom);
        // It is easier to work with counterclockwise angles here.
        $ang1 = 360 - $this->start;
        $ang2 = $ang1 - ($this->end - $this->start);
        $points = array($centerPxX, $centerPxY);
        for ($i = $ang2; $i <= $ang1; $i = $i + 0.5) {
            array_push(
                $points,
                $centerPxX + cos(deg2rad($i)) * $radiusPx,
                $centerPxY - sin(deg2rad($i)) * $radiusPx
            );
        }
        return $points;
    }
}
?>
