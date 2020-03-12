<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AttributeOption;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Cache\LruCachedGetExistingAttributeOptionsWithValues;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LruCachedGetExistingAttributeOptionsWithValuesIntegration extends TestCase
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
        $actual = $this->getQuery()->fromAttributeCodeAndOptionCodes([]);
        \PHPUnit\Framework\Assert::assertEqualsCanonicalizing([], $actual);

        $actual = $this->getQuery()->fromAttributeCodeAndOptionCodes(['attribute_2.option_A', 'attribute_2.option_B']);
        $expected = ['attribute_2.option_A' => ['en_US' => 'option A (2)']];
        $this->assertArrayEquals($expected, $actual);

        $actual = $this->getQuery()->fromAttributeCodeAndOptionCodes(
            ['attribute_1.option_A', 'attribute_1.option_D', 'attribute_1.option_C']
        );
        $expected = [
            'attribute_1.option_A' => [
                'en_US' => 'A option',
            ],
            'attribute_1.option_C' => ['en_US' => null],
        ];
        $this->assertArrayEquals($expected, $actual);
    }

    public function test_it_returns_an_empty_array_for_unknown_attribute_code()
    {
        $actual = $this->getQuery()->fromAttributeCodeAndOptionCodes(['unknown.option_A', 'attribute_2.unknown']);
        \PHPUnit\Framework\Assert::assertEqualsCanonicalizing([], $actual);
    }

    private function assertArrayEquals(array $expected, array $actual): void
    {
        // We want to check the arrays are equl no matter the order.
        // assertEqualsCanonicalizing works only for first level, not for second, third, etc.. levels.
        // As you know our arrays have only 2 levels, we can perform a assertEqualsCanonicalizing on second level only.
        \PHPUnit\Framework\Assert::assertEqualsCanonicalizing(array_keys($expected), array_keys($actual));
        foreach ($expected as $key => $values) {
            \PHPUnit\Framework\Assert::assertEqualsCanonicalizing($values, $actual[$key]);
        }
    }

    private function getQuery(): LruCachedGetExistingAttributeOptionsWithValues
    {
        return $this->get('akeneo.pim.structure.query.lru_cached_get_existing_attribute_options_with_values');
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
