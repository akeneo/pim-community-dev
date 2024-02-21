<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\Storage\Sql\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAttributeOptionsMaxSortOrderIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_gets_the_max_options_sort_order_indexed_by_attribute_code()
    {
        $this->createSimpleSelectAttribute('color', ['blue', 'red', 'green']);
        $this->createSimpleSelectAttribute('size', ['xs', 's', 'm', 'l', 'xl']);
        $this->createSimpleSelectAttribute(
            'material',
            [
                12 => 'leather',
                23 => 'cotton',
                4 => 'metal',
            ]
        );

        $expected = [
            'color' => 2,
            'size' => 4,
            'material' => 23,
        ];

        $actual = $this->get('pim_catalog.query.get_attribute_options_max_sort_order')
                       ->forAttributeCodes(['size', 'color', 'material']);

        Assert::assertSameSize($expected, $actual);
        foreach ($expected as $expectedKey => $expectedValue) {
            Assert::assertArrayHasKey($expectedKey, $actual);
            Assert::assertSame($expectedValue, $actual[$expectedKey]);
        }
    }

    /**
     * @test
     */
    public function it_does_not_return_a_value_for_an_attribute_without_options()
    {
        $this->createSimpleSelectAttribute('select_without_options', []);
        Assert::assertSame([], $this->get('pim_catalog.query.get_attribute_options_max_sort_order')
                                    ->forAttributeCodes(['select_without_options']));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createSimpleSelectAttribute(string $code, array $optionCodes): void
    {
        $attribute = $this->createAttribute();
        $attribute->setCode($code);
        $attribute->setType(AttributeTypes::OPTION_SIMPLE_SELECT);
        $attribute->setBackendType(AttributeTypes::BACKEND_TYPE_OPTION);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        foreach ($optionCodes as $sortOrder => $optionCode) {
            $option = new AttributeOption();
            $option->setCode($optionCode);
            $option->setAttribute($attribute);
            $option->setSortOrder($sortOrder);
            $this->get('pim_catalog.saver.attribute_option')->save($option);
        }
    }

    private function createAttribute(): AttributeInterface
    {
        return $this->get('pim_catalog.factory.attribute')->create();
    }
}
