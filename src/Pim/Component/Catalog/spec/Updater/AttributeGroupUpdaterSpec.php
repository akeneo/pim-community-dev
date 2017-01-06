<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;

class AttributeGroupUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeGroupRepositoryInterface $attributeGroupRepository
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $attributeGroupRepository
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\AttributeGroupUpdater');
    }

    function it_is_an_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_an_attribute_group()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'Pim\Component\Catalog\Model\AttributeGroupInterface'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_an_attribute_group(
        $attributeRepository,
        $attributeGroupRepository,
        AttributeGroupInterface $attributeGroup,
        AttributeGroupInterface $defaultGroup,
        AttributeInterface $size,
        AttributeInterface $mainColor,
        AttributeInterface $sku
    ) {
        $values = [
            'code'       => 'sizes',
            'sort_order' => 1,
            'attributes' => ['size', 'main_color'],
            'label'      => [
                'en_US' => 'Sizes',
                'fr_FR' => 'Tailles'
            ]
        ];

        $attributeGroup->getCode()->willReturn('sizes');

        $attributeGroup->setCode('sizes')->shouldBeCalled();
        $attributeGroup->setSortOrder(1)->shouldBeCalled();

        $sku->getCode()->willReturn('sku');
        $size->getCode()->willReturn('size');
        $attributeGroup->getAttributes()->willReturn([$sku, $size]);
        $attributeGroupRepository->findDefaultAttributeGroup()->willReturn($defaultGroup);

        $defaultGroup->addAttribute($sku)->shouldBeCalled();

        $attributeRepository->findOneByIdentifier('size')->willReturn($size);
        $attributeRepository->findOneByIdentifier('main_color')->willReturn($mainColor);
        $attributeGroup->addAttribute($size)->shouldBeCalled();
        $attributeGroup->addAttribute($mainColor)->shouldBeCalled();

        $attributeGroup->setLocale('en_US')->shouldBeCalled();
        $attributeGroup->setLocale('fr_FR')->shouldBeCalled();
        $attributeGroup->setLabel('Sizes')->shouldBeCalled();
        $attributeGroup->setLabel('Tailles')->shouldBeCalled();

        $this->update($attributeGroup, $values, []);
    }

    function it_throws_an_exception_if_attribute_not_found(
        $attributeRepository,
        AttributeGroupInterface $attributeGroup
    ) {
        $values = [
            'attributes' => ['foo'],
        ];

        $attributeGroup->getCode()->willReturn('sizes');

        $attributeGroup->getAttributes()->willReturn([]);

        $attributeRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
               'attributes',
               'attribute code',
               'The attribute does not exist',
               'updater',
               'attribute group',
               'foo'
            ))
            ->during('update', [$attributeGroup, $values]);
    }

    function it_does_not_update_attributes_from_the_default_group(
        $attributeGroupRepository,
        AttributeGroupInterface $attributeGroup
    ) {
        $values = [
            'code' => 'other',
            'sort_order' => 1,
            'attributes' => ['foo'],
            'label' => [
                'en_US' => 'Other',
                'fr_FR' => 'Autre'
            ]
        ];

        $attributeGroup->getCode()->willReturn('other');

        $attributeGroup->setCode('other')->shouldBeCalled();
        $attributeGroup->setSortOrder(1)->shouldBeCalled();

        $attributeGroup->setLocale('en_US')->shouldBeCalled();
        $attributeGroup->setLocale('fr_FR')->shouldBeCalled();
        $attributeGroup->setLabel('Other')->shouldBeCalled();
        $attributeGroup->setLabel('Autre')->shouldBeCalled();


        $attributeGroupRepository->findDefaultAttributeGroup()->shouldNotBeCalled();
        $attributeGroup->getAttributes()->shouldNotBeCalled();

        $this->update($attributeGroup, $values, []);
    }
}
