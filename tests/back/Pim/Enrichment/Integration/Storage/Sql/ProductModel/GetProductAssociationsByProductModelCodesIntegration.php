<?php


declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetProductAssociationsByProductModelCodes;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;
use PHPUnit\Framework\Assert as PHPUnitAssert;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductAssociationsByProductModelCodesIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_returns_empty_for_product_model_with_no_associations()
    {
        $result = $this->getQuery()->fetchByProductModelCodes(['product_model_with_no_associations']);

        PHPUnitAssert::assertEqualsCanonicalizing([
            'product_model_with_no_associations' => $this->getAssociationsFormattedAfterFetch()
        ], $result);
    }

    /**
     * @test
     */
    public function it_returns_associations_for_a_product_models()
    {
        $result = $this->getQuery()->fetchByProductModelCodes([
            'product_model_with_one_association',
            'product_model_with_multiple_associations'
        ]);

        PHPUnitAssert::assertEqualsCanonicalizing([
            'product_model_with_one_association' => $this->getAssociationsFormattedAfterFetch(['productA']),
            'product_model_with_multiple_associations' => $this->getAssociationsFormattedAfterFetch(['productB'], ['productA', 'productF'])
        ], $result);
    }

    /**
     * @test
     */
    public function it_returns_inherited_associations_of_product_models()
    {
        $result = $this->getQuery()->fetchByProductModelCodes([
            'root_product_model',
            'sub_product_model'
        ]);

        $expected = [
            'root_product_model' => $this->getAssociationsFormattedAfterFetch(['productF'], ['productA', 'productC']),
            'sub_product_model' => $this->getAssociationsFormattedAfterFetch(['productD', 'productF'], ['productC', 'productA'], ['productB'])
        ];

        PHPUnitAssert::assertEqualsCanonicalizing(
            $this->recursiveSort($expected),
            $this->recursiveSort($result)
        );
    }

    public function setUp(): void
    {
        parent::setUp();

        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $this->givenBooleanAttributes(['first_yes_no']);
        $this->givenFamilies([['code' => 'aFamily', 'attribute_codes' => ['first_yes_no']]]);
        $entityBuilder->createFamilyVariant(
            [
                'code' => 'familyVariant',
                'family' => 'aFamily',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['first_yes_no'],
                        'attributes' => [],
                    ]
                ],
            ]
        );

        $entityBuilder->createProductModel('product_model_with_no_associations', 'familyVariant', null, []);
        $entityBuilder->createProduct('productA', 'aFamily', []);
        $entityBuilder->createProduct('productB', 'aFamily', []);
        $entityBuilder->createProduct('productC', 'aFamily', $this->getAssociationsFormatted([], [], [], ['productA']));
        $entityBuilder->createProduct('productD', 'aFamily', $this->getAssociationsFormatted(['productA', 'productB'], ['productC']));
        $entityBuilder->createProduct('productE', 'aFamily', []);
        $entityBuilder->createProduct('productF', 'aFamily', []);
        $entityBuilder->createProduct('productG', 'aFamily', []);

        $entityBuilder->createProductModel('product_model_with_one_association', 'familyVariant', null, $this->getAssociationsFormatted(['productA']));
        $entityBuilder->createProductModel('product_model_with_multiple_associations', 'familyVariant', null, $this->getAssociationsFormatted(['productB'], ['productA', 'productF']));
        $rootProductModel = $entityBuilder->createProductModel('root_product_model', 'familyVariant', null, $this->getAssociationsFormatted(['productF'], ['productA', 'productC']));
        $entityBuilder->createProductModel('sub_product_model', 'familyVariant', $rootProductModel, $this->getAssociationsFormatted(['productD'], [], ['productB']));
    }

    private function getQuery(): GetProductAssociationsByProductModelCodes
    {
        return $this->get('akeneo.pim.enrichment.product_model.query.get_product_associations_by_product_model_codes');
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

    private function getAssociationsFormatted(array $crossSell = [], array $pack = [], array $substitutions = [], array $upsell = [])
    {
        return ['associations' => [
            'X_SELL' => ['products' => $crossSell],
            'PACK' => ['products' => $pack],
            'SUBSTITUTION' => ['products' => $substitutions],
            'UPSELL' => ['products' => $upsell],
        ]];
    }


    private function recursiveSort(&$array): bool
    {
        foreach ($array as &$value) {
            if (is_array($value)) $this->recursiveSort($value);
        }

        return sort($array);
    }

    private function getAssociationsFormattedAfterFetch(array $crossSell = [], array $pack = [], array $substitutions = [], array $upsell = []): array
    {
        return [
            'X_SELL' => ['products' => $crossSell],
            'PACK' => ['products' => $pack],
            'SUBSTITUTION' => ['products' => $substitutions],
            'UPSELL' => ['products' => $upsell],
        ];
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
