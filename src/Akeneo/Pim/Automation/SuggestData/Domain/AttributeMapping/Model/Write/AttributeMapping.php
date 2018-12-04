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

namespace Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Write;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeMapping
{
    /** The attribute is pending */
    public const ATTRIBUTE_PENDING = 0;

    /** The attribute is mapped */
    public const ATTRIBUTE_MAPPED = 1;

    /** The attribute was registered to not be mapped */
    public const ATTRIBUTE_UNMAPPED = 2;

    /** @var array */
    public const ATTRIBUTE_TYPES_MAPPING = [
        'metric' => AttributeTypes::METRIC,
        'select' => AttributeTypes::OPTION_SIMPLE_SELECT,
        'multiselect' => AttributeTypes::OPTION_MULTI_SELECT,
        'number' => AttributeTypes::NUMBER,
        'text' => AttributeTypes::TEXT,
        'boolean' => AttributeTypes::BOOLEAN,
        'identifier' => AttributeTypes::IDENTIFIER,
    ];

    /** @var string */
    private $targetAttributeCode;

    /** @var string */
    private $targetAttributeType;

    /** @var string|null */
    private $pimAttributeCode;

    /** @var AttributeInterface */
    private $attribute;

    /** @var string */
    private $status;

    /**
     * @param string $targetAttributeCode
     * @param string $targetAttributeType
     * @param null|string $pimAttributeCode
     */
    public function __construct(
        string $targetAttributeCode,
        string $targetAttributeType,
        ?string $pimAttributeCode
    ) {
        $this->targetAttributeCode = $targetAttributeCode;

        if (!array_key_exists($targetAttributeType, self::ATTRIBUTE_TYPES_MAPPING)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Type "%s" does not match with expected types (%s)',
                    $targetAttributeType,
                    implode(', ', array_keys(self::ATTRIBUTE_TYPES_MAPPING))
                )
            );
        }
        $this->targetAttributeType = $targetAttributeType;
        $this->pimAttributeCode = empty($pimAttributeCode) ? null : $pimAttributeCode;
        $this->status = empty($this->pimAttributeCode) ? self::ATTRIBUTE_UNMAPPED : self::ATTRIBUTE_MAPPED;
    }

    /**
     * @return string
     */
    public function getPimAttributeCode(): ?string
    {
        return $this->pimAttributeCode;
    }

    /**
     * @return string
     */
    public function getTargetAttributeCode(): string
    {
        return $this->targetAttributeCode;
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return AttributeMapping
     */
    public function setAttribute(AttributeInterface $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * @return AttributeInterface
     */
    public function getAttribute(): ?AttributeInterface
    {
        return $this->attribute;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getTargetAttributeType(): string
    {
        return $this->targetAttributeType;
    }
}
