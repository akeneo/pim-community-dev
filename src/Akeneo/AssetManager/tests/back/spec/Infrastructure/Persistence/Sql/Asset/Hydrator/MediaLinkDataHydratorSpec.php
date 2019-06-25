<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Asset\Value\MediaLinkData;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\MediaLinkDataHydrator;
use PhpSpec\ObjectBehavior;

class MediaLinkDataHydratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MediaLinkDataHydrator::class);
    }

    function it_only_supports_hydrate_data_of_mediaLink_attribute(
        MediaLinkAttribute $mediaLink,
        ImageAttribute $image
    ) {
        $this->supports($mediaLink)->shouldReturn(true);
        $this->supports($image)->shouldReturn(false);
    }

    function it_hydrates_mediaLink_data(MediaLinkAttribute $mediaLinkAttribute)
    {
        $mediaLinkData = $this->hydrate('house_255311', $mediaLinkAttribute);
        $mediaLinkData->shouldBeAnInstanceOf(MediaLinkData::class);
        $mediaLinkData->normalize()->shouldReturn('house_255311');
    }
}
