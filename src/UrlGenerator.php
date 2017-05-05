<?php

namespace Colibri\UrlGenerator;

use Colibri\Router\Route;
use Colibri\Router\Router;

/**
 * Class UrlGenerator
 * @package Colibri\UrlGenerator
 */
class UrlGenerator
{
  
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
    if ($this->normalSearch()->hardSearch()->isFounded()) {
      return $this->getLink();
    }
    return false;
  }
  
  /**
   * @return $this
   */
  protected function normalSearch()
  {
    foreach ($this->getRouter()->getRoutes() as $route) {
      
      $route->handleUri();
      
      if (
        count($route->getMacrosNames()) > 0
        && count($route->getMacrosNames()) === count($this->getParameters())
      ) {
        if (count(array_diff($route->getMacrosNames(), array_keys($this->getParameters()))) === 0) {
          $this->makeLink($route, $this->getParameters());
          break;
        }
      }
    }
    
    return $this;
  }
  
  /**
   * @return $this
   */
  protected function hardSearch()
  {
    if (!$this->isFounded()) {
      
      foreach ($this->getRouter()->getRoutes() as $route) {
        
        $route->handleUri();
        $matches = array_diff_key($this->getParameters(), $route->getMacrosPositions());
        $parameters = array_diff_key($this->getParameters(), $route->getMatches());
        
        $isEquals = (count(array_diff($matches, $route->getMatches())) === 0);

        if ($isEquals && count($route->getMatches()) > 0 && count($parameters) > 0) {
          $this->makeLink($route, $parameters);
          break;
        }
      }
    }
    
    return $this;
  }
  
  /**
   * @param Route $route
   * @param array $parameters
   * @return $this
   */
  protected function makeLink(Route $route, array $parameters = [])
  {
    $link = $route->getPseudoPattern();
    
    if (count($parameters) > 0) {
      $replacements = array_values($parameters);
      $search = array_map(function ($name) {
        return ":$name";
      }, array_keys($parameters));
      
      $link = str_replace($search, $replacements, $link);
    }
    
    $this->setLink($link);
    $this->setFounded(true);
    
    return $this;
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
   * @return Router
   */
  public function getRouter()
  {
    return $this->router;
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
