<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\GetTextAttributeCodesCompatibleWithSpellingQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

class GetTextAttributeCodesCompatibleWithSpellingQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->givenAttributes([
            ['code' => 'a_not_localized_readonly_text', 'type' => AttributeTypes::TEXT, 'scopable' => false, 'localizable' => false, 'properties' => ['is_read_only' => false]],
            ['code' => 'a_localized_readonly_text', 'type' => AttributeTypes::TEXT, 'scopable' => false, 'localizable' => true, 'properties' => ['is_read_only' => true]],
            ['code' => 'a_localized_main_label', 'type' => AttributeTypes::TEXT, 'scopable' => false, 'localizable' => true],
            ['code' => 'a_not_localized_main_label', 'type' => AttributeTypes::TEXT, 'scopable' => false, 'localizable' => false],
            ['code' => 'a_localized_and_scopable_text', 'type' => AttributeTypes::TEXT, 'scopable' => true, 'localizable' => true],
        ]);

        $this->givenFamilies([
            [
                'code' => 'familyA',
                'attribute_codes' => [
                    'sku',
                    'a_not_localized_readonly_text',
                    'a_localized_readonly_text',
                    'a_localized_main_label',
                    'a_not_localized_main_label',
                    'a_localized_and_scopable_text',
                ]
            ]
        ]);
    }

    public function test_it_gets_product_localizable_attributes_by_type()
    {
        $productId = $this->createProduct();

        $expectedAttributeCodes = ['a_localized_and_scopable_text', 'a_localized_main_label'];

        $result = $this
            ->get(GetTextAttributeCodesCompatibleWithSpellingQuery::class)
            ->byProductId($productId);

        $this->assertEqualsCanonicalizing($expectedAttributeCodes, $result);
    }

    private function createProduct(): ProductId
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier('product_with_family')
            ->withFamily('familyA')
            ->build();

        $data = [
            'values' => [
                'a_localized_and_scopable_text' => [['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'some text']],
                'a_localized_main_label' => [['scope' => null, 'locale' => 'en_US', 'data' => 'some text']],
                'a_localized_readonly_text' => [['scope' => null, 'locale' =>  'en_US', 'data' => 'some text']],
                'a_not_localized_main_label' => [['scope' => null, 'locale' =>  null, 'data' => 'some text']],
                'a_not_localized_readonly_text' => [['scope' => null, 'locale' =>  null, 'data' => 'some text']],
            ]
        ];

        $this->get('pim_catalog.updater.product')->update($product, $data);

        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId((int) $product->getId());
    }

    private function givenAttributes(array $attributesData): void
    {
        $attributes = array_map(function ($attributeData) {
            $attribute = $this->get('pim_catalog.factory.attribute')->create();

            if (isset($attributeData['properties']) && is_array($attributeData['properties'])) {
                foreach ($attributeData['properties'] as $propertyKey => $propertyValue) {
                    $attribute->setProperty($propertyKey, $propertyValue);
                }
            }

            $this->get('pim_catalog.updater.attribute')->update(
                $attribute,
                [
                    'code' => $attributeData['code'],
                    'type' => $attributeData['type'],
                    'localizable' => $attributeData['localizable'] ?? false,
                    'scopable' => $attributeData['scopable'] ?? false,
                    'group' => 'other',
                    'available_locales' => $attributeData['available_locales'] ?? [],
                    'decimals_allowed' => $attributeData['type'] === AttributeTypes::PRICE_COLLECTION ? false : null,
                ]
            );

            $errors = $this->get('validator')->validate($attribute);
            Assert::count($errors, 0);

            return $attribute;
        }, $attributesData);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function givenFamilies(array $familiesData): void
    {
        $families = array_map(function ($familyData) {
            $family = $this->get('pim_catalog.factory.family')->create();
            $this->get('pim_catalog.updater.family')->update(
                $family,
                [
                    'code' => $familyData['code'],
                    'attributes'  =>  $familyData['attribute_codes'] ?? [],
                    'attribute_requirements' => $familyData['attribute_requirements'] ?? [],
                ]
            );

            $errors = $this->get('validator')->validate($family);
            Assert::count($errors, 0);

            return $family;
        }, $familiesData);

        $this->get('pim_catalog.saver.family')->saveAll($families);
    }
}
