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

namespace Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model;

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

    /** @var array */
    private $identifiers;

    private $formerIdentifiers;

    /**
     * @param array $identifiers
     */
    public function __construct()
    {
        $this->identifiers = array_fill_keys(self::FRANKLIN_IDENTIFIERS, null);

        $this->formerIdentifiers = $this->identifiers;

        foreach (array_keys($this->identifiers) as $identifier) {
            $this->identifiers[$identifier] = new IdentifierMapping($identifier, null);
        }
    }

    /**
     * @return array
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * @param string $name
     *
     * @return null|AttributeInterface
     */
    public function getMappedAttribute(string $name): ?AttributeInterface
    {
        if (array_key_exists($name, $this->identifiers)) {
            return $this->identifiers[$name]->getAttribute();
        }

        return null;
    }

    public function map(string $franklinIdentifierCode, ?AttributeInterface $attribute): self
    {
        if (!in_array($franklinIdentifierCode, self::FRANKLIN_IDENTIFIERS)) {
            throw new \InvalidArgumentException(sprintf('Invalid identifier %s', $franklinIdentifierCode));
        }

        $identifierMapping = $this->identifiers[$franklinIdentifierCode];
        $identifierMapping->setAttribute($attribute);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): iterable
    {
        return new \ArrayIterator($this->identifiers);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->identifiers) || empty(array_filter($this->identifiers, function (IdentifierMapping $identifierMapping) {
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
