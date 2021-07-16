<?php

declare(strict_types=1);

namespace spec\AkeneoEnterprise\Connectivity\Connection\Infrastructure\Marketplace;

use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Platform\VersionProviderInterface;
use AkeneoEnterprise\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceAliases;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebMarketplaceAliasesSpec extends ObjectBehavior
{
    public function let(
        WebMarketplaceAliasesInterface $decorated,
        VersionProviderInterface $versionProvider
    ) {
        $this->beConstructedWith($decorated, $versionProvider);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(WebMarketplaceAliases::class);
        $this->shouldImplement(WebMarketplaceAliasesInterface::class);
    }

    public function it_returns_the_utm_campaign_when_serenity(
        VersionProviderInterface $versionProvider
    ) {
        $versionProvider->getEdition()->willReturn('Serenity');

        $this->getUtmCampaign()->shouldReturn('connect_serenity');
    }

    public function it_returns_null_when_unknown_edition(
        VersionProviderInterface $versionProvider
    ) {
        $versionProvider->getEdition()->willReturn('Foo');

        $this->getUtmCampaign()->shouldReturn(null);
    }

    public function it_returns_the_edition_when_serenity(
        VersionProviderInterface $versionProvider
    ) {
        $versionProvider->getEdition()->willReturn('Serenity');

        $this->getEdition()->shouldReturn('serenity');
    }

    public function it_returns_the_edition_when_ee(
        VersionProviderInterface $versionProvider
    ) {
        $versionProvider->getEdition()->willReturn('EE');

        $this->getEdition()->shouldReturn('enterprise-edition');
    }

    public function it_returns_the_ee_edition_by_default_when_unknown_edition(
        VersionProviderInterface $versionProvider
    ) {
        $versionProvider->getEdition()->willReturn('Foo');

        $this->getEdition()->shouldReturn('enterprise-edition');
    }

    public function it_returns_the_version_from_the_decorated(
        WebMarketplaceAliasesInterface $decorated
    ) {
        $decorated->getVersion()->willReturn('5.0');

        $this->getVersion()->shouldReturn('5.0');
    }
}
