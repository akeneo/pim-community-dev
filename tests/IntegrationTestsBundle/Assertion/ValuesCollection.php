<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Assertion;

use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * Compare value collection
 */
final class ValuesCollection
{
    /** @var Collection */
    private $expectedEntities;

    /** @var Collection */
    private $actualValueCollection;

    /**
     * @param string[]                 $expectedEntities
     * @param ValueCollectionInterface $valuesCollection
     */
    public function __construct(array $expectedEntities, ValueCollectionInterface $valuesCollection)
    {
        $this->expectedEntities = $expectedEntities;
        $this->actualValueCollection = $valuesCollection;
    }

    /**
     * Compare if the $expectedEntities (array of attribute codes) is strictly equal to $valuesCollection (collection
     * of value)
     *
     * @throws \Exception
     */
    public function hasSameValues(): void
    {
        $expectedIdentityIdentifiers = $this->expectedEntities;
        $actualIdentityIdentifiers = array_map(function(ValueInterface $value) {
            return $value->getAttribute()->getCode();
        }, $this->actualValueCollection->toArray());

        sort($actualIdentityIdentifiers);
        sort($expectedIdentityIdentifiers);

        if ($actualIdentityIdentifiers !== $expectedIdentityIdentifiers) {
            throw new \Exception(
                sprintf(
                    'Expected entities with identifiers "%s" don\'t not exist',
                    implode(', ', array_diff($actualIdentityIdentifiers, $expectedIdentityIdentifiers))
                )
            );
        }
    }
}
