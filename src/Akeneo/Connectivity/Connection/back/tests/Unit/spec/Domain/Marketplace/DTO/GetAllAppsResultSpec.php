<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Marketplace\DTO;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllAppsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetAllAppsResultSpec extends ObjectBehavior
{
    public function let(App $app): void
    {
        $this->beConstructedThrough('create', [12, [$app]]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(GetAllAppsResult::class);
    }

    public function it_is_normalizable(App $app): void
    {
        $app->normalize()->willReturn([
            'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
        ]);

        $this->normalize()->shouldBe([
            'total' => 12,
            'apps' => [
                [
                    'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                ],
            ],
        ]);
    }

    public function it_adds_analytics(
        App $app,
        App $appWithAnalytics
    ): void {
        $queryParameters = [
            'utm_campaign' => 'foobar',
        ];

        $app->normalize()->willReturn([
            'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
            'url' => 'https://marketplace.akeneo.com/extension/shopify-connector',
        ]);
        $appWithAnalytics->normalize()->willReturn([
            'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
            'url' => 'https://marketplace.akeneo.com/extension/shopify-connector?utm_campaign=foobar',
        ]);
        $app->withAnalytics($queryParameters)->willReturn($appWithAnalytics);

        $this->withAnalytics($queryParameters)->normalize()->shouldEqual([
            'total' => 12,
            'apps' => [
                [
                    'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                    'url' => 'https://marketplace.akeneo.com/extension/shopify-connector?utm_campaign=foobar',
                ],
            ],
        ]);
    }

    public function it_adds_the_pim_url(
        App $app,
        App $appWithPimUrl
    ): void {
        $queryParameters = [
            'pim_url' => 'http://pim',
        ];

        $app->normalize()->willReturn([
            'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
            'activate_url' => 'https://extension.example/activate',
        ]);
        $appWithPimUrl->normalize()->willReturn([
            'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
            'activate_url' => 'https://extension.example/activate?pim_url=http%3A%2F%2Fpim',
        ]);
        $app->withPimUrlSource($queryParameters)->willReturn($appWithPimUrl);

        $this->withPimUrlSource($queryParameters)->normalize()->shouldEqual([
            'total' => 12,
            'apps' => [
                [
                    'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                    'activate_url' => 'https://extension.example/activate?pim_url=http%3A%2F%2Fpim',
                ],
            ],
        ]);
    }
}
