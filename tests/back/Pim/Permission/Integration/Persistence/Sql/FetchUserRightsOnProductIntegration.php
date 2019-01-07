<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\Sql;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class FetchUserRightsOnProductIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_fetches_user_rights_of_an_uncategorized_product()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProduct = $this->get('pimee_security.product_grid.query.fetch_user_rights_on_product');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $productRights = $fetchUserRightOnProduct->fetch('not_categorized_variant_product', $userId);
        Assert::assertFalse($productRights->canApplyDraftOnProduct());
        Assert::assertTrue($productRights->isProductEditable());
    }

    /**
     * @test
     */
    public function it_fetches_user_rights_of_a_variant_product_with_editable_categories_and_owned_categories()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProduct = $this->get('pimee_security.product_grid.query.fetch_user_rights_on_product');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $productRights = $fetchUserRightOnProduct->fetch('owned_variant_product', $userId);
        Assert::assertFalse($productRights->canApplyDraftOnProduct());
        Assert::assertTrue($productRights->isProductEditable());
    }

    /**
     * @test
     */
    public function it_fetches_user_rights_of_a_variant_product_with_only_editable_categories()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProduct = $this->get('pimee_security.product_grid.query.fetch_user_rights_on_product');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $productRights = $fetchUserRightOnProduct->fetch('editable_variant_product', $userId);
        Assert::assertTrue($productRights->canApplyDraftOnProduct());
        Assert::assertFalse($productRights->isProductEditable());
    }

    /**
     * @test
     */
    public function it_fetches_user_rights_of_a_product_with_non_viewable_categories()
    {

        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProduct = $this->get('pimee_security.product_grid.query.fetch_user_rights_on_product');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $productRights = $fetchUserRightOnProduct->fetch('not_viewable_variant_product', $userId);
        Assert::assertFalse($productRights->canApplyDraftOnProduct());
        Assert::assertFalse($productRights->isProductEditable());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
