<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Assertion;

use Doctrine\Common\Collections\Collection as DoctrineCollection;

/**
 * Compare two collections
 */
final class Collection
{
    /** @var Collection */
    private $expectedEntities;

    /** @var Collection */
    private $actualEntities;

    /**
     * @param DoctrineCollection $expectedEntities
     * @param DoctrineCollection $actualEntities
     */
    public function __construct(DoctrineCollection $expectedEntities, DoctrineCollection $actualEntities)
    {
        $this->expectedEntities = $expectedEntities;
        $this->actualEntities = $actualEntities;
    }

    /**
     * Check that $expectedEntities (collection of entities) belongs to $actualEntities (collection of entities)
     *
     * @param string $identifier Entity identifier field
     *
     * @throws \Exception
     */
    public function containsEntities(string $identifier = 'code'): void
    {
        foreach ($this->expectedEntities as $expectedEntity) {
            if (!$this->actualEntities->contains($expectedEntity)) {
                $method = sprintf('get%s', ucfirst($identifier));
                $identifierValue = $expectedEntity->{$method}();

                throw new \Exception(sprintf('Expected entity with the identifier "%s" does not exist', $identifierValue));
            }
        }
    }
}
