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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model;

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

    /**
     * @param array $identifiers
     */
    public function __construct(array $identifiers)
    {
        $this->identifiers = $identifiers;
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
    public function getIdentifier(string $name): ?AttributeInterface
    {
        if (array_key_exists($name, $this->identifiers)) {
            return $this->identifiers[$name];
        }

        return null;
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
        return empty($this->identifiers) || empty(array_filter($this->identifiers));
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $validMPNAndBrand = null !== $this->getIdentifier('mpn') && null !== $this->getIdentifier('brand');
        $validUPC = null !== $this->getIdentifier('upc');
        $validASIN = null !== $this->getIdentifier('asin');

        return $validASIN || $validUPC || $validMPNAndBrand;
    }
}
