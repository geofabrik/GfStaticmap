<?php

require 'color.php';
require 'toTile.php';

function output_error($message) {
    http_response_code(400);
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
        for ($i = 0; $i < count($this->points); $i++) {
            $x1 = floor(($width/2) - $tileSize * ($centerX - lonToTile($this->at($i)->x, $zoom)));
            $y1 = floor(($height/2) - $tileSize * ($centerY - latToTile($this->at($i)->y, $zoom)));
            $gdPArray[] = $x1;
            $gdPArray[] = $y1;
        }
        return array($gdPArray, $this->length());
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
?>
