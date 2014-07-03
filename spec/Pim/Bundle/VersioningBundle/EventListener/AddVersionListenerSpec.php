<?php

namespace spec\Pim\Bundle\VersioningBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\ChainedUpdateGuesser;

class AddVersionListenerSpec extends ObjectBehavior
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
