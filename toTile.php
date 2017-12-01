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

?>
