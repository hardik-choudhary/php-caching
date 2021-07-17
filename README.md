# PHP Cache Class
 A light, simple and powerful PHP Cache Class that uses the filesystem for caching.
 Your feedback is always welcome.

## Requirements

- PHP 7.4.0 or higher (**cache.class.php**)
- PHP 5.1.6 or higher (**cache.class-old.php**)

## Introduction
Basically, the caching class stores its data in files in text format. Files will be created for each cache you will create, it will use less memory (RAM) in comparison to using a single file for multiple caches.

## Quick Start ##

#### Setup Cache class:

First include the Cache class:

```php
<?php
    require_once 'cache.class.php';
    // require_once 'cache.class-old.php'; // for older versions of php
    
    // Create an instance; 
    $cache = new Cache('cache'); // We have to pass cache directory (folder) path
?>
```

#### Create cache:
`$classInstance->write(string $cacheName, string $content)` <br />
Params
- **$cacheName** (Required): Any string that will be used to access the cache in future
- **$content** (Optional): Content (as string);

```php
<?php
    $cache->write('cache-name', 'This is the content');
?>
```

#### Get cached data:
`$classInstance->read(string $cacheName, int $maxAge = 0, bool $deleteExpired = TRUE)` <br />
Params
- **$cacheName** (Required): String that was used while creating cache
- **$maxAge** (Optional): Seconds; Return NULL if file older then these seconds. Default: 0, No limit
- **$deleteExpired** (Optional): TRUE OR FALSE; Delete cache if file age is more then maxAge. Default: TRUE

```php
<?php
    $cache->read('cache-name', 200, TRUE);
?>
```

#### Cache Subfolder:
The cache files will be stored in a subfolder in the cache directory <br />
`$classInstance->setSubFolder(string $subFolder)` <br />
Params
- **$subFolder** (Required): subfolder name

```php
<?php
    $cache->setSubFolder('ip-files');
	$cache->write('134.201.250.155', '{"type": "ipv4", "continent": "NA", "country": "US", "region": "CA", }');
    $ipdate = $cache->read('134.201.250.155', 200, TRUE);
?>
```

#### Delete Expired Cache files:
`$classInstance->clear(int $maxAge = 0)`  <br />
Params
- **$maxAge** (Optional): Seconds; Return NULL if file older then these seconds. Default: 0, delete all

```php
<?php
    $cache->clear(200);
?>
```

#### Delete all Cache files:
`$classInstance->clearAll()` <br />
It will delete every thing from cache directory

```php
<?php
    $cache->clearAll();
?>
```

## Example:
Please check test folder for examples
```php
require_once("cache.class.php");

$cache = new Cache('cache');

$page = "home.php";
$cacheMaxAge = 86400; // One Day
$cachedData = $cache->read($page, $cacheMaxAge);

if($cachedData != NULL){
    echo $cachedData;
	die;
}
else{
    ob_start();
    include($page);

    $page_content = ob_get_contents();

    $cache->write($page, $page_content);

    ob_end_flush();
}
```

Thanks;
