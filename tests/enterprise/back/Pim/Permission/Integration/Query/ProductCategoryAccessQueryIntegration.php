<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Query;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ProductCategoryAccessQueryIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createProduct('product_without_category', []);
        $this->createProduct('product_viewable_by_everybody', [new SetCategories(['categoryA2'])]);

        $this->createProduct('product_not_viewable_by_redactor', [new SetCategories(['categoryB'])]);
        $this->createProductModel('product_model_with_not_viewable_categories', ['categoryB']);
        $this->createProductModel('product_model_with_viewable_categories', ['categoryA2']);
        $this->createProduct('product_with_product_model_not_viewable_by_redactor', [
            new ChangeParent('product_model_with_not_viewable_categories'),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionB'),

            new SetBooleanValue('a_yes_no', null, null, false),// needed for the variant level value
        ]);
        $this->createProduct('product_with_product_model_viewable_by_redactor', [
            new SetCategories(['categoryB']),
            new ChangeParent('product_model_with_viewable_categories'),
            new SetBooleanValue('a_yes_no', null, null, true),// needed for the variant level value
        ]);
    }

    public function test_it_returns_not_categorized_products_and_filter_not_granted_products()
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');
        $productIdentifiers = $this->getQuery()->getGrantedProductIdentifiers([
            'product_without_category',
            'product_viewable_by_everybody',
            'product_not_viewable_by_redactor',
            'unknown_product',
            'product_with_product_model_not_viewable_by_redactor',
            'product_with_product_model_viewable_by_redactor',
        ], $user);

        $productIdentifiersExpected = [
            'product_viewable_by_everybody',
            'product_without_category',
            'product_with_product_model_viewable_by_redactor'
        ];

        $this->assertEqualsCanonicalizing($productIdentifiersExpected, $productIdentifiers);

        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('julia');
        $productIdentifiers = $this->getQuery()->getGrantedProductIdentifiers([
            'product_without_category',
            'product_viewable_by_everybody',
            'product_not_viewable_by_redactor',
            'unknown_product'
        ], $user);

        $productIdentifiersExpected = [
            'product_viewable_by_everybody',
            'product_not_viewable_by_redactor',
            'product_without_category',
        ];

        $this->assertEqualsCanonicalizing($productIdentifiersExpected, $productIdentifiers);
    }

    public function test_it_returns_not_categorized_products_and_filter_not_granted_products_by_uuids()
    {
        $unknownUuid = Uuid::uuid4();
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');
        $productUuids = $this->getQuery()->getGrantedProductUuids(
            array_merge(
                array_map(
                    fn (string $identifier): UuidInterface => $this->getProductUuidFromIdentifier($identifier),
                    [
                        'product_without_category',
                        'product_viewable_by_everybody',
                        'product_not_viewable_by_redactor',
                        'product_with_product_model_not_viewable_by_redactor',
                        'product_with_product_model_viewable_by_redactor',
                    ]
                ),
                [$unknownUuid]
            ),
            $user
        );

        $productUuidsExpected = array_map(
            fn (string $identifier): UuidInterface => $this->getProductUuidFromIdentifier($identifier),
            [
                'product_viewable_by_everybody',
                'product_without_category',
                'product_with_product_model_viewable_by_redactor'
            ]
        );

        $this->assertEqualsCanonicalizing($productUuidsExpected, $productUuids);

        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('julia');
        $productUuids = $this->getQuery()->getGrantedProductUuids(
            array_merge(
                array_map(
                    fn (string $identifier): UuidInterface => $this->getProductUuidFromIdentifier($identifier),
                    [
                        'product_without_category',
                        'product_viewable_by_everybody',
                        'product_not_viewable_by_redactor',
                    ]
                ),
                [$unknownUuid]
            ),
            $user
        );

        $productIdentifiersExpected = array_map(
            fn (string $identifier): UuidInterface => $this->getProductUuidFromIdentifier($identifier),
            [
                'product_viewable_by_everybody',
                'product_not_viewable_by_redactor',
                'product_without_category',
            ]
        );

        $this->assertEqualsCanonicalizing($productIdentifiersExpected, $productUuids);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(string $identifier, array $userIntents = []): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->get('pim_enrich.product.message_bus')->dispatch(UpsertProductCommand::createWithIdentifier(
            $this->getUserId('admin'),
            ProductIdentifier::fromIdentifier($identifier),
            $userIntents
        ));
    }

    private function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);
        Assert::assertNotFalse($id);

        return \intval($id);
    }

    private function createProductModel(string $code, array $categoryCodes): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create($code);
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'code' => $code,
                'categories' => $categoryCodes,
                'family_variant' => 'familyVariantA2',
                'values' => [
                    'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionA']]
                ]
            ]
        );

        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    protected function getQuery(): ProductCategoryAccessQueryInterface
    {
        return $this->get('pimee_security.query.product_category_access_with_ids');
    }

    private function getProductUuidFromIdentifier(string $productIdentifier): UuidInterface
    {
        return Uuid::fromString($this->get('database_connection')->fetchOne(
            <<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
)
SELECT BIN_TO_UUID(product_uuid)
FROM pim_catalog_product_unique_data
WHERE raw_data = ?
AND attribute_id = (SELECT id FROM main_identifier)
SQL,
            [$productIdentifier]
        ));
    }
}
