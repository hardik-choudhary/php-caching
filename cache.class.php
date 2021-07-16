<?php
/*
 * Project      : PHP Cache Class
 * Author       : Hardik Choudhary (ThinTake)
 * Author URL   : https://thintake.in
 * License      : GNU GPLv3
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
     * Sub folder in cacheDirectory
     * @var string|null
     */
    public ?string $currentFolder = NULL;

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
     * @param string $currentFolder
     */
    public function setCurrentFolder(string $currentFolder): void
    {
        $this->currentFolder = $currentFolder;

        $this->createDirectory("{$this->cacheDirectory}/{$this->currentFolder}");
        // $this->createDefaultFiles("{$this->cacheDirectory}/{$this->currentFolder}");
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
        if (file_exists($cacheFile)) {
            if($maxAge == 0 || (time() - filemtime($cacheFile)) <= $maxAge){
                return file_get_contents($cacheFile);
            }
            elseif($deleteExpired){
                $this->delete($cacheName);
            }
        }
        return NULL;
    }

    /**
     * Create new cache file
     * @param string $cacheName Any string that will be used to access the cache in future
     * @param string $content Content
     */
    public function write(string $cacheName, string $content) :void
    {
        $cacheFile  = $this->getCachePath($cacheName);
        $handle     = fopen($cacheFile, 'a');
        fwrite($handle, $content);
        fclose($handle);
    }

    /**
     * Delete cache file
     * @param string $cacheName
     */
    public function delete(string $cacheName) :void
    {
        @unlink($this->getCachePath($cacheName));
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
        if (!file_exists("{$directory}/.htaccess")) {
            $f = @fopen("{$directory}/.htaccess", "a+");
            @fwrite($f, "deny from all");
            @fclose($f);
        }
        if (!file_exists("{$directory}/index.html")) {
            $f = @fopen("{$directory}/index.html", "a+");
            @fclose($f);
        }
    }

    /**
     * Get full path of cache file
     * @param string $cacheName String that was used while creating cache
     * @return string
     */
    private function getCachePath(string $cacheName) :string
    {
        $SubFolder = ($this->currentFolder != NULL)? "/{$this->currentFolder}": '';
        return "{$this->cacheDirectory}{$SubFolder}/". hash('sha1', $cacheName) .".cache";
    }

}