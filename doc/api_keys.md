# Working with API keys

Some tile providers use API keys. Staticmap is able to work with them.
There are three possible use cases:

* Your tile provider does not require an API key.
* You are a "client" of the tile provider and all tile requests triggered by
users of your staticmap instance use the same API key.
* Every user of your staticmap instance uses a different API key. This is a common
use case if you offer a tile server as a service and want to provide a staticmap
API as part of your service.

## Your tile provider does not require an API key

Don't use the parameter `{P}` in the URL templates in $tileSrcUrl in config.php.
In addition, you do not need to overwrite the default implementation of the getApiKey()
method. If your users add an `apikey=<apikey>` to the query string, it will be parsed
but never used.

## All Tile Requests Use the Same API Key

Add the API key hardcoded into the URL template at the location where the tile provider
expects the API key. Don't use the template parameter `{P}`. If your users add
`apikey=<apikey>` to the query string, it will be parsed but never used.

## Every User of Staticmap Uses A Different API Key

By default, the API key is read from the `apikey` parameter of the query string.
Implement the method getApiKey() in config.php if your users should place it elsewhere.

Add the template parameter `{P}` to `$tileSrcUrl` in your config.php.
