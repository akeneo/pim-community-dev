<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Assert;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PHPUnit\Framework\Assert;

/**
 * Assert that the entity with values collection are correct.
 */
class AssertEntityWithValues
{
    /** @var array */
    private $exceptedEntityIdentifier;

    /** @var array */
    private $actualEntities;

    /** @var string */
    private $message;

    /**
     * @param EntityWithValuesInterface[] $exceptedEntityIdentifier
     * @param string[]                    $actualEntities
     * @param string                      $message
     */
    public function __construct(array $exceptedEntityIdentifier, array $actualEntities, string $message)
    {
        $this->exceptedEntityIdentifier = $exceptedEntityIdentifier;
        $this->actualEntities = $actualEntities;
        $this->message = $message;
    }

    /**
     * Check if $actualEntities contains the following products identifiers ($exceptedEntities)
     */
    public function sameProducts(): void
    {
        $this->compareResult(function ($entity) {
            $id = null;
            if ($entity instanceof ProductInterface) {
                $id = $entity->getIdentifier();
            }

            return $id;
        });
    }

    /**
     * Check if $actualEntities contains the following product models code ($exceptedEntities)
     */
    public function sameProductModels(): void
    {
        $this->compareResult(function ($entity) {
            $id = null;
            if ($entity instanceof ProductModelInterface) {
                $id = $entity->getCode();
            }

            return $id;
        });
    }

    /**
     * Check if $actualEntities contains the following product models / products $exceptedEntities
     */
    public function same(): void
    {
        $this->compareResult(function ($entity) {
            $id = null;
            if ($entity instanceof ProductInterface) {
                $id = $entity->getIdentifier();
            }

            if ($entity instanceof ProductModelInterface) {
                $id = $entity->getCode();
            }

            return $id;
        });
    }

    /**
     * Compare the result
     *
     * @param callable $function
     *
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    private function compareResult(callable $function): void
    {
        $exceptedEntities = $this->exceptedEntityIdentifier;
        $actualEntities = array_map($function, $this->actualEntities);

        sort($actualEntities);
        sort($exceptedEntities);

        Assert::assertSame($exceptedEntities, $actualEntities, $this->message);
    }
}
