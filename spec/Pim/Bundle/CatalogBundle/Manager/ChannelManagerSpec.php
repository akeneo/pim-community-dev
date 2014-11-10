<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\ChannelRepository;
use Symfony\Component\Security\Core\SecurityContext;

class ChannelManagerSpec extends ObjectBehavior
{


    function let(
        ObjectManager $objectManager,
        SecurityContext $securityContext
    ) {
        $this->beConstructedWith($objectManager, $securityContext);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Pim\Component\Resource\Model\SaverInterface');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\ChannelManager');
    }

    function it_throws_exception_when_save_anything_else_than_a_channel()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\CatalogBundle\Entity\Channel", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }

    function it_provides_channels(ObjectManager $objectManager, ChannelRepository $repository)
    {
        $objectManager->getRepository('PimCatalogBundle:Channel')->willReturn($repository);
        $repository->findBy(array())->willReturn(array('mobile', 'ecommerce'));
        $this->getChannels()->shouldBeArray();
        $this->getChannels()->shouldHaveCount(2);
    }
}
