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

class FetchUserRightsOnProductModelIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_fetches_user_rights_of_an_uncategorized_product_model()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProductModel = $this->get('pimee_security.product_grid.query.fetch_user_rights_on_product_model');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $rootProductModelRights = $fetchUserRightOnProductModel->fetch('not_categorized_root_product_model', $userId);
        Assert::assertFalse($rootProductModelRights->canApplyDraftOnProductModel());
        Assert::assertTrue($rootProductModelRights->isProductModelEditable());

        $subProductModelRights = $fetchUserRightOnProductModel->fetch('not_categorized_sub_product_model', $userId);
        Assert::assertFalse($subProductModelRights->canApplyDraftOnProductModel());
        Assert::assertTrue($subProductModelRights->isProductModelEditable());
    }

    /**
     * @test
     */
    public function it_fetches_user_rights_of_a_product_model_with_editable_categories_and_owned_categories()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProductModel = $this->get('pimee_security.product_grid.query.fetch_user_rights_on_product_model');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $ownedRootProductModel = $fetchUserRightOnProductModel->fetch('owned_categorized_root_product_model', $userId);
        Assert::assertFalse($ownedRootProductModel->canApplyDraftOnProductModel());
        Assert::assertTrue($ownedRootProductModel->isProductModelEditable());

        $ownedSubProductModel = $fetchUserRightOnProductModel->fetch('owned_categorized_sub_product_model', $userId);
        Assert::assertFalse($ownedSubProductModel->canApplyDraftOnProductModel());
        Assert::assertTrue($ownedSubProductModel->isProductModelEditable());
    }

    /**
     * @test
     */
    public function it_fetches_user_rights_of_a_product_model_with_only_editable_categories()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProductModel = $this->get('pimee_security.product_grid.query.fetch_user_rights_on_product_model');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $editableProductModel = $fetchUserRightOnProductModel->fetch('editable_categorized_root_product_model', $userId);
        Assert::assertTrue($editableProductModel->canApplyDraftOnProductModel());
        Assert::assertFalse($editableProductModel->isProductModelEditable());
    }

    /**
     * @test
     */
    public function it_fetches_user_rights_of_a_product_model_with_non_viewable_categories()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProductModel = $this->get('pimee_security.product_grid.query.fetch_user_rights_on_product_model');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $nonViewableProductModel = $fetchUserRightOnProductModel->fetch('not_viewable_root_product_model', $userId);
        Assert::assertFalse($nonViewableProductModel->canApplyDraftOnProductModel());
        Assert::assertFalse($nonViewableProductModel->isProductModelEditable());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
