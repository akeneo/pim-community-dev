<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AttributeOption;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetExistingAttributeOptionCodesFromOptionCodesSqlIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->givenTheFollowingAttributeSimpleSelectWithOptions([
            'attribute_1' => [
                'option_A',
                'option_B',
                'option_C',
            ],
            'attribute_2' => [
                'option_D',
                'option_F',
                'option_G',
                'option_A',
            ]
        ]);
    }

    public function test_it_works_with_empty_codes()
    {
        $expected = [];
        $actual = $this->getQuery()->fromOptionCodesByAttributeCode([]);

        \PHPUnit\Framework\Assert::assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_returns_only_what_is_existing()
    {
        $expected = ['attribute_1' => ['option_A', 'option_B'], 'attribute_2' => ['option_D']];
        $actual = $this->getQuery()->fromOptionCodesByAttributeCode(['attribute_1' => ['option_A', 'option_B', 'option_X'], 'attribute_2' => ['option_Z', 'option_D']]);

        \PHPUnit\Framework\Assert::assertEqualsCanonicalizing($expected, $actual);
    }

    private function getQuery(): GetExistingAttributeOptionCodes
    {
        return $this->get('akeneo.pim.structure.query.get_existing_attribute_option_codes_from_option_codes');
    }

    private function givenTheFollowingAttributeSimpleSelectWithOptions(array $tree): void
    {
        $attributes = array_map(function (string $code) {
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

            return $attribute;
        }, array_keys($tree));

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);

        $attributeOptions = [];

        foreach ($tree as $attributeCode => $options) {
            foreach ($options as $optionCode) {
                $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();

                $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
                    'code' => $optionCode,
                    'attribute' => $attributeCode,
                ]);

                $constraints = $this->get('validator')->validate($attributeOption);

                Assert::count($constraints, 0);

                $attributeOptions[] = $attributeOption;
            }
        }

        $this->get('pim_catalog.saver.attribute_option')->saveAll($attributeOptions);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
