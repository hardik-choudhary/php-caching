<?php
/*
 * Project      : PHP Cache Class
 * Version      : 1.1
 * Author       : Hardik Choudhary (ThinTake)
 * Author URL   : https://thintake.in
 * Class URL    : https://github.com/hardik-choudhary/php-caching
 * License      : GNU GPLv3
 * 
 * This class works on PHP 7.4.0 or higher (Minimum PHP version 7.4 is required)
 */


/**
 * Class Cache
 */
class Cache
{
    /**
     * Directory where cache files will be stored
     * @var string|null
     */
    public ?string $cacheDirectory = NULL;

    /**
     * Sub folder in cacheDirectory optional
     * @var string|null
     */
    public ?string $subFolder = NULL;

    /**
     * Cache constructor.
     * @param string $cacheDirectory
     */
    public function __construct(string $cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
        $this->createDirectory($this->cacheDirectory);
        $this->createDefaultFiles($this->cacheDirectory);
    }

    /**
     * Set sub folder in cache directory. Like: {cacheDirectory}/{subFolder}, "cache/pages-cache"
     * @param string $subFolder
     */
    public function setSubFolder(string $subFolder): void
    {
        $this->subFolder = $subFolder;

        $this->createDirectory($this->cacheDirectory . DIRECTORY_SEPARATOR . $this->subFolder);
        // $this->createDefaultFiles($this->cacheDirectory . DIRECTORY_SEPARATOR . $this->subFolder);
    }

    /**
     * Get cache file
     * @param string $cacheName String that was used while creating cache
     * @param int $maxAge (in Seconds). Return NULL if file older then these seconds. Default: 0, No limit
     * @param bool $deleteExpired Delete cache if file age is more then maxAge. Default: TRUE
     * @return string|null Return null if file expired or doesn't exist
     */
    public function read(string $cacheName, int $maxAge = 0, bool $deleteExpired = TRUE): ?string
    {
        $cacheFile = $this->getCachePath($cacheName);
        if($this->checkCache($cacheName, $maxAge, $deleteExpired)){
            return file_get_contents($cacheFile);
        }
        return NULL;
    }


    /**
     * Check is cache exist or not
     *
     * @param string $cacheName String that was used while creating cache.
     * @param int $maxAge (in Seconds). Return NULL if file older then these seconds. Default: 0, No limit
     * @param bool $deleteExpired Delete cache if file age is more then maxAge. Default: TRUE
     * @return bool
     */
    public function checkCache(string $cacheName, int $maxAge = 0, bool $deleteExpired = TRUE) :bool
    {
        $cacheFile = $this->getCachePath($cacheName);
        if (file_exists($cacheFile)) {
            if($maxAge == 0 || (time() - filemtime($cacheFile)) <= $maxAge){
                return TRUE;
            }
            elseif($deleteExpired){
                $this->delete($cacheName);
            }
        }
        return FALSE;
    }

    /**
     * Create new cache file
     * @param string $cacheName Any string that will be used to access the cache in future
     * @param string $content Content
     */
    public function write(string $cacheName, string $content) :void
    {
        $cacheFile  = $this->getCachePath($cacheName);
        file_put_contents($cacheFile, $content);
    }

    /**
     * Create directory if doesn't exists
     * @param string $directory
     */
    private function createDirectory(string $directory) :void
    {
        if (!file_exists($directory)) {
            $oldmask = umask(0);
            @mkdir($directory, 0777, true);
            @umask($oldmask);
        }
    }

    /**
     * Create .htaccess and index.html file. (To deny direct access to cache files)
     * @param string $directory
     */
    private function createDefaultFiles(string $directory) :void
    {
        if (!file_exists($directory . DIRECTORY_SEPARATOR . ".htaccess")) {
            $f = @fopen($directory . DIRECTORY_SEPARATOR . ".htaccess", "a+");
            @fwrite($f, "deny from all");
            @fclose($f);
        }
        if (!file_exists($directory . DIRECTORY_SEPARATOR . "index.html")) {
            $f = @fopen($directory . DIRECTORY_SEPARATOR . "index.html", "a+");
            @fclose($f);
        }
    }

    /**
     * Get full path of cache file
     * @param string $cacheName String that was used while creating cache
     * @return string
     */
    public function getCachePath(string $cacheName) :string
    {
        return $this->getCacheDir() . DIRECTORY_SEPARATOR . hash('sha1', $cacheName) .".cache";
    }

    /**
     * Get current cache directory with selected
     * @return string
     */
    public function getCacheDir(): string
    {
        return ($this->subFolder != NULL)? $this->cacheDirectory . DIRECTORY_SEPARATOR . $this->subFolder: $this->cacheDirectory;
    }

    /**
     * Delete cache single file
     * @param string $cacheName
     */
    public function delete(string $cacheName) :void
    {
        @unlink($this->getCachePath($cacheName));
    }

    /**
     * Clear specific cache.
     * @param int $maxAge (in Seconds). Delete all files older then these seconds. Default: 0, Clear All Files
     */
    public function clear(int $maxAge = 0) :void
    {
        $cacheDir = $this->getCacheDir();
        foreach (array_diff(scandir($cacheDir), array('.', '..', '.htaccess', 'index.html')) as $file){
            $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . $file;
            if(is_file($cacheFile) && ($maxAge == 0 || (time() - filemtime($cacheFile)) >= $maxAge)){
                unlink($cacheFile);
            }
        }
    }

    /**
     * Clear all cache files
     */
    public function clearAll() :bool
    {
        return $this->deleteDirectory($this->cacheDirectory);
    }

    /**
     * Delete a directory
     * @param $dir
     * @return bool
     */
    private function deleteDirectory($dir):bool
    {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }

    /**
     * Gets cache modification time
     *
     * @param string $cacheName String that was used while creating cache.
     * @return int|null
     */
    public function getTime(string $cacheName) :?int
    {
        $cacheFile = $this->getCachePath($cacheName);
        if (file_exists($cacheFile)) {
            return filemtime($cacheFile);
        }
        return FALSE;
    }
}
