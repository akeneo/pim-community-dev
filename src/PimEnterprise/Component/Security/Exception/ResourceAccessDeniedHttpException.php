<?php

namespace PimEnterprise\Component\Security\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Access denied for a resource.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResourceAccessDeniedHttpException extends AccessDeniedHttpException
{
    /** @var mixed */
    private $resource;

    /**
     * @param mixed           $resource
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $exception
     */
    public function __construct($resource, $message = '', $code = 403, \Exception $exception = null)
    {
        parent::__construct($message, $exception, $code);

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
