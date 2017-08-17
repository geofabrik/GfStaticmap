<?php

function lonToTile($long, $zoom){
    return (($long + 180) / 360) * pow(2, $zoom);
}

function latToTile($lat, $zoom){
    return (1 - log(tan($lat * pi()/180) + 1 / cos($lat* pi()/180)) / pi()) /2 * pow(2, $zoom);
}

?>
