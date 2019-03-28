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
     * @return AttributeCode|null
     */
    public function getMappedAttributeCode(string $name): ?AttributeCode
    {
        if (array_key_exists($name, $this->mapping)) {
            return $this->mapping[$name]->getAttributeCode();
        }

        return null;
    }

    /**
     * Maps a catalog attribute to a Franklin identifier, and calculates the diff from the previous state.
     * This method is used to mutate the entity.
     *
     * @param string $franklinIdentifierCode
     * @param AttributeCode|null $attributeCode
     *
     * @return IdentifiersMapping
     */
    public function map(string $franklinIdentifierCode, ?AttributeCode $attributeCode): self
    {
        if (!in_array($franklinIdentifierCode, static::FRANKLIN_IDENTIFIERS)) {
            throw new \InvalidArgumentException(sprintf('Invalid identifier %s', $franklinIdentifierCode));
        }

        $formerAttributeCode =
            (null !== $this->getMappedAttributeCode($franklinIdentifierCode))
            ? (string) $this->getMappedAttributeCode($franklinIdentifierCode) : null;
        $newAttributeCode = null !== $attributeCode ? (string) $attributeCode : null;

        if ($formerAttributeCode !== $newAttributeCode) {
            $this->mapping[$franklinIdentifierCode] = new IdentifierMapping($franklinIdentifierCode, (string) $attributeCode);
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
                return null !== $identifierMapping->getAttributeCode();
            }
        ));
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $validMPNAndBrand = null !== $this->getMappedAttributeCode('mpn') && null !== $this->getMappedAttributeCode('brand');
        $validUPC = null !== $this->getMappedAttributeCode('upc');
        $validASIN = null !== $this->getMappedAttributeCode('asin');

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
            $attributeCode = $identifierMapping->getAttributeCode();
            if (null !== $attributeCode && $referenceAttributeCode->equals($attributeCode)) {
                return true;
            }
        }

        return false;
    }
}
