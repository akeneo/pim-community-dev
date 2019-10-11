<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetValuesAndPropertiesFromProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetValuesAndPropertiesFromProductModelCodesIntegration extends TestCase
{
    private const CREATED = '2012-10-05 22:49:48';
    private const UPDATED = '2012-10-28 23:50:49';

    /** @var EntityBuilder */
    private $entityBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        $this->givenTheFollowingProductModelsWithValues([
            'root_product_model_1' => [
                'data' => [
                    'values' => ['first_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]]]
                ],
                'sub_product_models' => [
                    'sub_product_model_1_1' => [
                        'data' => [
                            'values' => ['second_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]]]
                        ]
                    ],
                ]
            ],
            'root_product_model_2' => [
                'data' => [
                    'values' => ['first_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]]]
                ],
                'sub_product_models' => [
                    'sub_product_model_2_1' => [
                        'data' => [
                            'values' => ['second_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]]]
                        ]
                    ],
                ]
            ],
        ]);

        $this->getDatabaseConnection()->executeQuery(sprintf(
            'UPDATE pim_catalog_product_model SET created="%s", updated="%s"',
            self::CREATED,
            self::UPDATED
        ));
    }

    public function testNoProductModels()
    {
        $expected = [];
        $actual = $this->getQuery()->fromProductModelCodes([]);
        $this->assertEquals($expected, $actual);
    }

    public function testMultipleProductModels()
    {
        $platform = $this->getDatabaseConnection()->getDatabasePlatform();
        $expected = [
            'root_product_model_1' => [
                'code' => 'root_product_model_1',
                'family' => 'FamilyWithVariant',
                'family_variant' => 'familyVariantWithTwoLevels',
                'parent' => null,
                'raw_values' => [
                    'first_yes_no' => ['<all_channels>' => ['<all_locales>' => false]]
                ],
                'created' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPhpValue(self::CREATED, $platform),
                'updated' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPhpValue(self::UPDATED, $platform),
            ],
            'sub_product_model_1_1' => [
                'code' => 'sub_product_model_1_1',
                'family' => 'FamilyWithVariant',
                'family_variant' => 'familyVariantWithTwoLevels',
                'parent' => 'root_product_model_1',
                'raw_values' => [
                    'first_yes_no' => ['<all_channels>' => ['<all_locales>' => false]],
                    'second_yes_no' => ['<all_channels>' => ['<all_locales>' => true]],
                ],
                'created' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPhpValue(self::CREATED, $platform),
                'updated' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPhpValue(self::UPDATED, $platform),
            ],
            'root_product_model_2' => [
                'code' => 'root_product_model_2',
                'family' => 'FamilyWithVariant',
                'family_variant' => 'familyVariantWithTwoLevels',
                'parent' => null,
                'raw_values' => [
                    'first_yes_no' => ['<all_channels>' => ['<all_locales>' => true]]
                ],
                'created' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPhpValue(self::CREATED, $platform),
                'updated' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPhpValue(self::UPDATED, $platform),
            ],
            'sub_product_model_2_1' => [
                'code' => 'sub_product_model_2_1',
                'family' => 'FamilyWithVariant',
                'family_variant' => 'familyVariantWithTwoLevels',
                'parent' => 'root_product_model_2',
                'raw_values' => [
                    'first_yes_no' => ['<all_channels>' => ['<all_locales>' => true]],
                    'second_yes_no' => ['<all_channels>' => ['<all_locales>' => false]],
                ],
                'created' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPhpValue(self::CREATED, $platform),
                'updated' => Type::getType(Type::DATETIME_IMMUTABLE)->convertToPhpValue(self::UPDATED, $platform),
            ],

        ];
        $actual = $this->getQuery()->fromProductModelCodes([
            'root_product_model_1',
            'sub_product_model_1_1',
            'root_product_model_2',
            'sub_product_model_2_1',
        ]);

        foreach ($actual as $productModelCode => $propertiesAndValues) {
            Assert::true(isset($propertiesAndValues['id']));
            Assert::integer($propertiesAndValues['id']);
            unset($actual[$productModelCode]['id']);
        }

        $this->assertEquals($expected, $actual);
    }

    private function getQuery(): GetValuesAndPropertiesFromProductModelCodes
    {
        return $this->get('akeneo.pim.enrichment.product_model.query.get_values_and_properties_from_product_model_codes');
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

    private function givenTheFollowingProductModelsWithValues(array $productModelsTree = [], ?ProductModelInterface $parent = null, bool $firstPass = true) {
        if ($firstPass) {
            $family = $this->get('pim_catalog.factory.family')->create();
            $this->get('pim_catalog.updater.family')->update($family, [
                'code' => 'family'
            ]);
            $this->get('pim_catalog.saver.family')->save($family);

            $this->givenBooleanAttributes(['first_yes_no', 'second_yes_no']);
            $this->givenFamilies([['code' => 'FamilyWithVariant', 'attribute_codes' => ['first_yes_no', 'second_yes_no']]]);
            $this->entityBuilder->createFamilyVariant(
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
        }

        foreach ($productModelsTree as $productModelCode => $data) {
            $productModel = $this->entityBuilder->createProductModel($productModelCode, 'familyVariantWithTwoLevels', $parent, $data['data'] ?? []);
            $this->givenTheFollowingProductModelsWithValues($data['sub_product_models'] ?? [], $productModel, false);
        }
    }
}
