<?php

namespace sborislav\api;

class route
{
    private $array = array();
    public $cache = false;

    /**
     * Создание объекта
     */
    public function __construct()
    {
        if ($this->cache()) {
            $this->getCache();
            $this->cache = true;
        }
    }

    /**
     * Добавление пути
     *
     * @param $url
     * @param $path
     * @return $this
     */
    public function add( $url, $path )
    {
        $this->array[$url] = $path;
        return $this;
    }

    /**
     * Проверка на существование кеша
     *
     * @param $url
     * @return bool
     */
    public function check($url)
    {
        return isset($this->array[$url]);
    }

    /**
     * Вывод реального пути
     *
     * @param $url
     * @return mixed
     */
    public function path($url)
    {
        return $this->array[$url];
    }

    /**
     * Проверка на существование кеша
     *
     * @return bool
     */
    public function cache()
    {
        return file_exists(__DIR__.'/../cache/config.php');
    }

    /**
     * Создание кеша
     *
     * @return bool
     */
    public function makeCache()
    {
        if (!file_exists(__DIR__.'/../cache'))
            mkdir(__DIR__.'/../cache');
        
        $string = "<?php\n return ".var_export($this->array, true).';';
        if ( file_put_contents(__DIR__.'/../cache/config.php', $string) === false )
            return false;
        return true;
    }

    /**
     * Получение кеша
     *
     * @return $this
     */
    private function getCache()
    {
        $this->array = include __DIR__.'/../cache/config.php';
        return $this;
    }
}
