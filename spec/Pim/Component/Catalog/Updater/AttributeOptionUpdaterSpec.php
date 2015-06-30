<?php

namespace spec\Pim\Component\Catalog\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class AttributeOptionUpdaterSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\AttributeOptionUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_updates_all_fields_on_a_new_attribute_option(
        $attributeRepository,
        AttributeOptionInterface $attributeOption,
        AttributeInterface $attribute,
        AttributeOptionValueInterface $attributeOptionValue
    ) {
        $attributeOption->getId()->willReturn(null);
        $attributeOption->getAttribute()->willReturn(null);

        $attributeOption->setCode('mycode')->shouldBeCalled();
        $attributeRepository->findOneByIdentifier('myattribute')->willReturn($attribute);
        $attributeOption->setAttribute($attribute)->shouldBeCalled();
        $attributeOption->setSortOrder(12)->shouldBeCalled();
        $attributeOption->setLocale('de_DE')->shouldBeCalled();
        $attributeOption->getTranslation()->willReturn($attributeOptionValue);
        $attributeOptionValue->setLabel('210 x 1219 mm')->shouldBeCalled();

        $this->update(
            $attributeOption,
            [
                'code' => 'mycode',
                'attribute' => 'myattribute',
                'sort_order' => 12,
                'labels' => [
                    'de_DE' => '210 x 1219 mm'
                ]
            ]
        );
    }

    function it_throws_an_exception_when_attribute_does_not_exist(
        $attributeRepository,
        AttributeOptionInterface $attributeOption
    ) {
        $attributeOption->getId()->willReturn(null);
        $attributeOption->getAttribute()->willReturn(null);

        $attributeOption->setCode('mycode')->shouldBeCalled();
        $attributeRepository->findOneByIdentifier('myattribute')->willReturn(null);

        $this->shouldThrow('\InvalidArgumentException')->during(
            'update',
            [
                $attributeOption,
                [
                    'code' => 'mycode',
                    'attribute' => 'myattribute',
                    'sort_order' => 12,
                    'labels' => [
                        'de_DE' => '210 x 1219 mm'
                    ]
                ]
            ]
        );
    }

    function it_does_not_update_readonly_fields_on_an_existing_attribute_option(
        AttributeOptionInterface $attributeOption,
        AttributeOptionValueInterface $attributeOptionValue
    ) {
        $attributeOption->getId()->willReturn(42);
        $attributeOption->getAttribute()->willReturn(null);

        // read only fields
        $attributeOption->setCode('mycode')->shouldNotBeCalled();
        $attributeOption->setAttribute(Argument::any())->shouldNotBeCalled();

        $attributeOption->setSortOrder(12)->shouldBeCalled();
        $attributeOption->setLocale('de_DE')->shouldBeCalled();
        $attributeOption->getTranslation()->willReturn($attributeOptionValue);
        $attributeOptionValue->setLabel('210 x 1219 mm')->shouldBeCalled();

        $this->update(
            $attributeOption,
            [
                'code' => 'mycode',
                'attribute' => 'myattribute',
                'sort_order' => 12,
                'labels' => [
                    'de_DE' => '210 x 1219 mm'
                ]
            ]
        );
    }
}
