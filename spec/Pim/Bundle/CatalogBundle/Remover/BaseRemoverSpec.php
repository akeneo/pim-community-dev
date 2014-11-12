<?php

namespace spec\Pim\Bundle\CatalogBundle\Remover;

use PhpSpec\ObjectBehavior;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\GroupType;

class BaseRemoverSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager)
    {
        $this->beConstructedWith($objectManager, 'Pim\Bundle\CatalogBundle\Entity\GroupType');
    }

    function it_is_a_remover()
    {
        $this->shouldHaveType('Pim\Component\Resource\Model\RemoverInterface');
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
        $this->remove($type, ['only_object' => true]);
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
}
