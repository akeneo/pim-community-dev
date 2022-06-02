<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product\Association;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductAssociationsByProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductAssociationsByProductUuidsIntegration extends TestCase
{
    /**
     * @var ProductInterface[] ['productIdentifier' => $product]
     */
    private array $productList;
    public function setUp(): void
    {
        parent::setUp();
        $this->messageBus = $this->get('pim_enrich.product.message_bus');

        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $this->givenBooleanAttributes(['first_yes_no', 'second_yes_no']);
        $this->givenFamilies([['code' => 'aFamily', 'attribute_codes' => ['first_yes_no', 'second_yes_no']]]);
        $entityBuilder->createFamilyVariant(
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

        $this->productList['productA'] = $this->createProduct('productA', [new SetFamily('aFamily')]);
        $this->productList['productB'] = $this->createProduct('productB',[new SetFamily('aFamily')]);
        $this->productList['productC'] = $this->createProduct(
            'productC',
            [
                new SetFamily('aFamily'),
                new AssociateProducts('UPSELL', ['productA'])
            ]
        );
        $this->productList['productD'] = $this->createProduct(
            'productD',
            [
                new SetFamily('aFamily'),
                new AssociateProducts('X_SELL', ['productA', 'productB']),
                new AssociateProducts('PACK', ['productC'])
            ]
        );
        $this->productList['productE'] = $this->createProduct('productE', [new SetFamily('aFamily')]);
        $this->productList['productF'] = $this->createProduct('productF', [new SetFamily('aFamily')]);
        $this->productList['productG'] = $this->createProduct('productG', [new SetFamily('aFamily')]);

        $rootProductModel = $entityBuilder->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, $this->getAssociationsFormatted(['productF'], ['productA', 'productC']));
        $entityBuilder->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, $this->getAssociationsFormatted(['productD'], [], ['productB']));

        $this->productList['variant_product_1'] = $this->createProduct(
            'variant_product_1',
            [
                new SetFamily('aFamily'),
                new ChangeParent('sub_product_model_1'),
                new SetBooleanValue('second_yes_no', null, null, false),
                new AssociateProducts('X_SELL', ['productF']),
                new AssociateProducts('PACK', ['productG']),
                new AssociateProducts('UPSELL', ['productE']),
            ]
        );

        $this->givenAssociationTypes(['A_NEW_TYPE']);
    }

    public function testWithAProductContainingNoAssociation()
    {
        $expected = ['productE' => $this->getAssociationsFormattedAfterFetch()];
        $actual = $this->getQuery()->fetchByProductUuids([$this->productList['productE']->getUuid()]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnASingleProduct()
    {
        $expected = [
            'productD' => $this->getAssociationsFormattedAfterFetch(
                [
                    $this->productList['productA']->getUuid()->toString(),
                    $this->productList['productB']->getUuid()->toString()
                ],
                [$this->productList['productC']->getUuid()->toString()])
        ];
        $actual = $this->getQuery()->fetchByProductUuids([$this->productList['productD']->getUuid()]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnMultipleSimpleProduct()
    {
        $expected = [
            'productE' => $this->getAssociationsFormattedAfterFetch(),
            'productD' => $this->getAssociationsFormattedAfterFetch(
                [
                    $this->productList['productA']->getUuid()->toString(),
                    $this->productList['productB']->getUuid()->toString()
                ],
                [$this->productList['productC']->getUuid()->toString()]
            ),
            'productC' => $this->getAssociationsFormattedAfterFetch([], [], [], [$this->productList['productA']->getUuid()->toString()]),
        ];
        $actual = $this->getQuery()->fetchByProductUuids([
            $this->productList['productE']->getUuid(),
            $this->productList['productC']->getUuid(),
            $this->productList['productD']->getUuid()
        ]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnMultipleWithProductModels()
    {
        $expected = [
            'productE' => $this->getAssociationsFormattedAfterFetch(),
            'productC' => $this->getAssociationsFormattedAfterFetch(
                [],
                [],
                [],
                [$this->productList['productA']->getUuid()->toString()]
            ),
            'productD' => $this->getAssociationsFormattedAfterFetch(
                [
                    $this->productList['productA']->getUuid()->toString(),
                    $this->productList['productB']->getUuid()->toString()
                ],
                [$this->productList['productC']->getUuid()->toString()]
            ),
            'variant_product_1' => $this->getAssociationsFormattedAfterFetch(
                [
                    $this->productList['productF']->getUuid()->toString(),
                    $this->productList['productD']->getUuid()->toString()
                ],
                [
                    $this->productList['productA']->getUuid()->toString(),
                    $this->productList['productC']->getUuid()->toString(),
                    $this->productList['productG']->getUuid()->toString()
                ],
                [
                    $this->productList['productB']->getUuid()->toString()
                ],
                [
                    $this->productList['productE']->getUuid()->toString()
                ]
            ),
        ];
        $actual = $this->getQuery()->fetchByProductUuids([
            $this->productList['productE']->getUuid(),
            $this->productList['productC']->getUuid(),
            $this->productList['productD']->getUuid(),
            $this->productList['variant_product_1']->getUuid()
        ]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function getQuery(): GetProductAssociationsByProductUuids
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_associations_by_product_uuids');
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

    private function givenBooleanAttributes(array $codes): void
    {
        $attributes = array_map(function (string $code) {
            $data = [
                'code' => $code,
                'type' => AttributeTypes::BOOLEAN,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
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

    private function getAssociationsFormatted(array $crossSell = [], array $pack = [], array $substitutions = [], array $upsell = [], array $aNewType = [])
    {
        return ['associations' => [
            'X_SELL' => ['products' => $crossSell],
            'PACK' => ['products' => $pack],
            'SUBSTITUTION' => ['products' => $substitutions],
            'UPSELL' => ['products' => $upsell],
        ]];
    }

    private function getAssociationsFormattedAfterFetch(array $crossSell = [], array $pack = [], array $substitutions = [], array $upsell = [], array $aNewType = []): array
    {
        return [
            'X_SELL' => ['products' => $crossSell],
            'PACK' => ['products' => $pack],
            'SUBSTITUTION' => ['products' => $substitutions],
            'UPSELL' => ['products' => $upsell],
            'A_NEW_TYPE' => ['products' => $aNewType]
        ];
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
