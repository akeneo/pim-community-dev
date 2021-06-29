<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetEvaluableAttributesByProductModelQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeGroupActivationRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetEvaluableAttributesByProductModelQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->givenADeactivatedAttributeGroup('erp');

        $this->addAttributesToFamilyA([
            ['code' => 'a_readonly_textarea', 'type' => AttributeTypes::TEXTAREA, 'properties' => ['is_read_only' => true], 'localizable' => true],
            ['code' => 'a_localizable_text', 'type' => AttributeTypes::TEXT, 'scopable' => true, 'localizable' => true],
            ['code' => 'a_deactivated_text', 'type' => AttributeTypes::TEXT, 'scopable' => true, 'localizable' => true, 'group' => 'erp'],
        ]);
    }

    public function test_it_returns_the_evaluable_attributes_of_a_product_model_with_only_one_level_of_variation()
    {
        $productModelId = $this->givenAProductModelWithOnlyOneLevelOfVariation();

        $attributes = $this->get(GetEvaluableAttributesByProductModelQuery::class)->execute($productModelId);

        $expectedAttributes = [
            new Attribute(new AttributeCode('a_multi_select'), AttributeType::multiSelect(), false),
            new Attribute(new AttributeCode('a_text_area'), AttributeType::textarea(), false),
            new Attribute(new AttributeCode('a_localized_and_scopable_text_area'), AttributeType::textarea(), true),
            new Attribute(new AttributeCode('a_localizable_text'), AttributeType::text(), true),
        ];

        $this->assertEqualsCanonicalizing($expectedAttributes, $attributes);
    }

    public function test_it_returns_the_evaluable_attributes_of_a_root_product_model_with_two_levels_of_variation()
    {
        $productModelId = $this->givenARootProductModelWithTwoLevelsOfVariation();

        $attributes = $this->get(GetEvaluableAttributesByProductModelQuery::class)->execute($productModelId);

        $expectedAttributes = [
            new Attribute(new AttributeCode('a_multi_select'), AttributeType::multiSelect(), false),
            new Attribute(new AttributeCode('a_localized_and_scopable_text_area'), AttributeType::textarea(), true),
            new Attribute(new AttributeCode('a_localizable_text'), AttributeType::text(), true),
        ];

        $this->assertEqualsCanonicalizing($expectedAttributes, $attributes);
    }

    public function test_it_returns_the_evaluable_attributes_of_a_sub_product_model()
    {
        $productModelId = $this->givenASubProductModel();

        $attributes = $this->get(GetEvaluableAttributesByProductModelQuery::class)->execute($productModelId);

        $expectedAttributes = [
            new Attribute(new AttributeCode('a_multi_select'), AttributeType::multiSelect(), false),
            new Attribute(new AttributeCode('a_simple_select'), AttributeType::simpleSelect(), false),
            new Attribute(new AttributeCode('a_text'), AttributeType::text(), false),
            new Attribute(new AttributeCode('a_localized_and_scopable_text_area'), AttributeType::textarea(), true),
            new Attribute(new AttributeCode('a_localizable_text'), AttributeType::text(), true),
        ];

        $this->assertEqualsCanonicalizing($expectedAttributes, $attributes);
    }

    private function givenAProductModelWithOnlyOneLevelOfVariation(): ProductId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode('one_level_product_model')
            ->withFamilyVariant('familyVariantA2')
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return new ProductId($productModel->getId());
    }

    private function givenARootProductModelWithTwoLevelsOfVariation(): ProductId
    {
        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode('two_level_root_product_model')
            ->withFamilyVariant('familyVariantA1')
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return new ProductId($productModel->getId());
    }

    private function givenASubProductModel(): ProductId
    {
        $this->givenARootProductModelWithTwoLevelsOfVariation();

        $productModel = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode('a_sub_product_model')
            ->withFamilyVariant('familyVariantA1')
            ->withParent('two_level_root_product_model')
            ->build();

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return new ProductId($productModel->getId());
    }

    private function addAttributesToFamilyA(array $attributesData): void
    {
        $attributes = array_map(function ($attributeData) {
            $attributeCodes[] = $attributeData['code'];
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

        $attributeCodes = array_map(function ($attribute) { return $attribute->getCode();}, $attributes);

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA');
        $this->get('pim_catalog.updater.family')->update($family, [
            'attributes' => array_merge($family->getAttributeCodes(), $attributeCodes)
        ]);
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
