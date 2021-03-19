# API Documentation for Static Map Service

## API Parameters

### Parameters for image size, location and zoom level

Mandatory parameter:

* `size`: `<width>x<height>`, width and height in pixel, both integer

The following parameters are mandatory if neither `path` nor `markers` is provided. If `center` and
`zoom` are not provided, GfStaticMap will use the largest possible zoom level (but not larger than 19) which makes
all content fit onto the map.

* `center`: `<latitude>,<longitude>`, center of the map image in geographical coordinates, e.g. `40.714728,-73.998672`
* `zoom`: `<zoom level>`, zoom level (`0` to `18`), integer

### Optional Parameters Controlling the Overall Appearance of the Map

* `maptype`: string. If the server supports offers high-resolution tiles, `print` and `print150` might be added. Default value: `default`
* `attribution`: boolean. Use `false` if you do not want a attribution text being included in the lower right corner. Default value: `true`
* `attribution-font`: string. Font to be used for attribution. Default value: `NotoSansUI-Regular`

All other parameters control overlays and attribution

### Markers (Optional)

* `markers`: pipe-separated list of markers. Each marker is a comma-separated key-value store. Keys and values are separated by colons (`:`):

```
&markers=<marker1>|<marker2>|<marker3
```

Each marker is a key-value list:

```
&markers=lat:<latitude>,lon:<longitude>,image:<image>|lat:<latitude>,lon:<longitude>,image:<image>
```

Following keys are available:

* `lat`: latitude (float), mandatory
* `lon`: longitude (float), mandatory
* `image`: image to be used (string), mandatory
* `color`: color of the icon as RGB HEX string with 6 or 8 digits and no leading # character (default: FF0000FF). Transparency is not supported, marker fill color is always opaque.
* `fontcolor`: color of the font as RGB HEX string with 6 or 8 digits and no leading # character (default: 000000FF)
* `label`: a single character which will be used as label. If this string is empty, the marker will be placed but not labelled. If this parameter is not set, it will be numbered automatically. There must not be more than nine markers which are labelled automatically.
* `font`: font to be used. See list of available fonts below.

Markers are drawn and numbered in the order they occur in this list. Each marker gets its number inside the pin.

Following marker icons are available:

* `pin`: a pin
* `marker`: marker icon as used by the Leaflet JavaScript library
* `ol-marker`: marker icon as used by the OpenLayers 2 JavaScript library, text will be placed outside the icon (and maybe outside the image)

#### Legacy marker syntax

**This syntax is legacy. Please migrate to the new syntax which is more flexible and has more features.**

The old marker syntax is still supported. It is a pipe-separated list of markers but each marker definition is a comma-separated list. The
order of the elements in that list is `lat`, `lon`, `image`.

`&markers=<latitude1>,<longitude1>,<image1>,<label1>|<latitude2>,<longitude2>,<image2>,<label2>` could be rewritten as
`&markers=lat:<latitude1>,lon:<longitude1>,image:<image1>,label:<label1>|lat:<latitude2>,lon:<longitude2>,image:<image2>,label:<label2>`.

The comma-separated list must contain at least three elements, the forth element (the label) is optional. Any further elements are silently ignored.


### Lines (optional)

* `path`: a pipe-separated list of paths. Each marker is a comma-separated key-value store. Keys and values are separated by colons (`:`):

```
&path=<path1>|<path2>|<path3>
```

Each path is a key-value list:

```
&path=points:(<lon1> <lat1>)(<lon2> <lat2>)(<lon3><lat3>),color:<color>|points:(<lon1> <lat1>)(<lon2> <lat2),color:<color>
```

Following keys are available:

* `points`: list of coordinate pairs of the pattern `(<longitude1> <latitude1>)(<longitude2> <latitude2>)(<longitude3> <latitude3>)`. Longitude and latitude of a point are separated by a space. A point is surrounded by round brackets. If you want to draw a closed ring, the first point must be the same as the last point.
* `width`: width of the line. Widths larger than 3 might look bad due to a bug in the underlying graphics software library.
* `color`: color of the outline as RGB HEX string with 6 or 8 digits and no leading # character
* `fillcolor`: fill color as RGB HEX string with 6 or 8 digits and no leading # character


### Circles (optional)

* `circle`: a pipe-separated list of circles. Each circle is a comma-separated key-value store. Keys and values are separated by colons (`:`):

```
&circle=<circle1>|<circle2>|<circle3>
```

Each circle is a key-value list:

```
&circle=center:(<lon1> <lat1>),color:<color>,radius:<radius>,fillcolor:<fillcolor>,width:<width>|center:(<lon1> <lat1>),color:<color>,fillcolor:<fillcolor>,radius:<radius>
```

Following keys are available:

* `center`: a point `(<longitude1> <latitude1>)`. Longitude and latitude of a point are separated by a space. A point is surrounded by round brackets. This key is mandatory.
* `radius`: radius in meter. This key is mandatory.
* `width`: width of the line. Widths larger than 3 might look bad due to a bug in the underlying graphics software library.
* `color`: color of the outline as RGB HEX string with 6 or 8 digits and no leading # character
* `fillcolor`: fill color as RGB HEX string with 6 or 8 digits and no leading # character

### Pies (optional)

* `pie`: a pipe-separated list of pies. Each pie is a comma-separated key-value store. Keys and values are separated by colons (`:`):

```
&pie=<pie1>|<pie2>|<pie3>
```

Each pie is a key-value list:

```
&pie=center:(<lon1> <lat1>),color:<color>,radius:<radius>,from:<from>,to:<to>,fillcolor:<fillcolor>,width:<width>|center:(<lon1> <lat1>),color:<color>,fillcolor:<fillcolor>,from:<from>,to:<to>,radius:<radius>
```

You can use the same keys as for circle. In addition, the following keys are mandatory:

* `from`: start angle in degree, 0° is located at the three-o'clock position, and the arc is drawn clockwise.
* `to`: end angle in degree.

## Examples

Map of Manhattan Downtown, two markers automatically labeled, filled polygon:

```
http://staticmap.hatano.geofabrik.de/test_osm?center=40.714728,-73.998672&zoom=14&size=512x512&maptype=default&markers=lon:-74.015794,lat:40.702147,image:marker,fontcolor:00000033,color:FFFF0033|lon:-74.015794,lat:40.709147,image:pin,fontcolor:0000FF,color:FF00FF&path=points:(-73.998672%2040.702147)(-74.015794%2040.702147)(-74.0117%2040.712147)(-73.998672%2040.702147),color:FF000033,fillcolor:00FF0033
```

The same but with manually labeled markers:

```
http://staticmap.hatano.geofabrik.de/test_osm?center=40.714728,-73.998672&zoom=14&size=512x512&maptype=default&markers=label:A,lon:-74.015794,lat:40.702147,image:marker,fontcolor:00000033,color:FFFF0033|lon:-74.015794,lat:40.709147,label:Z,image:pin,fontcolor:0000FF,color:FF00FF&path=points:(-73.998672%2040.702147)(-74.015794%2040.702147)(-74.0117%2040.712147)(-73.998672%2040.702147),color:FF000033,fillcolor:00FF0033
```

## List of Fonts

Following fonts are installed with Staticmap by default:

* NotoSansUI-Regular
* NotoSansUI-Bold
* LiberationSans-Regular
* LiberationSans-Bold

Additional TrueType fonts can be installed on request.
