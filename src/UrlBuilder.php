<?php

namespace Subapp\UrlGenerator;

use Subapp\Http\Request;
use Subapp\Router\Router;
use Subapp\URI\Builder as BaseUrlBuilder;

/**
 * Class UrlBuilder
 * @package Subapp\UrlGenerator
 */
class UrlBuilder extends BaseUrlBuilder
{

    /**
     * @var Router
     */
    private $router;

    /**
     * UrlBuilder constructor.
     * @param Router $router
     * @param Request $request
     */
    public function __construct(Router $router, Request $request)
    {
        parent::__construct($request);

        $this->router = $router;
    }

    /**
     * @param null $expression
     * @param null $parameters
     * @param array $query
     * @return null|string
     */
    public function create($expression = null, $parameters = null, array $query = [])
    {
        $parameters = $this->prepareParameters($parameters);

        if (false !== strpos($expression, '?')) {
            list($expression, $queryString) = explode('?', $expression, 2);
            $parameters = array_merge($parameters, $this->prepareParameters($queryString));
        }

        $generator = new UrlGenerator($expression, $parameters, $this->getRouter());

        return $generator->make() ? $this->path($generator->getLink(), $query) : null;
    }

    /**
     * @param $queryParameters
     * @return array $params
     */
    private function prepareParameters($queryParameters)
    {
        $params = [];

        if (is_string($queryParameters)) {
            parse_str($queryParameters, $params);
        } else if (is_array($queryParameters)) {
            $params = $queryParameters;
        }

        return array_filter($params);
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

}
