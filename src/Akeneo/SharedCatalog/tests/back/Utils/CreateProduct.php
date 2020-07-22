<?php

namespace Akeneo\SharedCatalog\tests\back\Utils;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Webmozart\Assert\Assert;

trait CreateProduct
{
    private function createProduct(
        string $identifier,
        string $familyCode,
        array $data
    ): ProductInterface {
        Assert::isInstanceOf($this, TestCase::class);

        /** @var EntityBuilder $entityBuilder */
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        return $entityBuilder->createProduct($identifier, $familyCode, $data);
    }
}
