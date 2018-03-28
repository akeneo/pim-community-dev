<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupTypeInterface;

class GroupTypeUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\GroupTypeUpdater');
    }

    function it_is_an_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_a_group_type()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'Pim\Component\Catalog\Model\GroupTypeInterface'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_group_type(GroupTypeInterface $groupType)
    {
        $values = [
            'code'       => 'variant',
            'label'      => [
                'en_US' => 'variant',
                'fr_FR' => 'variantes',
            ]
        ];

        $groupType->setCode('variant')->shouldBeCalled();
        $groupType->setLocale('en_US')->shouldBeCalled();
        $groupType->setLocale('fr_FR')->shouldBeCalled();
        $groupType->setLabel('variant')->shouldBeCalled();
        $groupType->setLabel('variantes')->shouldBeCalled();

        $this->update($groupType, $values, []);
    }
}
