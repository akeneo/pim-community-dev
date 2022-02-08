<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\back\Test\Integration;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

abstract class EnrichmentProductTestCase extends TestCase
{
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function loadEnrichmentProductFunctionalFixtures(): void
    {
        $this->createCategory(['code' => 'print']);
        $this->createCategory(['code' => 'suppliers']);
        $this->createCategory(['code' => 'sales']);

        $this->createAttribute('name', ['type' => AttributeTypes::TEXT]);
        $this->createAttribute('sub_name', ['type' => AttributeTypes::TEXT]);
        $this->createAttribute('main_color', ['type' => AttributeTypes::OPTION_SIMPLE_SELECT]);
        $this->createAttributeOptions('main_color', ['red', 'blue', 'green', 'white']);

        $this->createFamily('accessories', ['attributes' => ['name', 'sub_name', 'main_color']]);
        $this->createFamilyVariant('color_variant_accessories', 'accessories', [
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['main_color'],
                    'attributes' => [],
                ],
            ],
        ]);
    }

    protected function createProduct(string $identifier, array $data): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $violations = $this->get('pim_catalog.validator.product')->validate($product);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.product')->save($product);
    }

    protected function createProductModel(string $code, string $familyVariantCode, array $data): ProductModelInterface
    {
        $data = \array_merge(['code' => $code, 'family_variant' => $familyVariantCode], $data);
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $violations = $this->get('pim_catalog.validator.product')->validate($productModel);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    protected function createFamily(string $code, array $data = []): FamilyInterface
    {
        $data = array_merge(['code' => $code], $data);

        $family = $this->get('akeneo_integration_tests.base.family.builder')->build($data);

        $violations = $this->get('validator')->validate($family);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.family')->save($family);

        return $family;
    }

    protected function createFamilyVariant(string $code, string $family, array $data = []): FamilyVariantInterface
    {
        $data = array_merge(['code' => $code, 'family' => $family], $data);

        $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $data);

        $violations = $this->get('validator')->validate($familyVariant);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.family_variant')->save($familyVariant);

        return $familyVariant;
    }

    protected function createAttribute(string $code, array $data = []): AttributeInterface
    {
        $defaultData = [
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ];
        $data = array_merge($defaultData, $data);

        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build($data, true);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    protected function createAttributeOptions(string $attributeCode, array $optionsCodes): void
    {
        $attributeOptions = [];
        foreach ($optionsCodes as $optionCode) {
            $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
            $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
                'code' => $optionCode,
                'attribute' => $attributeCode,
            ]);
            $attributeOptions[] = $attributeOption;
        }

        $this->get('pim_catalog.saver.attribute_option')->saveAll($attributeOptions);
    }
}
