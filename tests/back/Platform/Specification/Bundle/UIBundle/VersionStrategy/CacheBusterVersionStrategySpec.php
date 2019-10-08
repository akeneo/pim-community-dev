<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\VersionStrategy;

use Akeneo\Platform\Bundle\UIBundle\VersionStrategy\CacheBusterVersionStrategy;
use Akeneo\Platform\VersionProviderInterface;
use PhpSpec\ObjectBehavior;

class CacheBusterVersionStrategySpec extends ObjectBehavior
{
    function let(VersionProviderInterface $versionProvider)
    {
        $this->beConstructedWith($versionProvider);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CacheBusterVersionStrategy::class);
    }

    public function it_returns_the_pim_patch_version($versionProvider)
    {
        $versionProvider->getPatch()->willReturn('2.0.1');

        $this->getVersion('')->shouldReturn('2.0.1');
    }

    public function it_returns_the_versioned_asset_path($versionProvider)
    {
        $versionProvider->getPatch()->willReturn('2.0.2');
        $hash = md5('2.0.2');

        $this->applyVersion('css/pim.css')->shouldReturn('css/pim.css?' . $hash);
    }

    public function it_returns_the_versioned_asset_path_with_leading_slash($versionProvider)
    {
        $versionProvider->getPatch()->willReturn('1.7.8');
        $hash = md5('1.7.8');

        $this->applyVersion('/js/main.dist.js')->shouldReturn('/js/main.dist.js?' . $hash);
    }
}
