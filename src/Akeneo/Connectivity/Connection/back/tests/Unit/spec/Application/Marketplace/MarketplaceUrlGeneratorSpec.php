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
                'https://marketplace.akeneo.test/search?' .
                'props%5Bakeneo_editions%5D=community-edition&' .
                'utm_medium=pim&' .
                'utm_content=marketplace_button&' .
                'utm_source=http%3A%2F%2Fmy-akeneo.test'
            );
    }

    public function it_generates_an_url_with_the_user_profile(
        GetUserProfileQueryInterface $getUserProfileQuery
    ): void {
        $getUserProfileQuery->execute('willy')->willReturn('manager');

        $this
            ->generateUrl('willy')
            ->shouldReturn(
                'https://marketplace.akeneo.test/search?' .
                'props%5Bakeneo_editions%5D=community-edition&' .
                'utm_medium=pim&' .
                'utm_content=marketplace_button&' .
                'utm_source=http%3A%2F%2Fmy-akeneo.test&' .
                'utm_term=manager'
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
