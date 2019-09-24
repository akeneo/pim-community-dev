<?php

namespace Akeneo\Tool\Component\StorageUtils\Exception;

/**
 * Exception thrown when performing an action on an unsupported object.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidObjectException extends \LogicException
{
    /* @var string */
    protected $objectClassName;

    /* @var string */
    protected $expectedClassName;

    /**
     * @param string     $objectClassName
     * @param string     $expectedClassName
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct($objectClassName, $expectedClassName, $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->objectClassName   = $objectClassName;
        $this->expectedClassName = $expectedClassName;
    }

    /**
     * @param string $objectClassName
     * @param string $expectedClassName
     *
     * @return InvalidObjectException
     */
    public static function objectExpected($objectClassName, $expectedClassName)
    {
        return new static(
            $objectClassName,
            $expectedClassName,
            sprintf(
                'Expects a "%s", "%s" given.',
                $expectedClassName,
                $objectClassName
            )
        );
    }

    /**
     * @return string
     */
    public function getObjectClassName()
    {
        return $this->objectClassName;
    }

    /**
     * @return string
     */
    public function getExpectedClassName()
    {
        return $this->expectedClassName;
    }
}
