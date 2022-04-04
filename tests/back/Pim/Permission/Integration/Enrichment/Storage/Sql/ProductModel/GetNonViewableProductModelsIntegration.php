<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\Sql\ProductModel;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel\GetNonViewableProductModels;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;

class GetNonViewableProductModelsIntegration extends EnrichmentProductTestCase
{
    private GetNonViewableProductModels $getNonViewableProductModels;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadEnrichmentProductFunctionalFixtures();
        $this->getNonViewableProductModels = $this->getContainer()
            ->get('Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableProductModels');
    }

    /** @test */
    public function it_returns_non_viewable_product_model_codes(): void
    {
        $this->createProductModel('product_model_with_not_viewable_categories', 'color_variant_accessories',
            ['categories' => ['suppliers']]
        );
        $this->createProductModel('product_model_with_viewable_categories', 'color_variant_accessories',
            ['categories' => ['print']]
        );
        $nonViewableProductModelCodes = $this->getNonViewableProductModels->fromProductModelCodes(
            ['product_model_with_not_viewable_categories', 'product_model_with_viewable_categories'],
            $this->getUserId('betty')
        );
        Assert::assertEquals(['product_model_with_not_viewable_categories'], $nonViewableProductModelCodes);

    }
}
