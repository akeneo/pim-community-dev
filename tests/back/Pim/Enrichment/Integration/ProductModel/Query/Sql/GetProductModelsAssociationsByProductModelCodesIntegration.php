<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\ProductModel\Query\Sql;

use Akeneo\Pim\Enrichment\Bundle\ProductModel\Query\Sql\GetProductModelsAssociationsByProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Webmozart\Assert\Assert;
use PHPUnit\Framework\Assert as PHPUnitAssert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductModelsAssociationsByProductModelCodesIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $entityBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->entityBuilder = new EntityBuilder($this->testKernel->getContainer());

        $this->givenTheFollowingProductModels([
            'productModelA',
            'productModelB',
            'productModelC',
            'productModelD',
            'productModelE',
            'productModelF',
            'productModelG',
        ]);

        $this->givenTheFollowingProductModelsWithProductModelsAssociations([
            'root_product_model_1' => [
                'associations' => [
                    'X_SELL' => ['product_models' => ['productModelF']],
                    'PACK' => ['product_models' => ['productModelA', 'productModelC']],
                ],
                'sub_product_models' => [
                    'sub_product_model_1_1' => [
                        'associations' => [
                            'X_SELL' => ['product_models' => ['productModelD']],
                            'SUBSTITUTION' => ['product_models' => ['productModelB']],
                        ],
                    ],
                    'sub_product_model_1_2' => [
                        'associations' => [
                            'PACK' => ['product_models' => ['productModelG']],
                            'UPSELL' => ['product_models' => ['productModelE']],
                        ],
                    ],
                ]
            ],
            'root_product_model_2' => [
                'associations' => [],
                'sub_product_models' => [
                    'sub_product_model_2_1' => [
                        'associations' => [],
                    ],
                    'sub_product_model_2_2' => [
                        'associations' => [
                            'PACK' => ['product_models' => ['productModelC']],
                        ],
                    ]
                ]
            ],
        ]);

        $this->givenAssociationTypes(['A_NEW_TYPE']);
    }

    public function testWithoutAnyProductModels(): void
    {
        $expected = [];
        $actual = $this->getQuery()->fromProductModelCodes([]);

        PHPUnitAssert::assertEqualsCanonicalizing($expected, $actual);
    }

    public function testWithMixingAllKindsOfModelsForProductModelsAssociationsAndInheritance()
    {
        $expected = [
            'root_product_model_1' => [
                'PACK' => ['productModelA', 'productModelC'],
                'UPSELL' => [],
                'X_SELL' => ['productModelF'],
                'A_NEW_TYPE' => [],
                'SUBSTITUTION' => [],
            ],
            'root_product_model_2' => [
                'PACK' => [],
                'UPSELL' => [],
                'X_SELL' => [],
                'A_NEW_TYPE' => [],
                'SUBSTITUTION' => [],
            ],
            'sub_product_model_1_1' => [
                'PACK' => ['productModelA', 'productModelC'],
                'UPSELL' => [],
                'X_SELL' => ['productModelD', 'productModelF'],
                'A_NEW_TYPE' => [],
                'SUBSTITUTION' => ['productModelB'],
            ],
            'sub_product_model_1_2' => [
                'PACK' => ['productModelA', 'productModelG', 'productModelC'],
                'UPSELL' => ['productModelE'],
                'X_SELL' => ['productModelF'],
                'A_NEW_TYPE' => [],
                'SUBSTITUTION' => [],
            ],
            'sub_product_model_2_1' => [
                'PACK' => [],
                'UPSELL' => [],
                'X_SELL' => [],
                'A_NEW_TYPE' => [],
                'SUBSTITUTION' => [],
            ],
            'sub_product_model_2_2' => [
                'PACK' => ['productModelC'],
                'UPSELL' => [],
                'X_SELL' => [],
                'A_NEW_TYPE' => [],
                'SUBSTITUTION' => [],
            ]
        ];

        $actual = $this->getQuery()->fromProductModelCodes([
            'root_product_model_1',
            'sub_product_model_1_1',
            'sub_product_model_1_2',
            'root_product_model_2',
            'sub_product_model_2_1',
            'sub_product_model_2_2',
        ]);

        $this->recursiveSort($expected);
        $this->recursiveSort($actual);

        PHPUnitAssert::assertEqualsCanonicalizing($expected, $actual);
    }

    private function recursiveSort(&$array): bool
    {
        foreach ($array as &$value) {
            if (is_array($value)) $this->recursiveSort($value);
        }

        return sort($array);
    }

    private function givenAssociationTypes(array $codes): void
    {
        $associationTypes = array_map(function (string $code) {
            $associationType = $this->get('pim_catalog.factory.association_type')->create();
            $this->get('pim_catalog.updater.association_type')->update($associationType, ['code' => $code]);

            $errors = $this->get('validator')->validate($associationType);

            Assert::count($errors, 0);

            return $associationType;
        }, $codes);

        $this->get('pim_catalog.saver.association_type')->saveAll($associationTypes);
    }

    private function givenTheFollowingProductModels(array $productModelCodes): void
    {
        $this->givenBooleanAttributes(['first_yes_no', 'second_yes_no']);
        $this->givenFamilies([['code' => 'aFamily', 'attribute_codes' => ['first_yes_no', 'second_yes_no']]]);
        $this->entityBuilder->createFamilyVariant(
            [
                'code' => 'familyVariantWithTwoLevels',
                'family' => 'aFamily',
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

        foreach ($productModelCodes as $productModelCode) {
            $this->entityBuilder->createProductModel($productModelCode, 'familyVariantWithTwoLevels', null, []);
        }
    }

    private function getQuery(): GetProductModelsAssociationsByProductModelCodes
    {
        return $this->testKernel->getContainer()->get('akeneo.pim.enrichment.product_model.query.get_models_associations_by_product_model_codes');
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
                'attributes' => array_merge(['sku'], $data['attribute_codes']),
                'attribute_requirements' => ['ecommerce' => ['sku']]
            ]);

            $errors = $this->get('validator')->validate($family);

            Assert::count($errors, 0);

            return $family;
        }, $familiesData);

        $this->get('pim_catalog.saver.family')->saveAll($families);
    }

    private function givenTheFollowingProductModelsWithProductModelsAssociations(array $productModelsTree, ?ProductModelInterface $parent = null): void
    {
        foreach ($productModelsTree as $productModelCode => $data) {
            $associations = $data['associations'] ?? [];
            $productModel = $this->entityBuilder->createProductModel($productModelCode, 'familyVariantWithTwoLevels', $parent, ['associations' => $associations]);
            $this->givenTheFollowingProductModelsWithProductModelsAssociations($data['sub_product_models'] ?? [], $productModel);
        }
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
