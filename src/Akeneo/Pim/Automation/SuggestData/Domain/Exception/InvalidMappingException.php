<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Domain\Exception;

/**
 * TODO translation during management in front of errors
 *
 * Exception thrown when trying to save a not valid mapping.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class InvalidMappingException extends \InvalidArgumentException
{
    /** @var string */
    private $className;

    /**
     * @param string          $className
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(
        string $className,
        string $message = '',
        int $code = 400,
        ?\Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->className = $className;
    }

    /**
     * @param int|string $frequency
     * @param string     $attributeCode
     * @param string     $className
     *
     * @return InvalidMappingException
     */
    public static function duplicateAttributeCode($frequency, string $attributeCode, string $className)
    {
        $message = sprintf(
            'An attribute cannot be used more than once. Attribute "%s" has been used %s times.',
            $attributeCode,
            $frequency
        );

        return new static($className, $message);
    }

    /**
     * @param array $expectedIdentifiers
     * @param array $givenIdentifiers
     * @param string $className
     *
     * @return InvalidMappingException
     */
    public static function missingOrInvalidIdentifiersInMapping(
        array $expectedIdentifiers,
        array $givenIdentifiers,
        string $className
    ) {
        $message = sprintf(
            'Some identifiers mapping keys are missing or invalid. Expected: "%s", got "%s"',
            var_export($expectedIdentifiers, true),
            var_export($givenIdentifiers, true)
        );

        return new static($className, $message);
    }

    /**
     * @param string $attributeCode
     * @param string $className
     *
     * @return static
     */
    public static function attributeNotFound(
        string $attributeCode,
        string $className
    ) {
        $message = sprintf(
            'Attribute with attribute code "%s" for the identifiers mapping does not exist',
            $attributeCode
        );

        return new static($className, $message);
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
