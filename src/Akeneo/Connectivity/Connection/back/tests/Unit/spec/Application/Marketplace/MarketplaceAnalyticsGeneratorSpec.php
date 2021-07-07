<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceAnalyticsGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Akeneo\Platform\VersionProviderInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MarketplaceAnalyticsGeneratorSpec extends ObjectBehavior
{
    public function let(
        VersionProviderInterface $versionProvider,
        PimUrl $pimUrl,
        GetUserProfileQueryInterface $getUserProfileQuery
    ): void {
        $this->beConstructedWith(
            $getUserProfileQuery,
            $versionProvider,
            $pimUrl
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MarketplaceAnalyticsGenerator::class);
    }

    function it_generates_extension_query_parameters_for_the_serenity_environment(
        VersionProviderInterface $versionProvider,
        PimUrl $pimUrl,
        GetUserProfileQueryInterface $getUserProfileQuery
    ): void {
        $versionProvider->getEdition()->willReturn('Serenity');
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
                    'utm_campaign' => 'connect_serenity',
                ]
            );
    }

    function it_generates_extension_query_parameters_for_the_growth_edition_environment(
        VersionProviderInterface $versionProvider,
        PimUrl $pimUrl,
        GetUserProfileQueryInterface $getUserProfileQuery
    ): void {
        $versionProvider->getEdition()->willReturn('GE');
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

    function it_generates_extension_query_parameters_without_profile_when_missing(
        VersionProviderInterface $versionProvider,
        PimUrl $pimUrl,
        GetUserProfileQueryInterface $getUserProfileQuery
    ): void {
        $versionProvider->getEdition()->willReturn('GE');
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
