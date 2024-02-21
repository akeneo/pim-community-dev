<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PreventDuplicateVariantAssociation extends TestCase
{
    /** @test */
    public function it_does_not_duplicate_associations_on_a_sub_product_model(): void
    {
        // Given a root product model with associations
        $this->upsertProductModel(
            'root_pm',
            [
                'family_variant' => 'familyVariantA1',
                'associations' => [
                    'x_SELL' => [
                        'products' => ['asso_product_1'],
                        'product_models' => ['asso_pm_1'],
                        'groups' => ['groupA'],
                    ],
                ],
            ]
        );

        // When I associate entities to its sub product model
        $this->upsertProductModel(
            'sub_pm',
            [
                'parent' => 'root_pm',
                'values' => [
                    'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionA']],
                ],
                'associations' => [
                    'x_SELL' => [
                        'products' => ['asso_product_1', 'asso_product_2'],
                        'product_models' => ['asso_pm_2', 'asso_pm_1'],
                        'groups' => ['groupA', 'groupB'],
                    ],
                ],
            ]
        );

        // Then the associations of the parent should not be duplicated
        Assert::assertEqualsCanonicalizing(
            [
                'products' => ['asso_product_2'],
                'product_models' => ['asso_pm_2'],
                'groups' => ['groupB'],
            ],
            $this->getDirectProductModelAssociations('sub_pm', 'X_SELL')
        );
    }

    /** @test */
    public function it_odes_not_duplicate_associations_on_a_variant_product(): void
    {
        // Given a root product model and a sub product model with associations
        $this->upsertProductModel(
            'root_pm',
            [
                'family_variant' => 'familyVariantA1',
                'associations' => [
                    'x_SELL' => [
                        'products' => ['asso_product_1'],
                        'product_models' => ['asso_pm_1'],
                        'groups' => ['groupA'],
                    ],
                ],
            ]
        );
        $this->upsertProductModel(
            'sub_pm',
            [
                'parent' => 'root_pm',
                'values' => [
                    'a_simple_select' => [['scope' => null, 'locale' => null, 'data' => 'optionA']],
                ],
                'associations' => [
                    'x_SELL' => [
                        'products' => ['asso_product_2'],
                        'product_models' => ['asso_pm_2'],
                        'groups' => ['groupB'],
                    ],
                ],
            ]
        );

        // When I associate entities to their variant product
        $product = $this->upsertProduct(
            'variant_product',
            [
                new ChangeParent('sub_pm'),
                new SetBooleanValue('a_yes_no', null, null, true),
                new AssociateProducts('X_SELL', ['asso_product_1', 'asso_product_3', 'asso_product_2']),
                new AssociateProductModels('X_SELL', ['asso_pm_3', 'asso_pm_2', 'asso_pm_1']),
                new AssociateGroups('X_SELL', ['groupA', 'groupB', 'groupC']),
            ]
        );

        // Then the associations of the ancestors should not be duplicated
        Assert::assertEqualsCanonicalizing(
            [
                'products' => ['asso_product_3'],
                'product_models' => ['asso_pm_3'],
                'groups' => ['groupC'],
            ],
            $this->getDirectProductAssociations($product->getUuid(), 'X_SELL')
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->upsertProduct('asso_product_1', []);
        $this->upsertProduct('asso_product_2', []);
        $this->upsertProduct('asso_product_3', []);
        $this->upsertProductModel('asso_pm_1', ['family_variant' => 'familyVariantA1']);
        $this->upsertProductModel('asso_pm_2', ['family_variant' => 'familyVariantA1']);
        $this->upsertProductModel('asso_pm_3', ['family_variant' => 'familyVariantA1']);
        // create product group "groupC"
        $group = $this->get('pim_catalog.factory.group')->create();
        $this->get('pim_catalog.updater.group')->update($group, ['code' => 'groupC', 'type' => 'RELATED']);
        $this->get('pim_catalog.saver.group')->save($group);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function upsertProductModel(string $code, array $data): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier($code) ??
            $this->get('pim_catalog.factory.product_model')->create();

        $this->get('pim_catalog.updater.product_model')->update($productModel, \array_merge($data, ['code' => $code]));
        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, \sprintf('The product model is invalid: %s', $violations));
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->clearCache();

        return $productModel;
    }

    private function upsertProduct(string $identifier, array $userIntents): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier($identifier),
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->clearCache();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    private function clearCache(): void
    {
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    /**
     * @return array {products: string[], product_models: string[], groups: string[]}
     */
    private function getDirectProductAssociations(UuidInterface $ownerUuid, string $associationTypeCode): array
    {
        $sql = <<<SQL
            SELECT 
                JSON_ARRAYAGG(associated_product.identifier) AS associated_product_identifiers,
                JSON_ARRAYAGG(associated_pm.code) AS associated_product_model_codes,
                JSON_ARRAYAGG(associated_group.code) AS associated_group_codes
            FROM pim_catalog_association asso
                INNER JOIN pim_catalog_association_type type ON type.id = asso.association_type_id
                LEFT JOIN pim_catalog_association_product asso_products ON asso_products.association_id = asso.id
                LEFT JOIN pim_catalog_product associated_product ON asso_products.product_uuid = associated_product.uuid
                LEFT JOIN pim_catalog_association_product_model asso_pms ON asso_pms.association_id = asso.id
                LEFT JOIN pim_catalog_product_model associated_pm ON associated_pm.id = asso_pms.product_model_id
                LEFT JOIN pim_catalog_association_group asso_groups ON asso.id = asso_groups.association_id
                LEFT JOIN pim_catalog_group associated_group ON associated_group.id = asso_groups.group_id
            WHERE asso.owner_uuid = :uuid
            AND type.code = :associationTypeCode;
            SQL;
        $result = $this->getConnection()->executeQuery(
            $sql,
            [
                'uuid' => $ownerUuid->getBytes(),
                'associationTypeCode' => $associationTypeCode,
            ]
        )->fetchAssociative();

        return [
            'products' => \json_decode($result['associated_product_identifiers'] ?? '[]', true),
            'product_models' => \json_decode($result['associated_product_model_codes'] ?? '[]', true),
            'groups' => \json_decode($result['associated_group_codes'] ?? '[]', true),
        ];
    }

    /**
     * @return array {products: string[], product_models: string[], groups: string[]}
     */
    private function getDirectProductModelAssociations(string $ownerCode, string $associationTypeCode): array
    {
        $sql = <<<SQL
            WITH main_identifier AS (
                SELECT id
                FROM pim_catalog_attribute
                WHERE main_identifier = 1
                LIMIT 1
            )
            SELECT 
                JSON_ARRAYAGG(pcpud.raw_data) AS associated_product_identifiers,
                JSON_ARRAYAGG(associated_pm.code) AS associated_product_model_codes,
                JSON_ARRAYAGG(associated_group.code) AS associated_group_codes
            FROM pim_catalog_product_model owner
                INNER JOIN pim_catalog_product_model_association asso ON asso.owner_id = owner.id
                INNER JOIN pim_catalog_association_type type ON type.id = asso.association_type_id
                LEFT JOIN pim_catalog_association_product_model_to_product asso_products ON asso_products.association_id = asso.id
                LEFT JOIN pim_catalog_product associated_product ON asso_products.product_uuid = associated_product.uuid
                LEFT JOIN pim_catalog_product_unique_data pcpud
                    ON pcpud.product_uuid = associated_product.uuid
                    AND pcpud.attribute_id = (SELECT id FROM main_identifier)
                LEFT JOIN pim_catalog_association_product_model_to_product_model asso_pms ON asso_pms.association_id = asso.id
                LEFT JOIN pim_catalog_product_model associated_pm ON associated_pm.id = asso_pms.product_model_id
                LEFT JOIN pim_catalog_association_product_model_to_group asso_groups ON asso.id = asso_groups.association_id
                LEFT JOIN pim_catalog_group associated_group ON associated_group.id = asso_groups.group_id
            WHERE owner.code = :ownerCode
            AND type.code = :associationTypeCode;
            SQL;
        $result = $this->getConnection()->executeQuery(
            $sql,
            [
                'ownerCode' => $ownerCode,
                'associationTypeCode' => $associationTypeCode,
            ]
        )->fetchAssociative();

        return [
            'products' => \json_decode($result['associated_product_identifiers'] ?? '[]', true),
            'product_models' => \json_decode($result['associated_product_model_codes'] ?? '[]', true),
            'groups' => \json_decode($result['associated_group_codes'] ?? '[]', true),
        ];
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
