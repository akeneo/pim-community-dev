<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Doctrine\ORM\Query;

use Akeneo\Test\Integration\TestCase;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

class DescendantProductModelIdsIntegration extends TestCase
{
    public function test_it_fetches_product_ids_from_product_model_ids()
    {
        $query = $this->get('pim_catalog.query.descendant_product_model_ids');

        $resultRows = $query->fetchFromParentProductModelId(5);
        $this->assertCount(2, $resultRows);
        $this->assertSame([57, 58], $resultRows);

        $resultRows = $query->fetchFromParentProductModelId(1);
        $this->assertCount(0, $resultRows);
        $this->assertSame([], $resultRows);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
