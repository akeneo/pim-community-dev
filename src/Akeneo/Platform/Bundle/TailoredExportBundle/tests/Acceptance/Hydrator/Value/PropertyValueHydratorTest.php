<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\Hydrator\Value;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\CategoriesValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\EnabledValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\FamilyValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\FamilyVariantValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\GroupsValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\ParentValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\Value\PropertyValueHydrator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PropertyValueHydratorTest extends KernelTestCase
{
    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    /**
     * @dataProvider valuePropertiesProvider
     * @test
     */
    public function it_returns_value_properties_from_product(string $propertyName, SourceValueInterface $expectedValue)
    {
        $parentCategory = new Category();
        $parentCategory->setCode('a_parent_category_code');

        $parentProductModel = new ProductModel();
        $parentProductModel->setCode('a_product_model_code');
        $parentProductModel->addCategory($parentCategory);

        $category = new Category();
        $category->setCode('a_category_code');

        $anotherCategory = new Category();
        $anotherCategory->setCode('another_category_code');

        $family = new Family();
        $family->setCode('a_family_code');

        $familyVariant = new FamilyVariant();
        $familyVariant->setCode('a_family_variant_code');

        $group = new Group();
        $group->setCode('a_group_code');

        $anotherGroup = new Group();
        $anotherGroup->setCode('another_group_code');

        $product = new Product();
        $product->setParent($parentProductModel);
        $product->addCategory($category);
        $product->addCategory($anotherCategory);
        $product->setEnabled(true);
        $product->setFamily($family);
        $product->setFamilyVariant($familyVariant);
        $product->addGroup($group);
        $product->addGroup($anotherGroup);

        $valueHydrated = $this->getHydrator()->hydrate($propertyName, $product);
        $this->assertEquals($expectedValue, $valueHydrated);
    }

    public function valuePropertiesProvider(): array
    {
        return [
            'it_hydrate_family_value' => [
                'property_name' => 'family',
                'expected_value' => new FamilyValue('a_family_code'),
            ],
            'it_hydrate_family_variant_value' => [
                'property_name' => 'family_variant',
                'expected_value' => new FamilyVariantValue('a_family_variant_code'),
            ],
            'it_hydrate_enabled_value' => [
                'property_name' => 'enabled',
                'expected_value' => new EnabledValue(true),
            ],
            'it_hydrate_parent_value' => [
                'property_name' => 'parent',
                'expected_value' => new ParentValue('a_product_model_code'),
            ],
            'it_hydrate_groups_value' => [
                'property_name' => 'groups',
                'expected_value' => new GroupsValue(['a_group_code', 'another_group_code']),
            ],
            'it_hydrate_categories_value' => [
                'property_name' => 'categories',
                'expected_value' => new CategoriesValue(['a_category_code', 'a_parent_category_code', 'another_category_code']),
            ],
        ];
    }

    public function it_returns_null_value_when_value_is_empty()
    {
        $this->assertEquals(new NullValue(), $this->getHydrator()->hydrate('family', new Product()));
        $this->assertEquals(new NullValue(), $this->getHydrator()->hydrate('family_variant', new Product()));
        $this->assertEquals(new NullValue(), $this->getHydrator()->hydrate('groups', new Product()));
        $this->assertEquals(new NullValue(), $this->getHydrator()->hydrate('parent', new Product()));
    }

    public function it_throw_an_exception_when_property_is_not_supported()
    {
        $this->expectErrorMessage('Unsupported property name "unknown_property"');

        $this->getHydrator()->hydrate('unknown_property', new Product());
    }

    private function getHydrator(): PropertyValueHydrator
    {
        return static::$container->get('Akeneo\Platform\TailoredExport\Infrastructure\Hydrator\Value\PropertyValueHydrator');
    }
}
