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

namespace Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Exception;

use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class InvalidMappingException extends \Exception
{
    /** @var string */
    private const IDENTIFIER_MAPPING_CONSTRAINT_KEY = 'akeneo_suggest_data.entity.identifier_mapping.constraint.%s';

    /** @var string */
    private $className;

    /** @var string */
    private $path;

    /** @var array */
    private $messageParams;

    /**
     * @param string $className
     * @param string $message
     * @param array $messageParams
     * @param string|null $path
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct(
        ?string $className,
        string $message = '',
        array $messageParams = [],
        string $path = null,
        int $code = 400,
        ?\Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->className = $className;
        $this->messageParams = $messageParams;
        $this->path = $path;
    }

    /**
     * @param string $className
     * @param string|null $path
     *
     * @return static
     */
    public static function duplicateAttributeCode(
        string $className,
        string $path = null
    ) {
        $message = sprintf(static::IDENTIFIER_MAPPING_CONSTRAINT_KEY, 'duplicate_attribute_code');

        return new static($className, $message, [], $path);
    }

    /**
     * @param array $givenIdentifiers
     * @param string $className
     * @param string|null $path
     *
     * @return static
     */
    public static function missingOrInvalidIdentifiersInMapping(
        array $givenIdentifiers,
        string $className,
        string $path = null
    ) {
        $message = sprintf(static::IDENTIFIER_MAPPING_CONSTRAINT_KEY, 'missing_or_invalid_identifiers');
        $messageParams = [
            'expected' => implode(IdentifiersMapping::FRANKLIN_IDENTIFIERS, ', '),
            'given' => implode($givenIdentifiers, ', '),
        ];

        return new static($className, $message, $messageParams, $path);
    }

    /**
     * @param string $attributeCode
     * @param string $className
     * @param string|null $path
     *
     * @return static
     */
    public static function attributeNotFound(
        string $attributeCode,
        string $className,
        string $path = null
    ) {
        $message = sprintf(static::IDENTIFIER_MAPPING_CONSTRAINT_KEY, 'attribute_not_found');
        $messageParams = [
            'attributeCode' => $attributeCode,
        ];

        return new static($className, $message, $messageParams, $path);
    }

    /**
     * @param string $className
     * @param string $missingAttributeCode
     *
     * @return static
     */
    public static function mandatoryAttributeMapping(string $className, string $missingAttributeCode)
    {
        $message = sprintf(static::IDENTIFIER_MAPPING_CONSTRAINT_KEY, 'mandatory_identifier_mapping');

        return new static($className, $message, [], $missingAttributeCode);
    }

    /**
     * @param string $className
     * @param string $attributeCode
     *
     * @return static
     */
    public static function attributeType(string $className, string $attributeCode)
    {
        $message = sprintf(static::IDENTIFIER_MAPPING_CONSTRAINT_KEY, 'attribute_type');

        return new static($className, $message, [], $attributeCode);
    }

    /**
     * @param string $attributeCode
     *
     * @return InvalidMappingException
     */
    public static function localizableNotAllowed(string $attributeCode): self
    {
        $message = sprintf(static::IDENTIFIER_MAPPING_CONSTRAINT_KEY, 'localizable_not_allowed');

        return new static(self::class, $message, [], $attributeCode);
    }

    /**
     * @param string $attributeCode
     *
     * @return InvalidMappingException
     */
    public static function scopableNotAllowed(string $attributeCode): self
    {
        $message = sprintf(static::IDENTIFIER_MAPPING_CONSTRAINT_KEY, 'scopable_not_allowed');

        return new static(self::class, $message, [], $attributeCode);
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return array
     */
    public function getMessageParams(): array
    {
        return $this->messageParams;
    }

    /**
     * @return null|string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
