<?php

namespace spec\Pim\Bundle\CatalogBundle\Remover;

use PhpSpec\ObjectBehavior;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class BaseRemoverSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager)
    {
        $this->beConstructedWith($objectManager, 'Pim\Bundle\CatalogBundle\Entity\GroupType');
    }

    function it_is_a_remover()
    {
        $this->shouldHaveType('Akeneo\Component\Persistence\RemoverInterface');
    }

    function it_removes_the_object_and_flush_the_unit_of_work($objectManager, GroupType $type)
    {
        $objectManager->remove($type)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->remove($type);
    }

    function it_removes_the_object_and_dont_flush($objectManager, GroupType $type)
    {
        $objectManager->remove($type)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $this->remove($type, ['flush' => false]);
    }

    function it_removes_the_object_and_flush_only_the_object($objectManager, GroupType $type)
    {
        $objectManager->remove($type)->shouldBeCalled();
        $objectManager->flush($type)->shouldBeCalled();
        $this->remove($type, ['flush_only_object' => true]);
    }

    function it_throws_exception_when_remove_anything_else_than_the_expected_class()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\CatalogBundle\Entity\GroupType", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('remove', [$anythingElse]);
    }

    function it_throws_an_exception_when_unknown_removing_option_is_used(
        $objectManager,
        GroupType $groupType
    ) {
        $objectManager->remove(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(new InvalidOptionsException('The option "fake_option" does not exist. Known options are: "flush", "flush_only_object"'))
            ->duringRemove($groupType, ['fake_option' => true, 'flush' => false, 'flush_only_object' => false]);
    }
}
