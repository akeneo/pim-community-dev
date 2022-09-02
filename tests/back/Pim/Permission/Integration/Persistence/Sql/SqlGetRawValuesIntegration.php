<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\Sql;

use Akeneo\Pim\Permission\Bundle\Persistence\Sql\SqlGetRawValues;
use Akeneo\Pim\Permission\Component\Query\GetRawValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class SqlGetRawValuesIntegration extends TestCase
{
    private SqlGetRawValues $sqlGetRawValues;
    private EntityBuilder $entityBuilder;

    private int $rootProductModelId;
    private int $subProductModelId;
    private UuidInterface $variantProductUuid;

    /** @test */
    public function it_returns_the_raw_values_of_product_models(): void
    {
        self::assertEqualsCanonicalizing(
            ['first_yes_no' => ['<all_channels>' => ['<all_locales>' => false]]],
            $this->sqlGetRawValues->forProductModelId($this->rootProductModelId)
        );
        self::assertEqualsCanonicalizing(
            [
                'first_yes_no' => ['<all_channels>' => ['<all_locales>' => false]],
                'second_yes_no' => ['<all_channels>' => ['<all_locales>' => false]],
            ],
            $this->sqlGetRawValues->forProductModelId($this->subProductModelId)
        );
        self::assertNull($this->sqlGetRawValues->forProductModelId(0));
    }

    /** @test */
    public function it_returns_the_nothing_if_product_does_not_exist(): void
    {
        self::assertEqualsCanonicalizing(
            [
                'sku' => ['<all_channels>' => ['<all_locales>' => 'variant_product']],
                'first_yes_no' => ['<all_channels>' => ['<all_locales>' => false]],
                'second_yes_no' => ['<all_channels>' => ['<all_locales>' => false]],
            ],
            $this->sqlGetRawValues->forProductUuid($this->variantProductUuid)
        );

        self::assertNull($this->sqlGetRawValues->forProductUuid(Uuid::uuid4()));
    }

    /** @test */
    public function it_returns_the_raw_values_of_a_simple_product(): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, [
            'code' => 'family',
            'attributes'  => ['sku', 'first_yes_no', 'second_yes_no'],
        ]);
        $this->get('pim_catalog.saver.family')->save($family);
        $product = $this->entityBuilder->createProduct('simple_product', 'family', [
            'values' => [
                'first_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]],
                'second_yes_no' => [['data' => true, 'locale' => null, 'scope' => null]],
            ],
        ]);
        self::assertEqualsCanonicalizing(
            [
                'sku' => ['<all_channels>' => ['<all_locales>' => 'simple_product']],
                'first_yes_no' => ['<all_channels>' => ['<all_locales>' => true]],
                'second_yes_no' => ['<all_channels>' => ['<all_locales>' => true]],
            ],
            $this->sqlGetRawValues->forProductUuid($product->getUuid())
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlGetRawValues = $this->get(GetRawValues::class);
        $this->entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

        $this->createAttributes(['first_yes_no', 'second_yes_no']);
        $this->createFamilyAndFamilyVariant('FamilyWithVariant', 'familyVariantWithTwoLevels');

        $rootProductModel = $this->entityBuilder->createProductModel(
            'root_pm',
            'familyVariantWithTwoLevels',
            null,
            ['values' => ['first_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]]]]
        );
        $this->rootProductModelId = $rootProductModel->getId();
        $subProductModel = $this->entityBuilder->createProductModel(
            'sub_pm',
            'familyVariantWithTwoLevels',
            $rootProductModel,
            ['values' => ['second_yes_no' => [['data' => false, 'locale' => null, 'scope' => null]]]]
        );
        $this->subProductModelId = $subProductModel->getId();

        $variant = $this->entityBuilder->createVariantProduct(
            'variant_product',
            'FamilyWithVariant',
            'familyVariantWithTwoLevels',
            $subProductModel,
            ['values' => ['sku' => [['data' => 'variant_product', 'locale' => null, 'scope' => null]]]]
        );
        $this->variantProductUuid = $variant->getUuid();
    }

    private function createAttributes(array $attributeCodes): void
    {
        $attributes = [];
        foreach ($attributeCodes as $code) {
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
            self::assertCount(0, $constraints);
            $attributes[] = $attribute;
        }
        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function createFamilyAndFamilyVariant(string $familyCode, string $familyVariantcode): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, [
            'code' => $familyCode,
            'attributes'  => ['sku', 'first_yes_no', 'second_yes_no'],
            'attribute_requirements' => ['ecommerce' => ['sku']],
        ]);
        $errors = $this->get('validator')->validate($family);
        self::assertCount(0, $errors);

        $this->get('pim_catalog.saver.family')->save($family);

        $this->entityBuilder->createFamilyVariant([
            'code' => $familyVariantcode,
            'family' => $familyCode,
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
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
