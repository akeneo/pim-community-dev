<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableProducts;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;

class GetNonViewableProductsIntegration extends EnrichmentProductTestCase
{
    private GetNonViewableProducts $getNonViewableProducts;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadEnrichmentProductFunctionalFixtures();

        $this->getNonViewableProducts = $this->getContainer()->get('Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableProducts');
    }

    /** @test */
    public function it_returns_non_viewable_product_identifiers(): void
    {
        $this->createProduct('product', [new SetCategories(['suppliers'])]);
        $this->createProduct('other_product', [new SetCategories(['print'])]);

        $nonViewableProducts = $this->getNonViewableProducts->fromProductIdentifiers(['product', 'other_product'], $this->getUserId('betty'));

        Assert::assertEquals(['product'], $nonViewableProducts);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}