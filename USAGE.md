# API Documentation for Static Map Service

## API Parameters

### Mandatory Parameters

* `center`: `<latitude>,<longitude>`, center of the map image in geographical coordinates, e.g. `40.714728,-73.998672`
* `zoom`: `<zoom level>`, zoom level (`0` to `18`), integer
* `size`: `<width>x<height>`, width and height in pixel, both integer
* `maptype`: `default`

All other parameters are optional and control overlays and attribution

### Markers (Optional)

* `markers`: pipe-separated list of markers. Each marker is a comma-separated key-value store. Keys and values are separated by colons (`:`):

```
&markers=<marker1>|<marker2>|<marker3
```

Each marker is a key-value list:

```
&marker=lat:<latitude>,lon:<longitude>,image:<image>|lat:<latitude>,lon:<longitude>,image:<image>
```

Following keys are available:

* `lat`: latitude (float), mandatory
* `lon`: longitude (float), mandatory
* `image`: image to be used (string), mandatory
* `color`: color of the icon as RGB HEX string with 6 or 8 digits and no leading # character (default: FF0000FF)
* `fontcolor`: color of the font as RGB HEX string with 6 or 8 digits and no leading # character (default: 000000FF)
* `label`: a single character which will be used as label. If this string is empty, the marker will be placed but not labelled. If this parameter is not set, it will be numbered automatically. There must not be more than nine markers which are labelled automatically.

Markers are drawn and numbered in the order they occur in this list. Each marker gets its number inside the pin.

Following marker icons are available:

* `pin`: a pin
* `marker`: marker icon as used by the Leaflet JavaScript library

### Lines (optional)

* `paths`: a pipe-separated list of paths. Each marker is a comma-separated key-value store. Keys and values are separated by colons (`:`):

```
&paths=<path1>|<path2>|<path3
```

Each path is a key-value list:

```
&paths=points:(<lon1> <lat1>)(<lon2> <lat2>)(<lon3><lat3>),color:<color>|points:(<lon1> <lat1>)(<lon2> <lat2),color:<color>
```

Following keys are available:

* `points`: list of coordinate pairs of the pattern `(<longitude1> <latitude1>)(<longitude2> <latitude2>)(<longitude3> <latitude3>)`. Longitude and latitude of a point are separated by a space. A point is surrounded by round brackets. If you want to draw a closed ring, the first point must be the same as the last point.
* `width`: width of the line. Widths larger than 3 might look bad due to a bug in the underlying graphics software library.
* `color`: color of the outline as RGB HEX string with 6 or 8 digits and no leading # character
* `fillcolor`: fill color as RGB HEX string with 6 or 8 digits and no leading # character
