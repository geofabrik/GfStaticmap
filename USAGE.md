# API Documentation for Static Map Service

## API Parameters

### Mandatory Parameters

* `center`: `<latitude>,<longitude>`, center of the map image in geographical coordinates, e.g. `40.714728,-73.998672`
* `zoom`: `<zoom level>`, zoom level (`0` to `18`), integer
* `size`: `<width>x<height>`, width and height in pixel, both integer
* `maptype`: `default`

All other parameters are optional and control overlays and attribution

### Markers (Optional)

* `markers`: comma-separated list of key-value pairs. Keys and values are separated by colons (`:`). Multiple markers are separated by bars (`|`, in URLs encoded as `%7C`).

Following keys are available:

* `lat`: latitude (float), mandatory
* `lon`: longitude (float), mandatory
* `image`: image to be used (string), mandatory
* `color`: color of the icon as RGB HEX string with 6 digits and no leading # character (default: FF0000)
* `fontcolor`: color of the font as RGB HEX string with 6 digits and no leading # character (default: 000000)

Markers are drawn and numbered in the order they occur in this list. Each marker gets its number inside the pin.

Following marker icons are available:

* `pin`: a pin
* `marker`: marker icon as used by the Leaflet JavaScript library
