<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetEvaluableAttributesByProductQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeGroupActivationRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetEvaluableAttributesByProductQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_the_evaluable_attributes_of_a_product()
    {
        $this->givenADeactivatedAttributeGroup('erp');

        $this->createAttributes([
            ['code' => 'a_boolean', 'type' => AttributeTypes::BOOLEAN],
            ['code' => 'a_localizable_textarea', 'type' => AttributeTypes::TEXTAREA, 'scopable' => true, 'localizable' => true],
            ['code' => 'a_readonly_textarea', 'type' => AttributeTypes::TEXTAREA, 'properties' => ['is_read_only' => true], 'localizable' => true],
            ['code' => 'a_localizable_text', 'type' => AttributeTypes::TEXT, 'scopable' => true, 'localizable' => true],
            ['code' => 'a_not_localizable_text', 'type' => AttributeTypes::TEXT, 'scopable' => false, 'localizable' => false],
            ['code' => 'a_text_of_another_family', 'type' => AttributeTypes::TEXT, 'scopable' => true, 'localizable' => true],
            ['code' => 'a_deactivated_text', 'type' => AttributeTypes::TEXT, 'scopable' => true, 'localizable' => true, 'group' => 'erp'],
        ]);

        $this->createFamily([
            'code' => 'familyA',
            'attributes' => [
                'sku',
                'a_boolean',
                'a_localizable_textarea',
                'a_readonly_textarea',
                'a_localizable_text',
                'a_not_localizable_text',
                'a_deactivated_text',
            ],
        ]);

        $productId = $this->createProduct();

        $expectedAttributes = [
            new Attribute(new AttributeCode('a_localizable_textarea'), AttributeType::textarea(), true),
            new Attribute(new AttributeCode('a_localizable_text'), AttributeType::text(), true),
            new Attribute(new AttributeCode('a_not_localizable_text'), AttributeType::text(), false),
        ];

        $result = $this
            ->get(GetEvaluableAttributesByProductQuery::class)
            ->execute($productId);

        $this->assertEqualsCanonicalizing($expectedAttributes, $result);
    }

    private function createProduct(): ProductId
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier('product_with_family')
            ->withFamily('familyA')
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId((int) $product->getId());
    }

    private function createAttributes(array $attributesData): void
    {
        $attributes = array_map(function ($attributeData) {
            $attribute = $this->get('pim_catalog.factory.attribute')->create();

            if (isset($attributeData['properties'])) {
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
                    'group' => $attributeData['group'] ?? 'other',
                ]
            );

            $errors = $this->get('validator')->validate($attribute);
            Assert::count($errors, 0);

            return $attribute;
        }, $attributesData);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function createFamily(array $familyData): void
    {
        $family = $this->get('akeneo_integration_tests.base.family.builder')->build($familyData);

        $errors = $this->get('validator')->validate($family);
        Assert::count($errors, 0);

        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function givenADeactivatedAttributeGroup(string $code): void
    {
        $attributeGroup = $this->get('pim_catalog.factory.attribute_group')->create();
        $this->get('pim_catalog.updater.attribute_group')->update($attributeGroup, ['code' => $code]);
        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);

        $attributeGroupActivation = new AttributeGroupActivation(new AttributeGroupCode($code), false);
        $this->get(AttributeGroupActivationRepository::class)->save($attributeGroupActivation);
    }
}
