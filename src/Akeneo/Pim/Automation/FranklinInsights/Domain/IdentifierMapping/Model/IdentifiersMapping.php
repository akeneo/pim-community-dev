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

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

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

    /** @var IdentifierMapping[] */
    private $formerMapping;

    public function __construct()
    {
        $this->mapping = array_fill_keys(self::FRANKLIN_IDENTIFIERS, null);

        $this->formerMapping = $this->mapping;

        foreach (array_keys($this->mapping) as $identifier) {
            $this->mapping[$identifier] = new IdentifierMapping($identifier, null);
        }
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
     * @return AttributeInterface|null
     */
    public function getMappedAttribute(string $name): ?AttributeInterface
    {
        if (array_key_exists($name, $this->mapping)) {
            return $this->mapping[$name]->getAttribute();
        }

        return null;
    }

    /**
     * Map a franklin identifier to a catalog attribute.
     *
     * @param string $franklinIdentifierCode
     * @param AttributeInterface|null $attribute
     *
     * @return IdentifiersMapping
     */
    public function map(string $franklinIdentifierCode, ?AttributeInterface $attribute): self
    {
        if (!in_array($franklinIdentifierCode, self::FRANKLIN_IDENTIFIERS)) {
            throw new \InvalidArgumentException(sprintf('Invalid identifier %s', $franklinIdentifierCode));
        }

        $identifierMapping = $this->mapping[$franklinIdentifierCode];
        $identifierMapping->setAttribute($attribute);

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
        return empty($this->mapping) || empty(array_filter($this->mapping, function (IdentifierMapping $identifierMapping) {
            return null !== $identifierMapping->getAttribute();
        }));
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
}
