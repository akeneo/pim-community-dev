<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Access denied for a resource.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ResourceAccessDeniedException extends AccessDeniedException
{
    /** @var mixed */
    private $resource;

    /**
     * @param mixed           $resource
     * @param string          $message
     * @param \Exception|null $exception
     */
    public function __construct($resource, $message = '', \Exception $exception = null)
    {
        parent::__construct($message, $exception);

        $this->resource = $resource;
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }
}
