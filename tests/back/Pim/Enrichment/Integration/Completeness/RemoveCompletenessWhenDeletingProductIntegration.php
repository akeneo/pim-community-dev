<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Completeness;

class RemoveCompletenessWhenDeletingProductIntegration extends AbstractCompletenessTestCase
{
    public function testCompletenessIsUpdatedWhenProductIsDeleted()
    {
        $this->get('doctrine.orm.default_entity_manager')->clear();
        $product = $this->get('pim_catalog.repository.product')
            ->findOneBy(["identifier" => "watch"]);

        if ($product === null) {
            throw new \RuntimeException("Could not fetch a product identifier='watch'.");
        }

        $completeness = $this->getProductCompletenesses()
            ->fromProductId($product->getId());
        $this->assertTrue($completeness->count() !== 0);
        $this->get('doctrine.orm.default_entity_manager')->remove($product);
        $completeness = $this->getProductCompletenesses()
            ->fromProductId($product->getId());
        $this->assertEquals($completeness->count(), 0);
        $this->assertTrue(false);
    }
}
