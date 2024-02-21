<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Product\Association;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductAssociationsByProductUuids;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductAssociationsByProductUuidsIntegration extends TestCase
{
    private $uuids = [];

    public function setUp(): void
    {
        parent::setUp();

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

        $this->uuids['noSku'] = $entityBuilder->createProduct(null, 'aFamily', [])->getUuid();
        $this->uuids['productA'] = $entityBuilder->createProduct('productA', 'aFamily', [])->getUuid();
        $this->uuids['productB'] = $entityBuilder->createProduct('productB', 'aFamily', [])->getUuid();
        $this->uuids['productC'] = $entityBuilder->createProduct('productC', 'aFamily', $this->getAssociationsFormatted([], [], [], [$this->uuids['productA']->toString()]))->getUuid();
        $this->uuids['productD'] = $entityBuilder->createProduct('productD', 'aFamily', $this->getAssociationsFormatted([$this->uuids['productA']->toString(), $this->uuids['productB']->toString()], [$this->uuids['productC']->toString()]))->getUuid();
        $this->uuids['productE'] = $entityBuilder->createProduct('productE', 'aFamily', [])->getUuid();
        $this->uuids['productF'] = $entityBuilder->createProduct('productF', 'aFamily', [])->getUuid();
        $this->uuids['productG'] = $entityBuilder->createProduct('productG', 'aFamily', [])->getUuid();
        $rootProductModel = $entityBuilder->createProductModel('root_product_model', 'familyVariantWithTwoLevels', null, $this->getAssociationsFormatted([$this->uuids['productF']->toString()], [$this->uuids['productA']->toString(), $this->uuids['productC']->toString()]));
        $subProductModel1 = $entityBuilder->createProductModel('sub_product_model_1', 'familyVariantWithTwoLevels', $rootProductModel, $this->getAssociationsFormatted([$this->uuids['productD']->toString()], [], [$this->uuids['productB']->toString()]));
        $this->uuids['variant1'] = $entityBuilder->createVariantProduct('variant_product_1', 'aFamily', 'familyVariantWithTwoLevels', $subProductModel1, $this->getAssociationsFormatted([$this->uuids['productF']->toString()], [$this->uuids['productG']->toString()], [], [$this->uuids['productE']->toString()]))->getUuid();
        $this->uuids['variantNoSku'] = $entityBuilder->createVariantProduct(null, 'aFamily', 'familyVariantWithTwoLevels', $subProductModel1, $this->getAssociationsFormatted([$this->uuids['noSku']->toString()]))->getUuid();

        $this->givenAssociationTypes(['A_NEW_TYPE']);
    }

    public function testWithAProductContainingNoAssociation(): void
    {
        $expected = [$this->uuids['productE']->toString() => $this->getAssociationsFormattedAfterFetch()];
        $actual = $this->getQuery()->fetchByProductUuids([$this->uuids['productE']]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnASingleProduct(): void
    {
        $expected = [
            $this->uuids['productD']->toString() => $this->getAssociationsFormattedAfterFetch([$this->uuids['productA'], $this->uuids['productB']], [$this->uuids['productC']])
        ];
        $actual = $this->getQuery()->fetchByProductUuids([$this->uuids['productD']]);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testOnMultipleSimpleProduct(): void
    {
        $expected = [
            $this->uuids['productE']->toString() => $this->getAssociationsFormattedAfterFetch(),
            $this->uuids['productD']->toString() => $this->getAssociationsFormattedAfterFetch([$this->uuids['productA'], $this->uuids['productB']], [$this->uuids['productC']]),
            $this->uuids['productC']->toString() => $this->getAssociationsFormattedAfterFetch([], [], [], [$this->uuids['productA']]),
        ];
        $actual = $this->getQuery()->fetchByProductUuids([$this->uuids['productE'], $this->uuids['productC'], $this->uuids['productD']]);

        $this->assertEqualsCanonicalizing(\array_keys($actual), \array_keys($expected));
        foreach ($actual as $productUuid => $actualAssociations) {
            $expectedAssociations = $expected[$productUuid];
            $this->assertEqualsCanonicalizing($actualAssociations, $expectedAssociations);
        }
    }

    public function testOnMultipleWithProductModels(): void
    {
        $expected = [
            $this->uuids['productE']->toString() => $this->getAssociationsFormattedAfterFetch(),
            $this->uuids['productD']->toString() => $this->getAssociationsFormattedAfterFetch([$this->uuids['productA'], $this->uuids['productB']], [$this->uuids['productC']]),
            $this->uuids['productC']->toString() => $this->getAssociationsFormattedAfterFetch([], [], [], [$this->uuids['productA']]),
            $this->uuids['variant1']->toString() => $this->getAssociationsFormattedAfterFetch([$this->uuids['productF'], $this->uuids['productD']], [$this->uuids['productA'], $this->uuids['productC'], $this->uuids['productG']], [$this->uuids['productB']], [$this->uuids['productE']]),
        ];
        ksort($expected);
        $actual = $this->getQuery()->fetchByProductUuids([$this->uuids['productE'], $this->uuids['productC'], $this->uuids['productD'], $this->uuids['variant1']]);

        $this->assertEqualsCanonicalizing(\array_keys($actual), \array_keys($expected));
        foreach ($actual as $productUuid => $actualAssociations) {
            $expectedAssociations = $expected[$productUuid];
            $this->assertEqualsCanonicalizing($actualAssociations, $expectedAssociations);
        }
    }

    public function testOnAssociatedProductsWithoutIdentifier(): void
    {
        $expected = [
            $this->uuids['variantNoSku']->toString() => $this->getAssociationsFormattedAfterFetch(
                [$this->uuids['noSku'], $this->uuids['productD'], $this->uuids['productF']],
                [$this->uuids['productA'], $this->uuids['productC']],
                [$this->uuids['productB']]
            ),
        ];

        $actual = $this->getQuery()->fetchByProductUuids([$this->uuids['variantNoSku']]);
        $this->assertEqualsCanonicalizing(\array_keys($actual), \array_keys($expected));
        foreach ($actual as $productUuid => $actualAssociations) {
            $expectedAssociations = $expected[$productUuid];
            $this->assertEqualsCanonicalizing($actualAssociations, $expectedAssociations);
        }
    }

    private function getQuery(): GetProductAssociationsByProductUuids
    {
        return $this->get('Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\Association\GetProductAssociationsByProductUuids');
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
            'X_SELL' => ['product_uuids' => $crossSell],
            'PACK' => ['product_uuids' => $pack],
            'SUBSTITUTION' => ['product_uuids' => $substitutions],
            'UPSELL' => ['product_uuids' => $upsell],
        ]];
    }

    private function getAssociationsFormattedAfterFetch(array $crossSell = [], array $pack = [], array $substitutions = [], array $upsell = [], array $aNewType = []): array
    {
        return [
            'X_SELL' => ['products' => $this->formatAssociation($crossSell)],
            'PACK' => ['products' => $this->formatAssociation($pack)],
            'SUBSTITUTION' => ['products' => $this->formatAssociation($substitutions)],
            'UPSELL' => ['products' => $this->formatAssociation($upsell)],
            'A_NEW_TYPE' => ['products' => $this->formatAssociation($aNewType)],
        ];
    }

    /**
     * @param UuidInterface[] $uuids
     */
    private function formatAssociation(array $uuids): array
    {
        return array_map(fn (UuidInterface $uuid): array => ['uuid' => $uuid->toString(), 'identifier' => $this->getProductIdentifier($uuid)], $uuids);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
