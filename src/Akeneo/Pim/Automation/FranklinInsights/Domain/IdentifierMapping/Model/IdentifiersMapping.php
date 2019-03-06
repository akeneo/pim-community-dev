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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;

/**
 * Holds the identifiers mapping. Collection of IdentifierMapping entities.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMapping implements \IteratorAggregate
{
    /** @var string[] */
    public const FRANKLIN_IDENTIFIERS = [
        'brand',
        'mpn',
        'upc',
        'asin',
    ];

    /** @var IdentifierMapping[] */
    private $mapping;

    /** @var array */
    private $diff;

    /**
     * @param array $mappedAttributes array of Attribute|null, indexed by Franklin identifier codes,
     *                                e.g: ['asin' => $asinAttribute, 'upc' => null, 'brand' => 'null, 'mpn' => null]
     */
    public function __construct(array $mappedAttributes)
    {
        foreach (static::FRANKLIN_IDENTIFIERS as $franklinIdentifier) {
            $this->mapping[$franklinIdentifier] = new IdentifierMapping(
                $franklinIdentifier,
                $mappedAttributes[$franklinIdentifier] ?? null
            );
        }

        $this->diff = [];
    }

    /**
     * @return IdentifierMapping[]
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }

    /**
     * @param string $name
     *
     * @return Attribute|null
     */
    public function getMappedAttribute(string $name): ?Attribute
    {
        if (array_key_exists($name, $this->mapping)) {
            return $this->mapping[$name]->getAttribute();
        }

        return null;
    }

    /**
     * Maps a catalog attribute to a Franklin identifier, and calculates the diff from the previous state.
     * This method is used to mutate the entity.
     *
     * @param string $franklinIdentifierCode
     * @param Attribute|null $attribute
     *
     * @return IdentifiersMapping
     */
    public function map(string $franklinIdentifierCode, ?Attribute $attribute): self
    {
        if (!in_array($franklinIdentifierCode, static::FRANKLIN_IDENTIFIERS)) {
            throw new \InvalidArgumentException(sprintf('Invalid identifier %s', $franklinIdentifierCode));
        }

        $formerAttributeCode =
            (null !== $this->getMappedAttribute($franklinIdentifierCode))
            ? $this->getMappedAttribute($franklinIdentifierCode)->getCode() : null;
        $newAttributeCode = null !== $attribute ? $attribute->getCode() : null;

        if ($formerAttributeCode !== $newAttributeCode) {
            $this->mapping[$franklinIdentifierCode] = new IdentifierMapping($franklinIdentifierCode, $attribute);
            $this->diff[$franklinIdentifierCode] = [
                'former' => $formerAttributeCode,
                'new' => $newAttributeCode,
            ];
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): iterable
    {
        return new \ArrayIterator($this->mapping);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty(array_filter(
            $this->mapping,
            function (IdentifierMapping $identifierMapping) {
                return null !== $identifierMapping->getAttribute();
            }
        ));
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $validMPNAndBrand = null !== $this->getMappedAttribute('mpn') && null !== $this->getMappedAttribute('brand');
        $validUPC = null !== $this->getMappedAttribute('upc');
        $validASIN = null !== $this->getMappedAttribute('asin');

        return $validASIN || $validUPC || $validMPNAndBrand;
    }

    /**
     * @return bool
     */
    public function isUpdated(): bool
    {
        return !empty($this->diff);
    }

    /**
     * Returns the Franklin identifier codes for which a mapping was updated or deleted (but not added).
     *
     * @return string[]
     */
    public function updatedIdentifierCodes(): array
    {
        return array_keys(
            array_filter(
                $this->diff,
                function (array $value) {
                    return null !== $value['former'];
                }
            )
        );
    }

    public function isMappedTo(AttributeCode $referenceAttributeCode): bool
    {
        foreach ($this->mapping as $identifierMapping) {
            $attribute = $identifierMapping->getAttribute();
            if (null !== $attribute && $referenceAttributeCode->equals($attribute->getCode())) {
                return true;
            }
        }

        return false;
    }
}
