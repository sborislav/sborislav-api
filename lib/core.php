<?php

namespace sborislav\api;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

use sborislav\api\Route as sRoute;

class core
{
    /**
     * @var sRoute
     */
    private $route;

    /**
     * @var array
     */
    private $param = array(), $response = array("error" => "API не найдено");

    /**
     * @var string
     */
    private $dir;

    /**
     * Определение класса и метода
     */
    public function Route()
    {
        $route = new Route(
            '/{code}/{class}.{method}',
            array(),
            array('code' => '\w{18}', 'class' => '[A-z]+', 'method' => '[A-z]+' )
        );

        $routes = new RouteCollection();
        $routes->add('API_route', $route);

        $context = new RequestContext();
        $matcher = new UrlMatcher($routes, $context);

        $url = $_SERVER['REQUEST_URI'];
        $pos = strpos($url, '?');
        if ($pos) $url = substr($url, 0, $pos);

        try {
            $this->param = $matcher->match($url);
            $this->method();
        } catch (ResourceNotFoundException $e ){

        }
    }

    /**
     * Создание объекта
     *
     * @param $dir
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
        $route = new sRoute();

        if (!$route->cache) {
            foreach (scandir($this->dir) as $row) {
                if (!in_array($row, array(".", ".."))) {
                    if (file_exists($this->dir.'/'.$row.'/config.php')) {
                        $url = include $this->dir.'/'.$row.'/config.php';
                        $route->add($url, $row);
                    }
                }
            }
            $route->makeCache();
        }
        $this->route = $route;
        $this->Route();
    }

    /**
     * Вывод объекта при завершении работы
     */
    public function __destruct()
    {
        echo $this;
    }


    /**
     * Преобразование текущего объекта в ответ
     *
     * @return string
     */
    public function __toString()
    {
        return $this->Response( $this->response );
    }

    /**
     * Вызов метода
     */
    private function method()
    {
        if ( $this->route->check($this->param['code']) ) {
            if (file_exists( $this->dir.'/'.$this->route->path($this->param['code']).'/'.$this->param['class'].'.php' )) {
                include_once $this->dir.'/'.$this->route->path($this->param['code']).'/'.$this->param['class'].'.php';
                $api_class = new $this->param['class']();
                if (class_exists($this->param['class']) && method_exists ($api_class, $this->param['method']))
                    $this->response = $api_class->{$this->param['method']}();
            }
        }
    }

    /**
     * Возврат ответа
     *
     * @param array $response
     * @return string
     */
    private function Response($response = array() )
    {
        if (is_array($response))
            return json_encode($response);
        else
            return json_encode(array("error" => "API не найдено"));
    }
}
