<?php


namespace AkeneoTest\Pim\Enrichment\Integration\Classification;

use Akeneo\Test\Integration\TestCase;

class ClassifyProductIntegration extends TestCase
{
    public function testClassify()
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('tee', 'clothing');
        $this->get('pim_catalog.updater.product')->update($product, ['categories' => ['supplier_zaro']]);
        $this->get('pim_catalog.validator.product')->validate($product);
        $this->get('pim_catalog.saver.product')->save($product);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('tee');
        $this->assertCount(1, $product->getCategories());
        $category = $product->getCategories()->first();
        $this->assertEquals($category->getCode(), 'supplier_zaro');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
