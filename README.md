<!-- docbloc -->
<span id='docbloc'>
php - intrd common functions
<table>
<tr>
<th>Package</th>
<td>intrd/php-common</td>
</tr>
<tr>
<th>Version</th>
<td>1.0</td>
</tr>
<tr>
<th>Tags</th>
<td>php, intrd, common</td>
</tr>
<tr>
<th>Project URL</th>
<td>https://github.com/intrd/php-common</td>
</tr>
<tr>
<th>Author</th>
<td>intrd - http://dann.com.br</td>
<tr>
<th>Copyright</th>
<td>(CC-BY-SA-4.0) 2016, intrd</td>
</tr>
<tr>
<th>License</th>
<td><a href='http://creativecommons.org/licenses/by-sa/4.0'>Creative Commons Attribution-ShareAlike 4.0</a></td>
</tr>
<tr>
<th>Dependencies</th>
<td> &#8226; php >=5.3.0</td>
</tr>
</table>
</span>
<!-- @docbloc 1.1 -->

## System installation
```
$ sudo apt-get update & apt-get upgrade
$ sudo apt-get install curl git php5-curl php5-cli
$ curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

Now download the package (Composer automatically install all dependencies)
$ git clone https://github.com/intrd/php-common && cd php-common
$ composer install -o

To check for update..
$ git pull && composer update
```

## Usage sample

Assuming your project are running over `Composer`, simply Require it on your `composer.json`
```
"require": {
    "intrd/php-common": ">=1.0.x-dev <dev-master"
}
```
Now Composer PSR-4 Autoload will instance this class and you are able to use by this way..

```
require __DIR__ . '/vendor/autoload.php';
use php\intrdCommons as i;

$test="works!";
i::vd($test);
```