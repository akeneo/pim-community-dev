<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Product;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogBundle\tests\fixture\EntityBuilder;
use Pim\Component\Catalog\Model\ProductInterface;

class RemoveOptionalAttributeIntegration extends TestCase
{
    /**
     * @test
     */
    function test_remove_an_attribute_on_a_product_without_family()
    {
        /** @var EntityBuilder $entityBuilder */
        $entityBuilder = $this->getFromTestContainer('akeneo_integration_tests.catalog.fixture.build_entity');
        $entityBuilder->createProduct('playstation', '', [
            'values' => [
                'a_text' => [['data' => 'A playstation.', 'locale' => null, 'scope' => null]]
            ]
        ]);

        /** @var ProductInterface $product */
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('playstation');
        $this->get('pim_catalog.updater.product')->update($product, ['values' => []]);
        $this->get('validator')->validate($product);
        $this->get('pim_catalog.saver.product')->save($product);

        Assert::assertNull($product->getValue('a_text'));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
