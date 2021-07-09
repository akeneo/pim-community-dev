<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace;

use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceAliases;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Platform\VersionProviderInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebMarketplaceAliasesSpec extends ObjectBehavior
{
    public function let(
        VersionProviderInterface $versionProvider
    ) {
        $this->beConstructedWith($versionProvider);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(WebMarketplaceAliases::class);
        $this->shouldImplement(WebMarketplaceAliasesInterface::class);
    }

    public function it_returns_the_utm_campaign_when_ge(
        VersionProviderInterface $versionProvider
    ) {
        $versionProvider->getEdition()->willReturn('GE');

        $this->getUtmCampaign()->shouldReturn('connect_ge');
    }

    public function it_returns_null_as_utm_campaign_when_unknown_edition(
        VersionProviderInterface $versionProvider
    ) {
        $versionProvider->getEdition()->willReturn('Foo');

        $this->getUtmCampaign()->shouldReturn(null);
    }

    public function it_returns_the_edition_when_ge(
        VersionProviderInterface $versionProvider
    ) {
        $versionProvider->getEdition()->willReturn('GE');

        $this->getEdition()->shouldReturn('growth-edition');
    }

    public function it_returns_the_edition_when_ce(
        VersionProviderInterface $versionProvider
    ) {
        $versionProvider->getEdition()->willReturn('CE');

        $this->getEdition()->shouldReturn('community-edition');
    }

    public function it_returns_the_ce_edition_by_default_when_unknown_edition(
        VersionProviderInterface $versionProvider
    ) {
        $versionProvider->getEdition()->willReturn('Foo');

        $this->getEdition()->shouldReturn('community-edition');
    }

    public function it_returns_the_version_when_semantic(
        VersionProviderInterface $versionProvider
    ) {
        $versionProvider->getVersion()->willReturn('5.0.3');

        $this->getVersion()->shouldReturn('5.0');
    }

    public function it_returns_null_when_unsupported_version(
        VersionProviderInterface $versionProvider
    ) {
        $versionProvider->getVersion()->willReturn('master');

        $this->getVersion()->shouldReturn(null);
    }
}
