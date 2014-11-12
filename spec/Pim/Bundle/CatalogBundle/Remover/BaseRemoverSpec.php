<?php

namespace spec\Pim\Bundle\CatalogBundle\Remover;

use PhpSpec\ObjectBehavior;
use Doctrine\Common\Persistence\ManagerRegistry;

class BaseRemoverSpec extends ObjectBehavior
{
    function let( ManagerRegistry $registry)
    {
        $this->beConstructedWith($registry, 'Pim\Bundle\CatalogBundle\Entity\GroupType');
    }

    function it_is_a_remover()
    {
        $this->shouldHaveType('Pim\Component\Resource\Model\RemoverInterface');
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
