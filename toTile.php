<?php

/**
 * Convert a longitude (degrees) to tile ID (floating comma) on a given zoom level)
 *
 * @param long longitude
 *
 * @param zoom zoom level
 *
 * @return tile X index (floating point)
 */
function lonToTile($long, $zoom){
    return (($long + 180) / 360) * pow(2, $zoom);
}

/**
 * Convert a latitude (degrees) to tile ID (floating comma) on a given zoom level)
 *
 * @param long latitude
 *
 * @param zoom zoom level
 *
 * @return tile Y index (floating point)
 */
function latToTile($lat, $zoom){
    return (1 - log(tan($lat * pi()/180) + 1 / cos($lat* pi()/180)) / pi()) /2 * pow(2, $zoom);
}

/**
 * Return Mercator scale factor
 */
function mercatorFactor($lat) {
    $latRad = ($lat / 180.0) * M_PI;
    return 1.0 / cos($latRad);
}

/**
 * Get map scale in pixel per meter
 */
function pixelPerMeter($lat, $zoom, $tileSize) {
    $earth_radius = 6378137.0;
    // tile width at equator
    $eqTileWidth = ($earth_radius * 2 * pi()) / pow(2, $zoom);
    // scale at equator
    $eqPixelWidth = $eqTileWidth / 256.0;
    return 1 / ($eqPixelWidth / mercatorFactor($lat));
}

?>
