<?php

namespace spec\Pim\Bundle\EnrichBundle\VersionStrategy;

use Pim\Bundle\EnrichBundle\VersionStrategy\CacheBusterVersionStrategy;
use Pim\Bundle\CatalogBundle\VersionProviderInterface;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CacheBusterVersionStrategySpec extends ObjectBehavior
{
    public function let(VersionProviderInterface $versionProvider) {
        $this->beConstructedWith($versionProvider);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\VersionStrategy\CacheBusterVersionStrategy');
    }

    public function it_returns_the_pim_patch_version($versionProvider)
    {
        $versionProvider->getPatch()->willReturn('2.0.1');

        $this->getVersion('')->shouldReturn('2.0.1');
    }

    public function it_returns_the_versioned_asset_path($versionProvider)
    {
        $versionProvider->getPatch()->willReturn('2.0.2');

        $this->applyVersion('css/pim.css')->shouldReturn('css/pim.css?2.0.2');
    }

    public function it_returns_the_versioned_asset_path_with_leading_slash($versionProvider)
    {
        $versionProvider->getPatch()->willReturn('1.7.8');

        $this->applyVersion('/js/main.dist.js')->shouldReturn('/js/main.dist.js?1.7.8');
    }
}
