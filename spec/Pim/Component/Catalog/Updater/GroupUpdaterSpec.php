<?php

namespace spec\Pim\Component\Catalog\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\GroupTranslation;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;
use Prophecy\Argument;

class GroupUpdaterSpec extends ObjectBehavior
{
    function let(GroupTypeRepositoryInterface $groupTypeRepository, AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($groupTypeRepository, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\GroupUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_a_variant_group()
    {
        $this->shouldThrow(
            new \InvalidArgumentException(
                'Expects a "Pim\Component\Catalog\Model\GroupInterface", "stdClass" provided.'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_group(
        $groupTypeRepository,
        $attributeRepository,
        GroupInterface $group,
        GroupTypeInterface $type,
        GroupTranslation $translatable,
        AttributeInterface $attributeColor,
        AttributeInterface $attributeSize
    ) {
        $groupTypeRepository->findOneByIdentifier('RELATED')->willReturn($type);
        $attributeRepository->findOneByIdentifier('color')->willReturn($attributeColor);
        $attributeRepository->findOneByIdentifier('size')->willReturn($attributeSize);

        $group->getTranslation()->willReturn($translatable);
        $translatable->setLabel('T-shirt super beau')->shouldBeCalled();
        $group->setCode('mycode')->shouldBeCalled();
        $group->setLocale('fr_FR')->shouldBeCalled();
        $group->setType($type)->shouldBeCalled();
        $group->setAxisAttributes([$attributeColor, $attributeSize])->shouldBeCalled();
        $group->getId()->willReturn(null);

        $values = [
            'code'   => 'mycode',
            'type'   => 'RELATED',
            'labels' => [
                'fr_FR' => 'T-shirt super beau',
            ],
            'axis'   => ['color', 'size'],
        ];

        $this->update($group, $values, []);
    }

    function it_throws_an_error_if_type_is_unknown(GroupInterface $group)
    {
        $group->setCode('mycode')->shouldBeCalled();
        $group->getId()->willReturn(null);

        $values = [
            'code' => 'mycode',
            'type' => 'UNKNOWN',
        ];

        $this->shouldThrow(new \InvalidArgumentException('Type "UNKNOWN" does not exist'))
            ->during('update', [$group, $values, []]);
    }

    function it_throws_an_exception_if_attribute_is_unknown($attributeRepository, GroupInterface $group)
    {
        $group->setCode('mycode')->shouldBeCalled();
        $attributeRepository->findOneByIdentifier('foo')->willReturn(null);
        $group->getId()->willReturn(null);

        $values = [
            'code' => 'mycode',
            'axis' => ['foo']
        ];

        $this->shouldThrow(new \InvalidArgumentException('Attribute "foo" does not exist'))
            ->during('update', [$group, $values, []]);
    }
}
