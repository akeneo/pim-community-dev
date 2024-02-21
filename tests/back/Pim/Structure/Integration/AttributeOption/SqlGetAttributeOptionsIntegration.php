<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AttributeOption;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql\SqlGetAttributeOptions;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlGetAttributeOptionsIntegration extends TestCase
{
    private SqlGetAttributeOptions $sqlGetAttributeOptions;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlGetAttributeOptions = $this->get('akeneo.pim.structure.query.get_attribute_options');

        $this->createAttributes(['attribute_1', 'attribute_2', 'attribute_3']);
        $this->createAttributeOptions('attribute_1', 'option_A', ['fr_FR' => 'option A', 'en_US' => 'A option']);
        $this->createAttributeOptions('attribute_1', 'option_B', ['fr_FR' => 'option B', 'en_US' => 'B option']);
        $this->createAttributeOptions('attribute_1', 'option_C', []);
        $this->createAttributeOptions('attribute_2', 'option_D', ['fr_FR' => 'option D']);
    }

    public function test_it_returns_all_attribute_option_codes_for_a_given_attribute_code(): void
    {
        $attributeOptions = \iterator_to_array($this->sqlGetAttributeOptions->forAttributeCode('attribute_1'));

        self::assertCount(3, $attributeOptions);

        self::assertEquals([
            'code' => 'option_A',
            'labels' => [
                'fr_FR' => 'option A',
                'en_US' => 'A option',
            ]
        ], $attributeOptions[0]->normalize());

        self::assertEquals([
            'code' => 'option_B',
            'labels' => [
                'fr_FR' => 'option B',
                'en_US' => 'B option',
            ]
        ], $attributeOptions[1]->normalize());

        self::assertEquals([
            'code' => 'option_C',
            'labels' => []
        ], $attributeOptions[2]->normalize());

        $attributeOptions = \iterator_to_array($this->sqlGetAttributeOptions->forAttributeCode('attribute_2'));

        self::assertCount(1, $attributeOptions);

        self::assertEquals([
            'code' => 'option_D',
            'labels' => [
                'fr_FR' => 'option D',
            ]
        ], $attributeOptions[0]->normalize());

        $attributeOptions = \iterator_to_array($this->sqlGetAttributeOptions->forAttributeCode('attribute_3'));
        self::assertSame([], $attributeOptions);

        $attributeOptions = \iterator_to_array($this->sqlGetAttributeOptions->forAttributeCode('unknown'));
        self::assertSame([], $attributeOptions);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createAttributes(array $codes): void
    {
        $attributes = [];
        foreach ($codes as $code) {
            $data = [
                'code' => $code,
                'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other'
            ];

            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
            $violations = $this->get('validator')->validate($attribute);

            Assert::count($violations, 0);
            $attributes[] = $attribute;
        }

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function createAttributeOptions(string $attributeCode, string $optionCode, array $labels): void
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();

        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
            'code' => $optionCode,
            'attribute' => $attributeCode,
            'labels' => $labels,
        ]);
        $constraints = $this->get('validator')->validate($attributeOption);
        Assert::count($constraints, 0);
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
    }
}
