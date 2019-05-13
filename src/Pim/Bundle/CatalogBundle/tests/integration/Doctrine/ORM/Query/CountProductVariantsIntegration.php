<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Doctrine\ORM\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class CountProductVariantsIntegration extends TestCase
{
    public function test_it_counts_the_number_of_product_variants_for_product_models(): void
    {
        $repository = $this->get('pim_catalog.repository.product_model');
        $query = $this->get('pim_catalog.query.count_product_variants');

        // No product model.
        $result = $query->forProductModels([]);
        self::assertEquals($result, 0);

        // Product model with 1 level of variant.
        $result = $query->forProductModels(
            [
                $repository->findOneByIdentifier('amor'),
            ]
        );
        self::assertEquals($result, 3);

        // Product model with 2 levels of variant.
        $result = $query->forProductModels(
            [
                $repository->findOneByIdentifier('caelus'),
            ]
        );
        self::assertEquals($result, 6);

        // Multiple product models with multiple levels of variant.
        $result = $query->forProductModels(
            [
                $repository->findOneByIdentifier('amor'),
                $repository->findOneByIdentifier('caelus'),
            ]
        );
        self::assertEquals($result, 9);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
