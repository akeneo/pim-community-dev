<?php

namespace spec\Pim\Bundle\CatalogBundle\Saver;

use PhpSpec\ObjectBehavior;
use Doctrine\Common\Persistence\ObjectManager;

class BaseSaverSpec extends ObjectBehavior
{
    function let(ObjectManager $manager)
    {
        $this->beConstructedWith($manager, 'Pim\Bundle\CatalogBundle\Entity\GroupType');
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Pim\Component\Resource\Model\SaverInterface');
    }

    function it_throws_exception_when_save_anything_else_than_the_expected_class()
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
            ->during('save', [$anythingElse]);
    }
}
