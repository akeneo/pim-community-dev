<?php

namespace spec\Pim\Bundle\VersioningBundle\EventSubscriber;

use PhpSpec\ObjectBehavior;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\Common\EventSubscriber;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\UpdateGuesser\ChainedUpdateGuesser;

class AddVersionSubscriberSpec extends ObjectBehavior
{
    function let(VersionManager $versionManager, ChainedUpdateGuesser $guesser, NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($versionManager, $guesser, $normalizer);
    }

    function it_is_a_doctrine_event_listener()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_on_and_post_flush_events()
    {
        $this->getSubscribedEvents()->shouldReturn(['onFlush', 'postFlush']);
    }
}
