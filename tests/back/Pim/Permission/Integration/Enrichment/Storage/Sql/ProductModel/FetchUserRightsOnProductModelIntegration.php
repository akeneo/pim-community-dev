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

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\Sql\ProductModel;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\Sql\UserRightsFixturesLoader;
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

        $fetchUserRightOnProductModel = $this->get('akeneo.pim.permission.product.query.fetch_user_rights_on_product_model');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $rootProductModelRights = $fetchUserRightOnProductModel->fetchByIdentifier('not_categorized_root_product_model', $userId);
        Assert::assertFalse($rootProductModelRights->canApplyDraftOnProductModel());
        Assert::assertTrue($rootProductModelRights->isProductModelEditable());
        Assert::assertTrue($rootProductModelRights->isProductModelViewable());

        $subProductModelRights = $fetchUserRightOnProductModel->fetchByIdentifier('not_categorized_sub_product_model', $userId);
        Assert::assertFalse($subProductModelRights->canApplyDraftOnProductModel());
        Assert::assertTrue($subProductModelRights->isProductModelEditable());
        Assert::assertTrue($subProductModelRights->isProductModelViewable());
    }

    /**
     * @test
     */
    public function it_fetches_user_rights_of_a_product_model_with_editable_categories_and_owned_categories()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProductModel = $this->get('akeneo.pim.permission.product.query.fetch_user_rights_on_product_model');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $ownedRootProductModel = $fetchUserRightOnProductModel->fetchByIdentifier('owned_categorized_root_product_model', $userId);
        Assert::assertFalse($ownedRootProductModel->canApplyDraftOnProductModel());
        Assert::assertTrue($ownedRootProductModel->isProductModelEditable());
        Assert::assertTrue($ownedRootProductModel->isProductModelViewable());

        $ownedSubProductModel = $fetchUserRightOnProductModel->fetchByIdentifier('owned_categorized_sub_product_model', $userId);
        Assert::assertFalse($ownedSubProductModel->canApplyDraftOnProductModel());
        Assert::assertTrue($ownedSubProductModel->isProductModelEditable());
        Assert::assertTrue($ownedSubProductModel->isProductModelViewable());
    }

    /**
     * @test
     */
    public function it_fetches_user_rights_of_a_product_model_with_only_editable_categories()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProductModel = $this->get('akeneo.pim.permission.product.query.fetch_user_rights_on_product_model');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $editableProductModel = $fetchUserRightOnProductModel->fetchByIdentifier('editable_categorized_root_product_model', $userId);
        Assert::assertTrue($editableProductModel->canApplyDraftOnProductModel());
        Assert::assertFalse($editableProductModel->isProductModelEditable());
        Assert::assertTrue($editableProductModel->isProductModelViewable());
    }

    /**
     * @test
     */
    public function it_fetches_user_rights_of_a_product_model_with_non_viewable_categories()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProductModel = $this->get('akeneo.pim.permission.product.query.fetch_user_rights_on_product_model');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $nonViewableProductModel = $fetchUserRightOnProductModel->fetchByIdentifier('not_viewable_root_product_model', $userId);
        Assert::assertFalse($nonViewableProductModel->canApplyDraftOnProductModel());
        Assert::assertFalse($nonViewableProductModel->isProductModelEditable());
        Assert::assertFalse($nonViewableProductModel->isProductModelViewable());
    }

    /**
     * @test
     */
    public function it_fetches_multiple_product_models_at_the_same_time()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();
        $fetchUserRightOnProductModels = $this->get('akeneo.pim.permission.product.query.fetch_user_rights_on_product_model');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchColumn('SELECT id FROM oro_user WHERE username = "mary"', [], 0);

        $productModelRights = $fetchUserRightOnProductModels->fetchByIdentifiers(
            ['not_viewable_root_product_model', 'editable_categorized_root_product_model'],
            $userId
        );

        Assert::assertCount(2, $productModelRights);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
