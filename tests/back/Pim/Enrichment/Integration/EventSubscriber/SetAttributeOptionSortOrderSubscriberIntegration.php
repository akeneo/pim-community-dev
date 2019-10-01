<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\EventSubscriber;

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
class SetAttributeOptionSortOrderSubscriberIntegration extends TestCase
{
    public function test_that_sort_order_is_set_when_saving_an_attribute_option()
    {
        $color = $this->createSimpleSelectAttributeWithOptions('color', [0 => 'blue', 1 => 'red']);
        $option = new AttributeOption();
        $option->setCode('yellow');
        $option->setAttribute($color);

        $this->get('pim_catalog.saver.attribute_option')->save($option);

        Assert::assertSame(2, $option->getSortOrder());
    }

    public function test_sort_orders_are_set_when_saving_multiple_attribute_options()
    {
        $color = $this->createSimpleSelectAttributeWithOptions('color', [20 => 'blue', 5 => 'red']);
        $size = $this->createSimpleSelectAttributeWithOptions('size', []);

        $option1 = new AttributeOption();
        $option1->setCode('yellow');
        $option1->setAttribute($color);

        $option2 = new AttributeOption();
        $option2->setCode('xs');
        $option2->setAttribute($size);

        $option3 = new AttributeOption();
        $option3->setCode('xl');
        $option3->setAttribute($size);

        $this->get('pim_catalog.saver.attribute_option')->saveAll([$option1, $option2, $option3]);

        Assert::assertSame(21, $option1->getSortOrder());
        Assert::assertSame(0, $option2->getSortOrder());
        Assert::assertSame(1, $option3->getSortOrder());
    }

    public function test_that_sort_orders_are_set_when_saving_an_attribute()
    {
        $material = $this->createSimpleSelectAttributeWithOptions('material', [
            10 => 'leather',
            12 => 'wool',
        ]);

        $cotton = new AttributeOption();
        $cotton->setCode('cotton');
        $material->addOption($cotton);
        $polyester = new AttributeOption();
        $polyester->setCode('polyester');
        $material->addOption($polyester);

        $this->get('pim_catalog.saver.attribute')->save($material);

        Assert::assertSame(13, $cotton->getSortOrder());
        Assert::assertSame(14, $polyester->getSortOrder());
    }

    public function test_that_sort_orders_are_set_when_saving_multiple_attributes()
    {
        $color = $this->createSimpleSelectAttributeWithOptions('color', [4 => 'blue', 5 => 'red']);
        $yellow = new AttributeOption();
        $yellow->setCode('yellow');
        $color->addOption($yellow);

        $size = $this->createSimpleSelectAttributeWithOptions('size', []);
        $xs = new AttributeOption();
        $xs->setCode('xs');
        $size->addOption($xs);
        $xl = new AttributeOption();
        $xl->setCode('xl');
        $size->addOption($xl);

        $material = $this->get('pim_catalog.factory.attribute')->create();
        $material->setCode('material');
        $material->setType(AttributeTypes::OPTION_MULTI_SELECT);
        $material->setBackendType(AttributeTypes::BACKEND_TYPE_OPTIONS);

        $cotton = new AttributeOption();
        $cotton->setCode('cotton');
        $material->addOption($cotton);
        $polyester = new AttributeOption();
        $polyester->setCode('polyester');
        $material->addOption($polyester);

        $this->get('pim_catalog.saver.attribute')->saveAll([$color, $size, $material]);

        Assert::assertSame(6, $yellow->getSortOrder());
        Assert::assertSame(0, $xs->getSortOrder());
        Assert::assertSame(1, $xl->getSortOrder());
        Assert::assertSame(0, $cotton->getSortOrder());
        Assert::assertSame(1, $polyester->getSortOrder());
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createSimpleSelectAttributeWithOptions(string $code, array $optionCodes): AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
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

        return $attribute;
    }
}
