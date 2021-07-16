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

$cachedData = $cache->clearAll();

echo "<p>Execution time: ". round(microtime(true) - $start, 3) . "</p>";

?>
</body>
</html>
