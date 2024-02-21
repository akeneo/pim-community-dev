<?php

namespace Oro\Bundle\SecurityBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException as BaseAccessDeniedException;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AccessDeniedException extends BaseAccessDeniedException
{
    /** @var string */
    protected $controllerClass;

    /** @var string */
    protected $method;

    /**
     * @param string $controllerClass
     * @param string $method
     *
     * @return AccessDeniedException
     */
    public static function create($controllerClass, $method)
    {
        return new self($controllerClass, $method, sprintf('Access denied to %s::%s.', $controllerClass, $method));
    }

    /**
     * @param string     $controllerClass
     * @param string     $method
     * @param string     $message
     * @param \Exception $previous
     */
    public function __construct($controllerClass, $method, $message, \Exception $previous = null)
    {
        parent::__construct($message, $previous);

        $this->controllerClass = $controllerClass;
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}
