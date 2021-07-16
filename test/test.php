<!DOCTYPE html>
<html>
<head>
    <title>Page Title</title>
</head>
<body>

<?php
$start = microtime(true);
require_once("../cache.class.php");

$cache = new Cache('cache');

$page = "home.php";
$cacheMaxAge = 86400; // One Day
$cachedData = $cache->read($page, $cacheMaxAge);

if($cachedData != NULL){
    echo $cachedData;
    // die; // Commented to measure execution time
}
else{
    ob_start();
    include($page);

    $page_content = ob_get_contents();

    $cache->write($page, $page_content);

    ob_end_flush();
}

echo "<p>Execution time: ". round(microtime(true) - $start, 3) . "</p>";

?>
</body>
</html>
