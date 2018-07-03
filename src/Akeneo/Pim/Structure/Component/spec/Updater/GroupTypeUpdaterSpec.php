<?php

namespace spec\Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;

class GroupTypeUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Pim\Structure\Component\Updater\GroupTypeUpdater');
    }

    function it_is_an_updater()
    {
        $this->shouldImplement('Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throw_an_exception_when_trying_to_update_anything_else_than_a_group_type()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'Akeneo\Pim\Structure\Component\Model\GroupTypeInterface'
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
