<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;

class AttributeGroupUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($attributeRepository);
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
            new \InvalidArgumentException(
                'Expects a "Pim\Component\Catalog\Model\AttributeGroupInterface", "stdClass" provided.'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_an_attribute_group(
        $attributeRepository,
        AttributeGroupInterface $attributeGroup,
        AttributeInterface $attributeSize,
        AttributeInterface $attributeMainColor
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

        $attributeGroup->setCode('sizes')->shouldBeCalled();
        $attributeGroup->setSortOrder(1)->shouldBeCalled();

        $attributeRepository->findOneByIdentifier('size')->willReturn($attributeSize);
        $attributeRepository->findOneByIdentifier('main_color')->willReturn($attributeMainColor);

        $attributeGroup->addAttribute($attributeSize)->shouldBeCalled();
        $attributeGroup->addAttribute($attributeMainColor)->shouldBeCalled();

        $attributeGroup->setLocale('en_US')->shouldBeCalled();
        $attributeGroup->setLocale('fr_FR')->shouldBeCalled();
        $attributeGroup->setLabel('Sizes')->shouldBeCalled();
        $attributeGroup->setLabel('Tailles')->shouldBeCalled();

        $this->update($attributeGroup, $values, []);
    }

    function it_throws_an_exception_if_attribute_not_found(
        $attributeRepository,
        AttributeGroupInterface $attributeGroupInterface
    ) {
        $values = [
            'code'       => 'sizes',
            'sort_order' => 1,
            'attributes' => ['foo'],
            'label'      => [
                'en_US' => 'Sizes',
                'fr_FR' => 'Tailles'
            ]
        ];

        $attributeRepository->findOneByIdentifier('foo')->willReturn(null);
        $this->shouldThrow(new \InvalidArgumentException('Attribute with "foo" code does not exist'))
            ->during('update', [$attributeGroupInterface, $values]);
    }
}
