<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AttributeOption;

use Akeneo\Pim\Structure\Bundle\Query\InternalApi\AttributeOption\SqlGetAttributeOptionCodes;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlGetAttributeOptionCodesIntegration extends TestCase
{
    private SqlGetAttributeOptionCodes $sqlGetAttributeOptionCodes;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlGetAttributeOptionCodes = $this->get('akeneo.pim.structure.query.get_attribute_option_codes');

        $this->createAttributes(['attribute_1', 'attribute_2', 'attribute_3']);
        $this->createAttributeOptions('attribute_1', 'option_A', ['fr_FR' => 'option A', 'en_US' => 'A option']);
        $this->createAttributeOptions('attribute_1', 'option_B', ['fr_FR' => 'option B', 'en_US' => 'B option']);
        $this->createAttributeOptions('attribute_1', 'option_C', []);
        $this->createAttributeOptions('attribute_2', 'option_D', ['fr_FR' => 'option D']);
    }

    public function test_it_returns_all_attribute_option_codes_for_a_given_attribute_code(): void
    {
        $attributeOptionCodes = \iterator_to_array($this->sqlGetAttributeOptionCodes->forAttributeCode('attribute_1'));
        self::assertSame(['option_A', 'option_B', 'option_C'], $attributeOptionCodes);

        $attributeOptionCodes = \iterator_to_array($this->sqlGetAttributeOptionCodes->forAttributeCode('attribute_2'));
        self::assertSame(['option_D'], $attributeOptionCodes);

        $attributeOptionCodes = \iterator_to_array($this->sqlGetAttributeOptionCodes->forAttributeCode('attribute_3'));
        self::assertSame([], $attributeOptionCodes);

        $attributeOptionCodes = \iterator_to_array($this->sqlGetAttributeOptionCodes->forAttributeCode('unknown'));
        self::assertSame([], $attributeOptionCodes);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
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
