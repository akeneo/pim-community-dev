<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceAnalyticsGenerator;
use Akeneo\Connectivity\Connection\Application\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MarketplaceAnalyticsGeneratorSpec extends ObjectBehavior
{
    public function let(
        GetUserProfileQueryInterface $getUserProfileQuery,
        WebMarketplaceAliasesInterface $webMarketplaceAliases,
        PimUrl $pimUrl
    ): void {
        $this->beConstructedWith(
            $getUserProfileQuery,
            $webMarketplaceAliases,
            $pimUrl
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(MarketplaceAnalyticsGenerator::class);
    }

    public function it_generates_extension_query_parameters_without_campaign_when_undefined(
        GetUserProfileQueryInterface $getUserProfileQuery,
        WebMarketplaceAliasesInterface $webMarketplaceAliases,
        PimUrl $pimUrl
    ): void {
        $webMarketplaceAliases->getUtmCampaign()->willReturn(null);
        $pimUrl->getPimUrl()->willReturn('http://my-akeneo.test');
        $getUserProfileQuery->execute('julia')->willReturn('manager');

        $this
            ->getExtensionQueryParameters('julia')
            ->shouldReturn(
                [
                    'utm_medium' => 'pim',
                    'utm_content' => 'extension_link',
                    'utm_source' => 'http://my-akeneo.test',
                    'utm_term' => 'manager',
                ]
            );
    }

    public function it_generates_extension_query_parameters_for_the_growth_edition_environment(
        GetUserProfileQueryInterface $getUserProfileQuery,
        WebMarketplaceAliasesInterface $webMarketplaceAliases,
        PimUrl $pimUrl
    ): void {
        $webMarketplaceAliases->getUtmCampaign()->willReturn('connect_ge');
        $pimUrl->getPimUrl()->willReturn('http://my-akeneo.test');
        $getUserProfileQuery->execute('julia')->willReturn('manager');

        $this
            ->getExtensionQueryParameters('julia')
            ->shouldReturn(
                [
                    'utm_medium' => 'pim',
                    'utm_content' => 'extension_link',
                    'utm_source' => 'http://my-akeneo.test',
                    'utm_term' => 'manager',
                    'utm_campaign' => 'connect_ge',
                ]
            );
    }

    public function it_generates_extension_query_parameters_without_profile_when_missing(
        GetUserProfileQueryInterface $getUserProfileQuery,
        WebMarketplaceAliasesInterface $webMarketplaceAliases,
        PimUrl $pimUrl
    ): void {
        $webMarketplaceAliases->getUtmCampaign()->willReturn('connect_ge');
        $pimUrl->getPimUrl()->willReturn('http://my-akeneo.test');
        $getUserProfileQuery->execute('julia')->willReturn(null);

        $this
            ->getExtensionQueryParameters('julia')
            ->shouldReturn(
                [
                    'utm_medium' => 'pim',
                    'utm_content' => 'extension_link',
                    'utm_source' => 'http://my-akeneo.test',
                    'utm_campaign' => 'connect_ge',
                ]
            );
    }
}
