<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

/**
 * Exception thrown when performing an action on a PQB sorter with an unexpected value type.
 *
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidDirectionException extends \LogicException
{
    const NOT_SUPPORTED_CODE = 300;

    /** @var array */
    protected $directions;

    /** @var mixed */
    protected $value;

    /** @var string */
    protected $className;

    /**
     * @param array           $directions
     * @param mixed           $value
     * @param string          $className
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(
        array $directions,
        $value,
        $className,
        $message = '',
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->directions = $directions;
        $this->value = $value;
        $this->className = $className;
    }

    /**
     * Build an exception when the direction is not supported.
     *
     * @param string $direction
     * @param string $className
     *
     * @return InvalidDirectionException
     */
    public static function notSupported($direction, $className)
    {
        $message = 'Direction "%s" is not supported';

        return new self(
            [$direction],
            null,
            $className,
            sprintf($message, $direction),
            self::NOT_SUPPORTED_CODE
        );
    }

    /**
     * @return array
     */
    public function getDirections()
    {
        return $this->directions;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}
