<?php

namespace Subapp\UrlGenerator;

use Subapp\Router\Pattern;
use Subapp\Router\Route;
use Subapp\Router\Router;

/**
 * Class UrlGenerator
 * @package Subapp\UrlGenerator
 */
class UrlGenerator
{

    const ROUTE_PATH_KEY = 'path';

    /**
     * @var
     */
    protected $router;

    /**
     * @var
     */
    protected $parameters;

    /**
     * @var bool
     */
    protected $founded = false;

    /**
     * @var
     */
    protected $link;

    /**
     * @param $macros
     * @param array $parameters
     * @param Router $router
     * @throws UrlGeneratorException
     */
    public function __construct($macros, array $parameters = [], Router $router)
    {

        $this->setRouter($router);

        if (!empty($macros)) {

            $macros = explode(':', $macros);

            if (count($macros) == 3) {
                list($parameters['module'], $parameters['controller'], $parameters['action']) = $macros;
            } else if (count($macros) == 2) {
                list($parameters['controller'], $parameters['action']) = $macros;
            } else {
                $parameters['controller'] = $macros[0];
            }

            $this->setParameters($parameters);
        } else {
            throw new UrlGeneratorException('The macro can not be empty.');
        }

    }

    /**
     * @return boolean
     */
    public function make()
    {
        $routes = $this->getRouter()->getRoutes();

        foreach ($routes as $route) {

            $route->createReplacements();
            $route->compile();

            $this->searchStepOne($route);

            if (true === $this->isFounded()) {
                return $this->getLink();
            }
        }

        return false;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param Router $router
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * @param Route $route
     */
    protected function searchStepOne(Route $route)
    {
        $positions  = $route->getPositions()[self::ROUTE_PATH_KEY] ?? [];
        $matches    = $route->getMatches();
        $positions  = array_diff_key($this->getParameters(), $positions);
        $parameters = array_diff_key($this->getParameters(), $route->getMatches());

        if (isset($matches['callback']) && !($matches['callback'] instanceof \Closure)) {
            $isEquals = (join($matches['callback']) == join($positions));
        } else {
            $isEquals = (count(array_diff($positions, $matches)) === 0);
        }

        if ($isEquals && count($matches) > 0 && count($parameters) > 0) {
            $this->compile($route, $this->getParameters());
        } else {
            $this->searchStepTwo($route);
        }
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param mixed $parameters
     * @return $this
     */
    public function setParameters($parameters)
    {
        $this->parameters = array_map('urlencode', $parameters);

        return $this;
    }

    /**
     * @param Route $route
     * @param array $parameters
     * @return $this
     */
    protected function compile(Route $route, array $parameters = [])
    {
        $pattern = ($route->getPatterns()[self::ROUTE_PATH_KEY] ?? Pattern::of('path', null))->getRaw();

        if (count($parameters) > 0) {
            $replacements = array_values($parameters);
            $search = array_map(function ($name) { return ":$name"; }, array_keys($parameters));
            $pattern = str_replace($search, $replacements, $pattern);
        }

        $this->setLink($pattern);
        $this->setFounded(true);

        return $this;
    }

    /**
     * @param Route $route
     */
    protected function searchStepTwo(Route $route)
    {
        $names = $route->getNames()[self::ROUTE_PATH_KEY] ?? [];

        if (count($names) > 0 && count($names) == count($this->getParameters())) {
            if (count(array_diff($names, array_keys($this->getParameters()))) == 0) {
                $this->compile($route, $this->getParameters());
            }
        }
    }

    /**
     * @return boolean
     */
    public function isFounded()
    {
        return $this->founded;
    }

    /**
     * @param boolean $founded
     * @return $this
     */
    public function setFounded($founded)
    {
        $this->founded = $founded;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $link
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

}
