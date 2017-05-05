<?php

namespace Colibri\UrlGenerator;

use Colibri\Http\Request;
use Colibri\Router\Router;
use Colibri\URI\Builder as BaseUrlBuilder;

/**
 * Class UrlBuilder
 * @package Colibri\UrlGenerator
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
   * @param string $base
   * @param string $static
   */
  public function __construct(Router $router, Request $request, $base = '/', $static = '/')
  {
    parent::__construct($request, $base, $static);
    
    $this->router = $router;
  }
  
  /**
   * @param null $macros
   * @param null $params
   * @param array $query
   * @return null|string
   */
  public function create($macros = null, $params = null, array $query = [])
  {
    $generator = new UrlGenerator($macros, $this->prepareParameters($params), $this->getRouter());
  
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