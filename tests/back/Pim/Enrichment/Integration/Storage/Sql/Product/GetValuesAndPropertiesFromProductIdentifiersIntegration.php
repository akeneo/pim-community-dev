<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetValuesAndPropertiesFromProductIdentifiers;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Webmozart\Assert\Assert;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class GetValuesAndPropertiesFromProductIdentifiersIntegration extends TestCase
{
    private const CREATED = '2012-10-05 22:49:48';
    private const UPDATED = '2012-10-28 23:50:49';

    public function setUp(): void
    {
        parent::setUp();

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
        $subProductModel = $entityBuilder->createProductModel(
            'SubProductModel',
            'familyVariantWithTwoLevels',
            $rootProductModel,
            ['values' => ['second_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]]]]
        );

        $entityBuilder->createProduct('productA', 'family', []);
        $entityBuilder->createProduct('productB', 'family', ['groups' => ['group1', 'group2']]);
        $entityBuilder->createVariantProduct('VariantProductA', 'FamilyWithVariant', 'familyVariantWithTwoLevels', $subProductModel, []);

        $this->getDatabaseConnection()->executeQuery(sprintf(
            'UPDATE pim_catalog_product SET created="%s", updated="%s"',
            self::CREATED,
            self::UPDATED
        ));
    }

    public function testNoProducts()
    {
        $expected = [];
        $actual = $this->getQuery()->fetchByProductIdentifiers([]);
        $this->assertEquals($expected, $actual);
    }

    public function testSingleProductProperties()
    {
        $platform = $this->getDatabaseConnection()->getDatabasePlatform();
        $expected = [
            'productA' => [
                'id' => 'doc: we can not check the id',
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
        $actual = $this->getQuery()->fetchByProductIdentifiers(['productA']);

        Assert::integer($actual['productA']['id']);
        unset($expected['productA']['id'], $actual['productA']['id']);

        $this->assertEquals($expected, $actual);
    }

    public function testGroups()
    {
        $this->assertEquals(
            ['group1', 'group2'],
            $this->getQuery()->fetchByProductIdentifiers(['productB'])['productB']['group_codes']
        );
    }

    public function testVariantProductValues()
    {
        $platform = $this->getDatabaseConnection()->getDatabasePlatform();
        $expected = [
            'VariantProductA' => [
                'id' => 'doc: we can not check the id',
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
        $actual = $this->getQuery()->fetchByProductIdentifiers(['VariantProductA']);

        Assert::integer($actual['VariantProductA']['id']);
        unset($expected['VariantProductA']['id'], $actual['VariantProductA']['id']);

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
        $rootProductModelOneLevel = $entityBuilder->createProductModel(
            'root_product_model_one_level',
            'familyVariantWithOneLevel',
            null,
            []
        );
        $entityBuilder->createVariantProduct(
            'VariantProductWithEmptyValuesFromPM',
            'FamilyWithVariant',
            'familyVariantWithOneLevel',
            $rootProductModelOneLevel,
            [
                'values' => [
                    'first_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]],
                    'second_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]],
                ]
            ]
        );
        $results = $this->getQuery()->fetchByProductIdentifiers(['VariantProductWithEmptyValuesFromPM']);

        $expected = [
            'sku' => ['<all_channels>' => ['<all_locales>' => 'VariantProductWithEmptyValuesFromPM']],
            'first_yes_no' => ['<all_channels>' => ['<all_locales>' => true]],
            'second_yes_no' => ['<all_channels>' => ['<all_locales>' => false]]
        ];

        static::assertArrayHasKey('VariantProductWithEmptyValuesFromPM', $results);
        static::assertEqualsCanonicalizing($expected, $results['VariantProductWithEmptyValuesFromPM']['raw_values']);
    }

    private function getQuery(): GetValuesAndPropertiesFromProductIdentifiers
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_values_and_properties_from_product_identifiers');
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
