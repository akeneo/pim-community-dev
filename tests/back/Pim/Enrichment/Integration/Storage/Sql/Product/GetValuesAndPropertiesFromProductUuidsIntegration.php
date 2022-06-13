<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetValuesAndPropertiesFromProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetValuesAndPropertiesFromProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @author Adrien Migaire <adrien.migaire@akeneo.com>
 */
class GetValuesAndPropertiesFromProductUuidsIntegration extends TestCase
{
    private const CREATED = '2012-10-05 22:49:48';
    private const UPDATED = '2012-10-28 23:50:49';

    private $productList = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->messageBus = $this->get('pim_enrich.product.message_bus');

        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, [
            'code' => 'family'
        ]);
        $this->get('pim_catalog.saver.family')->save($family);

        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $this->givenBooleanAttributes(['first_yes_no', 'second_yes_no']);
        $this->givenFamilies([['code' => 'FamilyWithVariant', 'attribute_codes' => ['first_yes_no', 'second_yes_no']]]);
        $this->givenGroups(['group1', 'group2']);
        $entityBuilder->createFamilyVariant(
            [
                'code' => 'familyVariantWithTwoLevels',
                'family' => 'FamilyWithVariant',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['first_yes_no'],
                        'attributes' => [],
                    ],
                    [
                        'level' => 2,
                        'axes' => ['second_yes_no'],
                        'attributes' => [],
                    ],
                ],
            ]
        );
        $rootProductModel = $entityBuilder->createProductModel(
            'root_product_model',
            'familyVariantWithTwoLevels',
            null,
            ['values' => ['first_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]]]]
        );
        $entityBuilder->createProductModel(
            'SubProductModel',
            'familyVariantWithTwoLevels',
            $rootProductModel,
            ['values' => ['second_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]]]]
        );

        $userId = ($this->getUserId('admin') !== 0)
            ? $this->getUserId('admin')
            : $this->createAdminUser()->getId();

        $this->productList['productA'] = $this->createProduct(
            'productA',
            [
                new SetFamily('family'),
            ],
            $userId
        );

        $this->productList['productB'] = $this->createProduct(
            'productB',
            [
                new SetFamily('family'),
                new SetBooleanValue('second_yes_no', null, null, false),
                new SetGroups(['group1', 'group2']),
            ],
            $userId
        );

        $this->productList['VariantProductA'] = $this->createProduct(
            'VariantProductA',
            [
                new SetFamily('FamilyWithVariant'),
                new ChangeParent('SubProductModel'),
                new SetBooleanValue('second_yes_no', null, null, true),
            ],
            $userId
        );

        $this->getDatabaseConnection()->executeQuery(sprintf(
            'UPDATE pim_catalog_product SET created="%s", updated="%s"',
            self::CREATED,
            self::UPDATED
        ));
    }

    public function testNoProducts()
    {
        $expected = [];
        $actual = $this->getQuery()->fetchByProductUuids([]);
        $this->assertEquals($expected, $actual);
    }

    public function testSingleProductProperties()
    {
        $platform = $this->getDatabaseConnection()->getDatabasePlatform();
        $expected = [
            $this->productList['productA']->getUuid()->toString() => [
                'uuid' => $this->productList['productA']->getUuid(),
                'identifier' => 'productA',
                'is_enabled' => true,
                'product_model_code' => null,
                'created' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPhpValue(self::CREATED, $platform),
                'updated' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPhpValue(self::UPDATED, $platform),
                'family_code' => 'family',
                'group_codes' => [],
                'raw_values' => [
                    'sku' => ['<all_channels>' => ['<all_locales>' => 'productA']]
                ]
            ]
        ];
        $actual = $this->getQuery()->fetchByProductUuids([$this->productList['productA']->getUuid()]);

        Assert::isInstanceOf($actual[$this->productList['productA']->getUuid()->toString()]['uuid'], UuidInterface::class);

        $this->assertEquals($expected, $actual);
    }

    public function testGroups()
    {
        $this->assertEquals(
            ['group1', 'group2'],
            $this->getQuery()->fetchByProductUuids([$this->productList['productB']->getUuid()])[$this->productList['productB']->getUuid()->toString()]['group_codes']
        );
    }

    public function testVariantProductValues()
    {
        $platform = $this->getDatabaseConnection()->getDatabasePlatform();
        $expected = [
            $this->productList['VariantProductA']->getUuid()->toString() => [
                'uuid' => $this->productList['VariantProductA']->getUuid(),
                'identifier' => 'VariantProductA',
                'is_enabled' => true,
                'product_model_code' => 'SubProductModel',
                'created' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPhpValue(self::CREATED, $platform),
                'updated' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPhpValue(self::UPDATED, $platform),
                'family_code' => 'FamilyWithVariant',
                'group_codes' => [],
                'raw_values' => [
                    'first_yes_no' => ['<all_channels>' => ['<all_locales>' => false]],
                    'sku' => ['<all_channels>' => ['<all_locales>' => 'VariantProductA']],
                    'second_yes_no' => ['<all_channels>' => ['<all_locales>' => true]]
                ]
            ]
        ];
        $actual = $this->getQuery()->fetchByProductUuids([$this->productList['VariantProductA']->getUuid()]);

        Assert::isInstanceOf($actual[$this->productList['VariantProductA']->getUuid()->toString()]['uuid'], UuidInterface::class);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    /**
     * Test that the merging of raw_values works as intended even if raw_values from parents are empty
     * @todo TIP-1231: remove this test
     */
    public function testVariantProductWithEmptyValuesFromParent()
    {
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $entityBuilder->createFamilyVariant(
            [
                'code' => 'familyVariantWithOneLevel',
                'family' => 'FamilyWithVariant',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['first_yes_no', 'second_yes_no'],
                        'attributes' => [],
                    ],
                ],
            ]
        );
        $entityBuilder->createProductModel(
            'root_product_model_one_level',
            'familyVariantWithOneLevel',
            null,
            []
        );

        $variantProduct = $this->createProduct(
            'VariantProductWithEmptyValuesFromPM',
            [
                new SetFamily('FamilyWithVariant'),
                new ChangeParent('root_product_model_one_level'),
                new SetBooleanValue('first_yes_no', null, null, true),
                new SetBooleanValue('second_yes_no', null, null, false),
            ],
            $this->getUserId('admin')
        );

        $results = $this->getQuery()->fetchByProductUuids([$variantProduct->getUuid()]);

        $expected = [
            'sku' => ['<all_channels>' => ['<all_locales>' => 'VariantProductWithEmptyValuesFromPM']],
            'first_yes_no' => ['<all_channels>' => ['<all_locales>' => true]],
            'second_yes_no' => ['<all_channels>' => ['<all_locales>' => false]]
        ];

        static::assertArrayHasKey($variantProduct->getUuid()->toString(), $results);
        static::assertEqualsCanonicalizing($expected, $results[$variantProduct->getUuid()->toString()]['raw_values']);
    }

    private function getQuery(): GetValuesAndPropertiesFromProductUuids
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_values_and_properties_from_product_uuids');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getDatabaseConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function givenBooleanAttributes(array $codes): void
    {
        $attributes = array_map(function (string $code) {
            $data = [
                'code' => $code,
                'type' => AttributeTypes::BOOLEAN,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other'
            ];
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
            $constraints = $this->get('validator')->validate($attribute);
            Assert::count($constraints, 0);

            return $attribute;
        }, $codes);
        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function givenFamilies(array $familiesData): void
    {
        $families = array_map(function ($data) {
            $family = $this->get('pim_catalog.factory.family')->create();
            $this->get('pim_catalog.updater.family')->update($family, [
                'code' => $data['code'],
                'attributes'  => array_merge(['sku'], $data['attribute_codes']),
                'attribute_requirements' => ['ecommerce' => ['sku']]
            ]);
            $errors = $this->get('validator')->validate($family);
            Assert::count($errors, 0);

            return $family;
        }, $familiesData);
        $this->get('pim_catalog.saver.family')->saveAll($families);
    }

    private function givenGroups(array $groupCodes): void
    {
        $groups = array_map(function ($groupCode) {
            $group = $this->get('pim_catalog.factory.group')->create();
            $this->get('pim_catalog.updater.group')->update($group, [
                'code' => $groupCode,
                'type' => 'RELATED'
            ]);
            $errors = $this->get('validator')->validate($group);
            Assert::count($errors, 0);

            return $group;
        }, $groupCodes);
        $this->get('pim_catalog.saver.group')->saveAll($groups);
    }
}
