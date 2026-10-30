<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\MarketplaceUrlGeneratorInterface;
use PhpSpec\ObjectBehavior;

class MarketplaceUrlGeneratorSpec extends ObjectBehavior
{
    public function let(
        GetUserProfileQueryInterface $getUserProfileQuery
    ): void {
        $this->beConstructedWith(
            'https://marketplace.akeneo.test',
            'http://my-akeneo.test',
            $getUserProfileQuery
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MarketplaceUrlGenerator::class);
        $this->shouldImplement(MarketplaceUrlGeneratorInterface::class);
    }

    public function it_generates_a_default_url(
        GetUserProfileQueryInterface $getUserProfileQuery
    ): void {
        $getUserProfileQuery->execute('willy')->willReturn(null);

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
        GetUserProfileQueryInterface $getUserProfileQuery
    ): void {
        $this->beConstructedWith('coucou', 'http://my-akeneo.test', $getUserProfileQuery);
        $this
            ->shouldThrow(new \InvalidArgumentException('$marketplaceUrl must be a valid URL.'))
            ->duringInstantiation();
    }
}
