<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * Exception thrown when performing an action on a property with invalid options.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidOptionsException extends InvalidPropertyException
{
    const VALID_ENTITY_CODE_EXPECTED_CODES = 306;

    /** @var array */
    private $propertyValues;

    /**
     * @param string          $propertyName
     * @param array           $propertyValues
     * @param string          $className
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(
        string $propertyName,
        array $propertyValues,
        string $className,
        string $message = '',
        int $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($propertyName, implode(', ', $propertyValues), $className, $message, $code, $previous);

        $this->propertyValues = $propertyValues;
    }

    /**
     * Build an exception when the data are invalid entity codes.
     *
     * @param string $propertyName
     * @param string $key
     * @param string $because
     * @param string $className
     * @param array  $values
     *
     * @return InvalidOptionsException
     */
    public static function validEntityListCodesExpected(
        string $propertyName,
        string $key,
        string $because,
        string $className,
        array $values
    ): InvalidOptionsException {
        $message = 'Property "%s" expects a list of valid %s. %s, "%s" given.';
        $flatValues = implode(', ', $values);

        return new static(
            $propertyName,
            $values,
            $className,
            sprintf($message, $propertyName, $key, $because, $flatValues),
            self::VALID_ENTITY_CODE_EXPECTED_CODES
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->propertyValues;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return implode(', ', $this->propertyValues);
    }
}
