<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceAliases;
use Akeneo\Platform\Bundle\PimVersionBundle\Version\FreeTrialVersion;
use Akeneo\Platform\Bundle\PimVersionBundle\Version\GrowthVersion;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebMarketplaceAliasesSpec extends ObjectBehavior
{
    public function let(
        VersionProviderInterface $versionProvider
    ): void {
        $this->beConstructedWith($versionProvider, new GrowthVersion(), new FreeTrialVersion());
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(WebMarketplaceAliases::class);
        $this->shouldImplement(WebMarketplaceAliasesInterface::class);
    }

    public function it_returns_the_utm_campaign_when_ge(
        VersionProviderInterface $versionProvider
    ): void {
        $versionProvider->getEdition()->willReturn('Growth Edition');

        $this->getUtmCampaign()->shouldReturn('connect_ge');
    }

    public function it_returns_null_as_utm_campaign_when_unknown_edition(
        VersionProviderInterface $versionProvider
    ): void {
        $versionProvider->getEdition()->willReturn('Foo');

        $this->getUtmCampaign()->shouldReturn(null);
    }

    public function it_returns_the_edition_when_ge(
        VersionProviderInterface $versionProvider
    ): void {
        $versionProvider->getEdition()->willReturn('Growth Edition');

        $this->getEdition()->shouldReturn('growth-edition');
    }

    public function it_returns_the_edition_when_free_trial(
        VersionProviderInterface $versionProvider
    ): void {
        $versionProvider->getEdition()->willReturn('Free Trial Edition');

        $this->getEdition()->shouldReturn('growth-edition');
    }

    public function it_returns_the_edition_when_ce(
        VersionProviderInterface $versionProvider
    ): void {
        $versionProvider->getEdition()->willReturn('CE');

        $this->getEdition()->shouldReturn('community-edition');
    }

    public function it_returns_the_ce_edition_by_default_when_unknown_edition(
        VersionProviderInterface $versionProvider
    ): void {
        $versionProvider->getEdition()->willReturn('Foo');

        $this->getEdition()->shouldReturn('community-edition');
    }

    public function it_returns_the_version_when_semantic(
        VersionProviderInterface $versionProvider
    ): void {
        $versionProvider->getVersion()->willReturn('5.0.3');

        $this->getVersion()->shouldReturn('5.0');
    }

    public function it_returns_null_when_unsupported_version(
        VersionProviderInterface $versionProvider
    ): void {
        $versionProvider->getVersion()->willReturn('20210713150654');

        $this->getVersion()->shouldReturn(null);
    }
}
