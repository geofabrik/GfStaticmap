# Dependencies

## Ubuntu/Debian

```sh
apt install php php-gd php-curl
```

* Clone code to /var/www/html/staticmap/
* Create directory /var/www/html/staticmap/cache/maps/
* Grant write permissions to www-data for /var/www/html/staticmap/cache/maps/

Add to your Apache VirtualHost config:

```Apache
        ScriptAlias / /srv/staticmap/staticmap.php/
        <Location />
                Require all granted
        </Location>
```
