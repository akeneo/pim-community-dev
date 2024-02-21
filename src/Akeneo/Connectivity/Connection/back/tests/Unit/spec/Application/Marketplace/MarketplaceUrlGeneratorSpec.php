<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\MarketplaceUrlGeneratorInterface;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use PhpSpec\ObjectBehavior;

class MarketplaceUrlGeneratorSpec extends ObjectBehavior
{
    public function let(
        VersionProviderInterface $versionProvider,
        GetUserProfileQueryInterface $getUserProfileQuery
    ): void {
        $this->beConstructedWith(
            'https://marketplace.akeneo.test',
            $versionProvider,
            'http://my-akeneo.test',
            $getUserProfileQuery
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(MarketplaceUrlGenerator::class);
        $this->shouldImplement(MarketplaceUrlGeneratorInterface::class);
    }

    public function it_generates_an_url_to_the_serenity_marketplace(
        VersionProviderInterface $versionProvider,
        GetUserProfileQueryInterface $getUserProfileQuery
    ): void {
        $versionProvider->getEdition()->willReturn('Serenity');
        $getUserProfileQuery->execute('willy')->willReturn('manager');

        $this
            ->generateUrl('willy')
            ->shouldReturn(
                'https://marketplace.akeneo.test/?' .
                'utm_medium=pim&' .
                'utm_content=marketplace_button&' .
                'utm_source=http%3A%2F%2Fmy-akeneo.test&' .
                'utm_term=manager&' .
                'utm_campaign=connect_serenity'
            );
    }

    public function it_generates_an_url_to_the_ge_marketplace(
        VersionProviderInterface $versionProvider,
        GetUserProfileQueryInterface $getUserProfileQuery
    ): void {
        $getUserProfileQuery->execute('willy')->willReturn('developer');
        $versionProvider->getEdition()->willReturn('GE');

        $this
            ->generateUrl('willy')
            ->shouldReturn(
                'https://marketplace.akeneo.test/?' .
                'utm_medium=pim&' .
                'utm_content=marketplace_button&' .
                'utm_source=http%3A%2F%2Fmy-akeneo.test&' .
                'utm_term=developer&' .
                'utm_campaign=connect_ge'
            );
    }

    public function it_generates_a_default_url(
        VersionProviderInterface $versionProvider,
        GetUserProfileQueryInterface $getUserProfileQuery
    ): void {
        $getUserProfileQuery->execute('willy')->willReturn(null);
        $versionProvider->getEdition()->willReturn('anything');

        $this
            ->generateUrl('willy')
            ->shouldReturn(
                'https://marketplace.akeneo.test/?' .
                'utm_medium=pim&' .
                'utm_content=marketplace_button&' .
                'utm_source=http%3A%2F%2Fmy-akeneo.test'
            );
    }

    public function it_throws_an_exception_if_the_market_place_url_is_not_an_url(
        VersionProviderInterface $versionProvider,
        GetUserProfileQueryInterface $getUserProfileQuery
    ): void {
        $this->beConstructedWith('coucou', $versionProvider, 'http://my-akeneo.test', $getUserProfileQuery);
        $this
            ->shouldThrow(new \InvalidArgumentException('$marketplaceUrl must be a valid URL.'))
            ->duringInstantiation();
    }
}
