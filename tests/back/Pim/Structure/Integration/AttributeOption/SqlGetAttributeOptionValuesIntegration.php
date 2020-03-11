<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AttributeOption;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql\SqlGetAttributeOptionValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetAttributeOptionValuesIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->createAttributes(['attribute_1', 'attribute_2', 'attribute_3']);

        $this->createAttributeOptions('attribute_1', 'option_A', ['fr_FR' => 'option A', 'en_US' => 'A option']);
        $this->createAttributeOptions('attribute_1', 'option_B', ['fr_FR' => 'option B', 'en_US' => 'B option']);
        $this->createAttributeOptions('attribute_1', 'option_C', []);

        $this->createAttributeOptions('attribute_2', 'option_D', ['fr_FR' => 'option D']);
        $this->createAttributeOptions('attribute_2', 'option_A', ['en_US' => 'option A (2)']);
    }

    public function test_it_returns_option_values()
    {
        $actual = $this->getSqlGetAttributeOptionValues()->fromOptionCodesByAttributeCode([
            'attribute_2' => ['option_A', 'option_B'],
            'attribute_1' => ['option_A', 'option_D', 'option_C'],
        ]);

        $expected = [
            'attribute_2' => [
                'option_A' => ['en_US' => 'option A (2)'],
            ],
            'attribute_1' => [
                'option_A' => [
                    'fr_FR' => 'option A',
                    'en_US' => 'A option',
                ],
                'option_C' => [],
            ],
        ];

        \PHPUnit\Framework\Assert::assertEqualsCanonicalizing($expected['attribute_2'], $actual['attribute_2']);
        \PHPUnit\Framework\Assert::assertEqualsCanonicalizing($expected['attribute_1'], $actual['attribute_1']);
    }

    public function getSqlGetAttributeOptionValues(): SqlGetAttributeOptionValues
    {
        return $this->get('akeneo.pim.structure.query.get_attribute_option_values');
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
            $constraints = $this->get('validator')->validate($attribute);

            Assert::count($constraints, 0);
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
