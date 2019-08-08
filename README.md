# iiif_file
Simple IIIF Image API server from a directory of files using PHP


## Enviroment Setup

### Apache Setup

Two Apache modules need to be enable:

	sudo a2enmod rewrite
	sudo a2enmod headers

Rewrite rules need to be set in the site configuration or in an .htaccess file. A .htaccess file given in the git repository. If you are using .htaccess make sure it is enabled in the site configuration with something like:

	AllowOverride All
	Require all granted

You will probably need to change the RewriteBase in the .htaccess file to suit your install location.

### PHP 

PHP 7 is assumed. It may work with earlier distributions.

The script uses ImageMagick to do the image manipuation. Do something like this on Ubuntu and similar to install it:

	sudo apt-get install php-imagick







