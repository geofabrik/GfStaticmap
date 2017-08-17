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

Create a directory `cache/maps` in the directory where staticmap.php is located.
Grant write access to this directory to the user running Apache (`www-data`):

```sh
mkdir -p cache/maps/
chown -R www-data cache
```



# Fonts

Further fonts can be installed by placing their .ttf files into the `fonts/` subdirectory.
The user running Apache must have read permissions for these files.
