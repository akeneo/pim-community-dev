<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\MarketplaceUrlGeneratorInterface;
use Akeneo\Platform\VersionProviderInterface;
use PhpSpec\ObjectBehavior;

class MarketplaceUrlGeneratorSpec extends ObjectBehavior
{
    public function let(VersionProviderInterface $versionProvider): void
    {
        $this->beConstructedWith('https://marketplace.akeneo.test', $versionProvider, 'http://my-akeneo.test');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MarketplaceUrlGenerator::class);
        $this->shouldImplement(MarketplaceUrlGeneratorInterface::class);
    }

    function it_generates_an_url_to_the_serenity_marketplace(
        VersionProviderInterface $versionProvider
    ): void {
        $versionProvider->getEdition()->willReturn('Serenity');

        $this
            ->generateUrl()
            ->shouldReturn(
                'https://marketplace.akeneo.test/discover/serenity/?' .
                'utm_medium=pim&' .
                'utm_content=marketplace_button&' .
                'utm_source=http%3A%2F%2Fmy-akeneo.test&' .
                'utm_campaign=connect_serenity'
            );
    }

    function it_generates_an_url_to_the_ge_marketplace(
        VersionProviderInterface $versionProvider
    ): void {
        $versionProvider->getEdition()->willReturn('GE');

        $this
            ->generateUrl()
            ->shouldReturn(
                'https://marketplace.akeneo.test/discover/growth-edition/?' .
                'utm_medium=pim&' .
                'utm_content=marketplace_button&' .
                'utm_source=http%3A%2F%2Fmy-akeneo.test&' .
                'utm_campaign=connect_ge'
            );
    }

    function it_generates_a_default_url(
        VersionProviderInterface $versionProvider
    ): void {
        $versionProvider->getEdition()->willReturn('anything');

        $this
            ->generateUrl()
            ->shouldReturn(
                'https://marketplace.akeneo.test/?' .
                'utm_medium=pim&' .
                'utm_content=marketplace_button&' .
                'utm_source=http%3A%2F%2Fmy-akeneo.test'
            );
    }

    function it_throws_an_exception_if_the_market_place_url_is_not_an_url(VersionProviderInterface $versionProvider)
    {
        $this->beConstructedWith('coucou', $versionProvider, 'http://my-akeneo.test');
        $this
            ->shouldThrow(new \InvalidArgumentException('$marketplaceUrl must be a valid URL.'))
            ->duringInstantiation();
    }
}
