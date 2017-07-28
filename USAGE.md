# API Documentation for Static Map Service

## API Parameters

### Mandatory Parameters

* `center`: `<latitude>,<longitude>`, center of the map image in geographical coordinates, e.g. `40.714728,-73.998672`
* `zoom`: `<zoom level>`, zoom level (`0` to `18`), integer
* `size`: `<width>x<height>`, width and height in pixel, both integer
* `maptype`: `default`

All other parameters are optional and control overlays and attribution

### Markers (Optional)

* `markers`: `{latitude},{longitude},{marker icon}|{latitude},{longitude},{marker icon}`, a list of markers separated by `|` (in URLs encoded as `%7C`. Latitude and longitude are float numbers. Marker icon is a string. See below for the list of available marker icons.

Markers are drawn and numbered in the order they occur in this list. Each marker gets its number inside the pin.

Following marker icons are available:

* `redpin`: a red pin
