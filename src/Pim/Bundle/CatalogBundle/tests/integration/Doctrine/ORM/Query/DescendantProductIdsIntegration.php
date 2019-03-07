<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Doctrine\ORM\Query;

use Akeneo\Test\Integration\TestCase;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

class DescendantProductIdsIntegration extends TestCase
{
    public function test_it_fetches_product_ids_from_product_model_ids()
    {
        $query = $this->get('pim_catalog.query.descendant_product_ids');

        $resultRows = $query->fetchFromProductModelIds([1]);
        $this->assertCount(3, $resultRows);
        $this->assertSame([1, 2, 3], $resultRows);

        $resultRows = $query->fetchFromProductModelIds([1, 2]);
        $this->assertCount(8, $resultRows);
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8], $resultRows);

        $resultRows = $query->fetchFromProductModelIds([81]);
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
