<?php

namespace Akeneo\SharedCatalog\tests\back\Utils;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Webmozart\Assert\Assert;

trait CreateProduct
{
    private function createProduct(
        string $identifier,
        string $familyCode,
        array $data
    ): ProductInterface {
        Assert::isInstanceOfAny($this, [TestCase::class, ApiTestCase::class]);

        /** @var EntityBuilder $entityBuilder */
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        return $entityBuilder->createProduct($identifier, $familyCode, $data);
    }
}
