<?php

namespace Akeneo\Component\StorageUtils\Exception;

/**
 * Invalid object exception the updater can throw when updating
 * an object which could not be handle by the updater.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidObjectException extends ObjectUpdaterException
{
    /* @var string */
    protected $objectClassName;

    /* @var string */
    protected $expectedClassName;

    /**
     * @param string     $objectClassName
     * @param int        $expectedClassName
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
     * @param $objectClassName
     * @param $expectedClassName
     *
     * @return InvalidObjectException
     */
    public static function objectExpected($objectClassName, $expectedClassName)
    {
        return new self(
            $objectClassName,
            $expectedClassName,
            sprintf(
                'Expects a "%s", "%s" provided.',
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
