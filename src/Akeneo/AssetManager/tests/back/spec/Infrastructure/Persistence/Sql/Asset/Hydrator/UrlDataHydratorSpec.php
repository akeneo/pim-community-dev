<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\UrlAttribute;
use Akeneo\AssetManager\Domain\Model\Asset\Value\UrlData;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\UrlDataHydrator;
use PhpSpec\ObjectBehavior;

class UrlDataHydratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UrlDataHydrator::class);
    }

    function it_only_supports_hydrate_data_of_url_attribute(
        UrlAttribute $url,
        ImageAttribute $image
    ) {
        $this->supports($url)->shouldReturn(true);
        $this->supports($image)->shouldReturn(false);
    }

    function it_hydrates_url_data(UrlAttribute $urlAttribute)
    {
        $urlData = $this->hydrate('house_255311', $urlAttribute);
        $urlData->shouldBeAnInstanceOf(UrlData::class);
        $urlData->normalize()->shouldReturn('house_255311');
    }
}
