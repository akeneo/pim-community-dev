<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Exception;

/**
 * Exception raises when try to remove an entity linked to published product
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductConsistencyException extends \Exception
{
    /** @var string */
    protected $route;

    /** @var array */
    protected $routeParams;

    /**
     * @param string     $message
     * @param integer    $code
     * @param \Exception $previous
     * @param string     $route
     * @param array      $routeParams
     */
    public function __construct(
        $message = "",
        $code = 0,
        \Exception $previous = null,
        $route = null,
        $routeParams = array()
    ) {
        parent::__construct($message, $code, $previous);

        $this->route = $route;
        $this->routeParams = $routeParams;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return array
     */
    public function getRouteParams()
    {
        return $this->routeParams;
    }
}
