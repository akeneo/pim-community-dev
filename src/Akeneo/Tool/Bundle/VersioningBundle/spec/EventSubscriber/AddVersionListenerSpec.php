<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddVersionListener;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AddVersionListenerSpec extends ObjectBehavior
{
    function let(
        VersionManager $versionManager,
        NormalizerInterface $versioningNormalizer,
        UpdateGuesserInterface $updateGuesser,
        VersionContext $versionContext
    ) {
        $this->beConstructedWith($versionManager, $versioningNormalizer, $updateGuesser, $versionContext);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(AddVersionListener::class);
    }
}
