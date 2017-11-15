# Dependencies

## Ubuntu/Debian

```sh
apt install php php-gd php-curl
```

The following instructions assume that Staticmap is installed at `/srv/staticmap/`.

* Clone code to /srv/staticmap/staticmap/
* Create directory /srv/staticmap/cache/maps/
* Grant write permissions to the user running Apache (usually `www-data`) for
  /srv/staticmap/cache/maps/

Clone Git repository:

```sh
cd /srv/
git clone https://github.com/geofabrik/staticmap.git
```

Add the following lines to your Apache VirtualHost config:

```Apache
        ScriptAlias / /srv/staticmap/staticmap.php/
        <Location />
                Require all granted
        </Location>
```

Create a directory `/srv/staticmap/cache/maps/` and grant write access to this
directory to the user running Apache (`www-data`):

```sh
mkdir -p /srv/staticmap/cache/maps/
chown -R www-data cache
```


# Fonts

Further fonts can be installed by placing their .ttf files into the `fonts/` subdirectory.
The user running Apache must have read permissions for these files.


# Configuration

Create a copy of `config.php.sample` called `config.php` and do your configuration there.
Details about the configuration are explained as comments in the file.
