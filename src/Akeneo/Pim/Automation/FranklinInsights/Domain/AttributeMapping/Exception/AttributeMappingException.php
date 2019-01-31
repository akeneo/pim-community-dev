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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class AttributeMappingException extends \Exception
{
    /** @var string */
    private const CONSTRAINT_KEY = 'akeneo_franklin_insights.entity.attributes_mapping.constraint.%s';

    /** @var array */
    private $messageParams;

    /**
     * @param string $message
     * @param array $messageParams
     */
    public function __construct(string $message, array $messageParams = [])
    {
        parent::__construct($message);

        $this->messageParams = $messageParams;
    }

    /**
     * Thrown exception when a Franklin attribute is mapped to an invalid PIM attribute type.
     *
     * @param string $pimAttributeType
     *
     * @return AttributeMappingException
     */
    public static function incompatibleAttributeTypeMapping(string $pimAttributeType): self
    {
        $message = sprintf(static::CONSTRAINT_KEY, 'invalid_attribute_type_mapping');

        return new static($message, ['pimType' => $pimAttributeType]);
    }

    /**
     * @return AttributeMappingException
     */
    public static function localizableAttributeNotAllowed(): self
    {
        $message = sprintf(static::CONSTRAINT_KEY, 'localizable_not_allowed');

        return new static($message, []);
    }

    /**
     * @return AttributeMappingException
     */
    public static function scopableAttributeNotAllowed(): self
    {
        $message = sprintf(static::CONSTRAINT_KEY, 'scopable_not_allowed');

        return new static($message, []);
    }

    /**
     * @return AttributeMappingException
     */
    public static function localeSpecificAttributeNotAllowed(): self
    {
        $message = sprintf(static::CONSTRAINT_KEY, 'locale_specific_not_allowed');

        return new static($message, []);
    }

    /**
     * @return AttributeMappingException
     */
    public static function attributeNotInFamilyNotAllowed(): self
    {
        $message = sprintf(static::CONSTRAINT_KEY, 'attribute_not_in_family_not_allowed');

        return new static($message, []);
    }

    /**
     * @return AttributeMappingException
     */
    public static function duplicatedPimAttribute(): self
    {
        $message = sprintf(static::CONSTRAINT_KEY, 'duplicated_pim_attribute');

        return new static($message, []);
    }

    /**
     * @return AttributeMappingException
     */
    public static function emptyAttributesMapping(): self
    {
        $message = sprintf(static::CONSTRAINT_KEY, 'only_unknown_mapped_attributes');

        return new static($message, []);
    }

    /**
     * @return array
     */
    public function getMessageParams(): array
    {
        return $this->messageParams;
    }
}
