<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Assertion;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * Compare value collection
 */
final class ValuesCollection
{
    /** @var string[] */
    private $expectedEntityCodes;

    /** @var Collection */
    private $actualValueCollection;

    /**
     * @param string[]                 $expectedEntityCodes
     * @param WriteValueCollection $valuesCollection
     */
    public function __construct(array $expectedEntityCodes, WriteValueCollection $valuesCollection)
    {
        $this->expectedEntityCodes = $expectedEntityCodes;
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
        $expectedIdentityIdentifiers = $this->expectedEntityCodes;
        $actualIdentityIdentifiers = array_map(function(ValueInterface $value) {
            return $value->getAttributeCode();
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
