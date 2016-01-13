<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Exception;

/**
 * Exception raises when try to remove an entity linked to published product
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PublishedProductConsistencyException extends \Exception
{
    /** @var string */
    protected $route;

    /** @var array */
    protected $routeParams;

    /**
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     * @param string     $route
     * @param array      $routeParams
     */
    public function __construct(
        $message = "",
        $code = 409,
        \Exception $previous = null,
        $route = null,
        $routeParams = []
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
