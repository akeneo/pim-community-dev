<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\tests\integration\Doctrine\Counter;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class CountProductsPerCategoriesIntegration extends TestCase
{
    public function testProductsAreCountedInCategories(): void
    {
        $category = $this->getFromTestContainer('pim_catalog.repository.category')
            ->findOneByCode('master_accessories');

        $productsCount = $this->getFromTestContainer('pim_enrich.doctrine.counter.category_product')
            ->getItemsCountInCategory($category, false);

        $this->assertEquals(0, $productsCount);
    }

    public function testProductsAreCountedInSubcategories(): void
    {
        $category = $this->getFromTestContainer('pim_catalog.repository.category')
            ->findOneByCode('master_accessories_hats');
        $this->getFromTestContainer('pim_catalog.remover.category')
            ->remove($category);

        $category = $this->getFromTestContainer('pim_catalog.repository.category')
            ->findOneByCode('master_accessories');

        $productsCount = $this->getFromTestContainer('pim_enrich.doctrine.counter.category_product')
            ->getItemsCountInCategory($category, true);

        $this->assertEquals(4, $productsCount);
    }

    public function testVariantProductsAreCountedInCategories(): void
    {
        $category = $this->getFromTestContainer('pim_catalog.repository.category')
            ->findOneByCode('master_men_pants_jeans');
        $productsCount = $this->getFromTestContainer('pim_enrich.doctrine.counter.category_product')
            ->getItemsCountInCategory($category, false);

        $this->assertEquals(12, $productsCount);

        $category = $this->getFromTestContainer('pim_catalog.repository.category')
            ->findOneByCode('master_women_dresses');
        $productsCount = $this->getFromTestContainer('pim_enrich.doctrine.counter.category_product')
            ->getItemsCountInCategory($category, false);

        $this->assertEquals(21, $productsCount);
    }

    public function testVariantProductsAreCountedInSubcategories(): void
    {
        $category = $this->getFromTestContainer('pim_catalog.repository.category')
            ->findOneByCode('master_men_pants');
        $productsCount = $this->getFromTestContainer('pim_enrich.doctrine.counter.category_product')
            ->getItemsCountInCategory($category, true);

        $this->assertEquals(48, $productsCount);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
