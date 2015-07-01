<?php

namespace spec\Pim\Component\Catalog\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\GroupTranslation;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupTypeRepositoryInterface;
use Prophecy\Argument;

class GroupUpdaterSpec extends ObjectBehavior
{
    function let(GroupTypeRepositoryInterface $groupTypeRepository) {
        $this->beConstructedWith($groupTypeRepository);
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
                'Expects a "Pim\Bundle\CatalogBundle\Model\GroupInterface", "stdClass" provided.'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_group(
        $groupTypeRepository,
        GroupInterface $group,
        GroupTypeInterface $type,
        GroupTranslation $translatable
    ) {
        $groupTypeRepository->findOneByIdentifier('RELATED')->willReturn($type);

        $group->getTranslation()->willReturn($translatable);
        $translatable->setLabel('T-shirt super beau')->shouldBeCalled();
        $group->setCode('mycode')->shouldBeCalled();
        $group->setLocale('fr_FR')->shouldBeCalled();
        $group->setType($type)->shouldBeCalled();
        $group->getId()->willReturn(null);

        $values = [
            'code'         => 'mycode',
            'type'         => 'RELATED',
            'labels'       => [
                'fr_FR' => 'T-shirt super beau',
            ],
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
}
