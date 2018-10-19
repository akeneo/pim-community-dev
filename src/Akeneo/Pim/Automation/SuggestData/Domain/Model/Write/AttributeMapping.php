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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model\Write;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeMapping
{
    /** The attribute is not mapped yet */
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
     * @param int $status
     * @param null|string $pimAttributeCode
     */
    public function __construct(
        string $targetAttributeCode,
        string $targetAttributeType,
        int $status,
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

        if (!in_array($status, [self::ATTRIBUTE_PENDING, self::ATTRIBUTE_MAPPED, self::ATTRIBUTE_UNMAPPED])) {
            throw new \InvalidArgumentException('Status "%s" does not match with expected types (0, 1, 2)', $status);
        }
        $this->status = $status;

        if (self::ATTRIBUTE_MAPPED !== $this->status) {
            $this->pimAttributeCode = null;
        } elseif (null === $pimAttributeCode) {
            throw new \InvalidArgumentException('Status need to be mapped if you want to map with an attribute');
        } else {
            $this->pimAttributeCode = $pimAttributeCode;
        }
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
