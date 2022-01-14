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

namespace Akeneo\Platform\Syndication\Test\Acceptance\Hydrator\Value\Property;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Platform\Syndication\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\Syndication\Application\Common\Selection\Code\CodeSelection;
use Akeneo\Platform\Syndication\Application\Common\Source\PropertySource;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\CategoriesValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\EnabledValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\FamilyValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\FamilyVariantValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\GroupsValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\NullValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\ParentValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\QualityScoreValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class PropertyValueHydratorTest extends AbstractPropertyValueHydratorTest
{
    /**
     * @dataProvider valuePropertiesProvider
     * @test
     */
    public function it_returns_value_properties_from_product(PropertySource $source, SourceValueInterface $expectedValue): void
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

        $product = new ConnectorProduct(
            Uuid::fromString('product_uuid'),
            'product_code',
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            true,
            $family->getCode(),
            [$category->getCode(), $anotherCategory->getCode()],
            [$group->getCode(), $anotherGroup->getCode()],
            $parentProductModel->getCode(),
            [],
            [],
            [],
            new ReadValueCollection([]),
            null,
            null
        );

        $valueHydrated = $this->getHydrator()->hydrate($source, $product);
        $this->assertEquals($expectedValue, $valueHydrated);
    }

    private function getPropertySource(
        string $propertyName,
        ?string $channelReference = null,
        ?string $localeReference = null
    ): PropertySource {
        return new PropertySource(
            'uuid',
            $propertyName,
            $channelReference,
            $localeReference,
            OperationCollection::create([]),
            new CodeSelection(),
        );
    }

    public function valuePropertiesProvider(): array
    {
        return [
            'it hydrates family value' => [
                'property source' => $this->getPropertySource('family'),
                'expected value' => new FamilyValue('a_family_code'),
            ],
            'it hydrates family variant value' => [
                'property source' => $this->getPropertySource('family_variant'),
                'expected value' => new FamilyVariantValue('a_family_variant_code'),
            ],
            'it hydrates enabled value' => [
                'property source' => $this->getPropertySource('enabled'),
                'expected value' => new EnabledValue(true),
            ],
            'it hydrates parent value' => [
                'property source' => $this->getPropertySource('parent'),
                'expected value' => new ParentValue('a_product_model_code'),
            ],
            'it hydrates groups value' => [
                'property source' => $this->getPropertySource('groups'),
                'expected value' => new GroupsValue(['a_group_code', 'another_group_code']),
            ],
            'it hydrates categories value' => [
                'property source' => $this->getPropertySource('categories'),
                'expected value' => new CategoriesValue(['a_category_code', 'a_parent_category_code', 'another_category_code']),
            ],
            'it hydrates quality score value' => [
                'property source' => $this->getPropertySource('quality_score', 'ecommerce', 'en_US'),
                'expected value' => new QualityScoreValue('B'),
            ],
            'it hydrates quality score value on another locale' => [
                'property source' => $this->getPropertySource('quality_score', 'ecommerce', 'fr_FR'),
                'expected value' => new QualityScoreValue('A'),
            ],
        ];
    }

    public function it_returns_null_value_when_value_is_empty(): void
    {
        $product = new ConnectorProduct(
            Uuid::fromString('product_uuid'),
            'product_code',
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            true,
            'family_code',
            [],
            [],
            'parent_product_code',
            [],
            [],
            [],
            new ReadValueCollection([]),
            null,
            null
        );

        $this->assertEquals(new NullValue(), $this->getHydrator()->hydrate($this->getPropertySource('family'), $product));
        $this->assertEquals(new NullValue(), $this->getHydrator()->hydrate($this->getPropertySource('family_variant'), $product));
        $this->assertEquals(new NullValue(), $this->getHydrator()->hydrate($this->getPropertySource('groups'), $product));
        $this->assertEquals(new NullValue(), $this->getHydrator()->hydrate($this->getPropertySource('parent'), $product));
    }

    public function it_throws_an_exception_when_property_is_not_supported(): void
    {
        $product = new ConnectorProduct(
            Uuid::fromString('product_uuid'),
            'product_code',
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            true,
            'family_code',
            [],
            [],
            'parent_product_code',
            [],
            [],
            [],
            new ReadValueCollection([]),
            null,
            null
        );

        $this->expectErrorMessage('Unsupported property name "unknown_property"');

        $this->getHydrator()->hydrate($this->getPropertySource('unknown_property'), $product);
    }
}
