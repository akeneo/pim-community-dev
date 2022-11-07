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

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\Sql\UserRightsFixturesLoader;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class FetchUserRightsOnProductIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_fetches_user_rights_of_an_uncategorized_product_uuid()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProduct = $this->get('akeneo.pim.permission.product.query.fetch_user_rights_on_product');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchOne('SELECT id FROM oro_user WHERE username = "mary"');

        $productRights = $fetchUserRightOnProduct->fetchByUuid(
            $this->getProductUuid('not_categorized_variant_product'),
            $userId
        );
        Assert::assertFalse($productRights->canApplyDraftOnProduct());
        Assert::assertTrue($productRights->isProductEditable());
    }

    /**
     * @test
     */
    public function it_fetches_user_rights_of_a_variant_product_uuid_with_editable_categories_and_owned_categories()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProduct = $this->get('akeneo.pim.permission.product.query.fetch_user_rights_on_product');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchOne('SELECT id FROM oro_user WHERE username = "mary"');

        $productRights = $fetchUserRightOnProduct->fetchByUuid(
            $this->getProductUuid('owned_variant_product'),
            $userId
        );
        Assert::assertFalse($productRights->canApplyDraftOnProduct());
        Assert::assertTrue($productRights->isProductEditable());
        Assert::assertTrue($productRights->isProductViewable());
    }

    /**
     * @test
     */
    public function it_fetches_user_rights_of_a_variant_product_uuid_with_only_editable_categories()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProduct = $this->get('akeneo.pim.permission.product.query.fetch_user_rights_on_product');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchOne('SELECT id FROM oro_user WHERE username = "mary"');

        $productRights = $fetchUserRightOnProduct->fetchByUuid(
            $this->getProductUuid('editable_variant_product'),
            $userId
        );
        Assert::assertTrue($productRights->canApplyDraftOnProduct());
        Assert::assertFalse($productRights->isProductEditable());
    }

    /**
     * @test
     */
    public function it_fetches_user_rights_of_a_product_uuid_with_non_viewable_categories()
    {

        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProduct = $this->get('akeneo.pim.permission.product.query.fetch_user_rights_on_product');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchOne('SELECT id FROM oro_user WHERE username = "mary"');

        $productRights = $fetchUserRightOnProduct->fetchByUuid(
            $this->getProductUuid('not_viewable_variant_product'),
            $userId
        );
        Assert::assertFalse($productRights->canApplyDraftOnProduct());
        Assert::assertFalse($productRights->isProductEditable());
        Assert::assertFalse($productRights->isProductViewable());
    }

    /**
     * @test
     */
    public function it_fetches_multiple_products_uuids_at_the_same_time()
    {
        $fixtureLoader = new UserRightsFixturesLoader(static::$kernel->getContainer());
        $fixtureLoader->loadProductAndProductModels();

        $fetchUserRightOnProduct = $this->get('akeneo.pim.permission.product.query.fetch_user_rights_on_product');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchOne('SELECT id FROM oro_user WHERE username = "mary"');

        $productRights = $fetchUserRightOnProduct->fetchByUuids(
            [
                $this->getProductUuid('not_viewable_variant_product'),
                $this->getProductUuid('editable_variant_product')
            ],
            $userId
        );
        Assert::assertCount(2, $productRights);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_product_uuid_does_not_exist()
    {
        $this->expectException(ObjectNotFoundException::class);

        $fetchUserRightOnProduct = $this->get('akeneo.pim.permission.product.query.fetch_user_rights_on_product');

        $userId = (int) $this
            ->get('database_connection')
            ->fetchOne('SELECT id FROM oro_user WHERE username = "mary"');

        $fetchUserRightOnProduct->fetchByUuid(Uuid::uuid4(), $userId);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
