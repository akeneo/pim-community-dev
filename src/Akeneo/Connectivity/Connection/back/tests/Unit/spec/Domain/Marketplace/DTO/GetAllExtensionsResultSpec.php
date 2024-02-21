<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Marketplace\DTO;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllExtensionsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\Extension;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetAllExtensionsResultSpec extends ObjectBehavior
{
    public function let(Extension $extension): void
    {
        $this->beConstructedThrough('create', [12, [$extension]]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(GetAllExtensionsResult::class);
    }

    public function it_is_normalizable(Extension $extension): void
    {
        $extension->normalize()->willReturn([
            'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
        ]);

        $this->normalize()->shouldBe([
            'total' => 12,
            'extensions' => [
                [
                    'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                ],
            ],
        ]);
    }

    public function it_adds_analytics(
        Extension $extension,
        Extension $extensionWithAnalytics
    ): void {
        $queryParameters = [
            'utm_campaign' => 'foobar',
        ];

        $extension->normalize()->willReturn([
            'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
            'url' => 'https://marketplace.akeneo.com/extension/shopify-connector',
        ]);
        $extensionWithAnalytics->normalize()->willReturn([
            'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
            'url' => 'https://marketplace.akeneo.com/extension/shopify-connector?utm_campaign=foobar',
        ]);
        $extension->withAnalytics($queryParameters)->willReturn($extensionWithAnalytics);

        $this->withAnalytics($queryParameters)->normalize()->shouldEqual([
            'total' => 12,
            'extensions' => [
                [
                    'id' => 'cdbb6108-1914-4262-b728-aa4c679e33a8',
                    'url' => 'https://marketplace.akeneo.com/extension/shopify-connector?utm_campaign=foobar',
                ],
            ],
        ]);
    }
}
