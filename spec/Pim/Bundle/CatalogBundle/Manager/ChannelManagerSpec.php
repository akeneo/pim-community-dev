<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;

class ChannelManagerSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        ChannelRepositoryInterface $repository,
        CompletenessManager $completenessManager
    ) {
        $this->beConstructedWith($objectManager, $repository, $completenessManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\ChannelManager');
    }

    function it_provides_channels(ObjectManager $objectManager, ChannelRepositoryInterface $repository)
    {
        $repository->findBy(array())->willReturn(array('mobile', 'ecommerce'));
        $this->getChannels()->shouldBeArray();
        $this->getChannels()->shouldHaveCount(2);
    }

    function it_provides_channel_choices(ObjectManager $objectManager, ChannelRepositoryInterface $repository, ChannelInterface $mobile, ChannelInterface $ecommerce)
    {
        $repository->findBy(array())->willReturn(array($mobile, $ecommerce));
        $mobile->getCode()->willReturn('mobile');
        $mobile->getLabel()->willReturn('Mobile');
        $ecommerce->getCode()->willReturn('ecommerce');
        $ecommerce->getLabel()->willReturn('Ecommerce');
        $this->getChannelChoices()->shouldReturn(['mobile' => 'Mobile', 'ecommerce' => 'Ecommerce']);
    }
}
