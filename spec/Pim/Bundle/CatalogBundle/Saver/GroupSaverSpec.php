<?php

namespace spec\Pim\Bundle\CatalogBundle\Saver;

use PhpSpec\ObjectBehavior;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Component\Resource\Model\BulkSaverInterface;

class GroupSaverSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, BulkSaverInterface $productSaver)
    {
        $this->beConstructedWith($objectManager, $productSaver);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Pim\Component\Resource\Model\SaverInterface');
    }

    function it_throws_exception_when_save_anything_else_than_a_group()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\CatalogBundle\Model\GroupInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }
}
