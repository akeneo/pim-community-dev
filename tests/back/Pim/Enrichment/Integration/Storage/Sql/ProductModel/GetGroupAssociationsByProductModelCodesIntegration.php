<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetGroupAssociationsByProductModelCodes;
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
class GetGroupAssociationsByProductModelCodesIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $entityBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        $this->givenGroup(['groupA', 'groupB', 'groupC', 'groupD', 'groupE', 'groupF', 'groupG']);

        $this->givenTheFollowingProductModelsWithGroupAssociations([
            'root_product_model_1' => [
                'associations' => [
                    'X_SELL' => ['groups' => ['groupF']],
                    'PACK' => ['groups' => ['groupA', 'groupC']],
                ],
                'sub_product_models' => [
                    'sub_product_model_1_1' => [
                        'associations' => [
                            'X_SELL' => ['groups' => ['groupD']],
                            'SUBSTITUTION' => ['groups' => ['groupB']],
                        ],
                    ],
                    'sub_product_model_1_2' => [
                        'associations' => [
                            'PACK' => ['groups' => ['groupG']],
                            'UPSELL' => ['groups' => ['groupE']],
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
                            'PACK' => ['groups' => ['groupC']],
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

    public function testWithMixingAllKindsOfModelsForGroupAssociationsAndInheritance()
    {
        $expected = [
            'root_product_model_1' => [
                'PACK' => ['groups' => ['groupA', 'groupC']],
                'UPSELL' => ['groups' => []],
                'X_SELL' => ['groups' => ['groupF']],
                'A_NEW_TYPE' => ['groups' => []],
                'SUBSTITUTION' => ['groups' => []],
            ],
            'root_product_model_2' => [
                'PACK' => ['groups' => []],
                'UPSELL' => ['groups' => []],
                'X_SELL' => ['groups' => []],
                'A_NEW_TYPE' => ['groups' => []],
                'SUBSTITUTION' => ['groups' => []],
            ],
            'sub_product_model_1_1' => [
                'PACK' => ['groups' => ['groupA', 'groupC']],
                'UPSELL' => ['groups' => []],
                'X_SELL' => ['groups' => ['groupD', 'groupF']],
                'A_NEW_TYPE' => ['groups' => []],
                'SUBSTITUTION' => ['groups' => ['groupB']],
            ],
            'sub_product_model_1_2' => [
                'PACK' => ['groups' => ['groupA', 'groupG', 'groupC']],
                'UPSELL' => ['groups' => ['groupE']],
                'X_SELL' => ['groups' => ['groupF']],
                'A_NEW_TYPE' => ['groups' => []],
                'SUBSTITUTION' => ['groups' => []],
            ],
            'sub_product_model_2_1' => [
                'PACK' => ['groups' => []],
                'UPSELL' => ['groups' => []],
                'X_SELL' => ['groups' => []],
                'A_NEW_TYPE' => ['groups' => []],
                'SUBSTITUTION' => ['groups' => []],
            ],
            'sub_product_model_2_2' => [
                'PACK' => ['groups' => ['groupC']],
                'UPSELL' => ['groups' => []],
                'X_SELL' => ['groups' => []],
                'A_NEW_TYPE' => ['groups' => []],
                'SUBSTITUTION' => ['groups' => []],
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

    private function givenGroup(array $codes): void
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

        $groups = array_map(function (string $code) {
            $group = $this->get('pim_catalog.factory.group')->createGroup('RELATED');
            $this->get('pim_catalog.updater.group')->update($group, [
                'code' => $code
            ]);

            $errors = $this->get('validator')->validate($group);

            Assert::count($errors, 0);

            return $group;
        }, $codes);

        $this->get('pim_catalog.saver.group')->saveAll($groups);
    }

    private function getQuery(): GetGroupAssociationsByProductModelCodes
    {
        return $this->get('akeneo.pim.enrichment.product_model.query.get_group_associations_by_product_model_codes');
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

    private function givenTheFollowingProductModelsWithGroupAssociations(array $productModelsTree, ?ProductModelInterface $parent = null): void
    {
        foreach ($productModelsTree as $productModelCode => $data) {
            $associations = $data['associations'] ?? [];
            $productModel = $this->entityBuilder->createProductModel($productModelCode, 'familyVariantWithTwoLevels', $parent, ['associations' => $associations]);
            $this->givenTheFollowingProductModelsWithGroupAssociations($data['sub_product_models'] ?? [], $productModel);
        }
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
