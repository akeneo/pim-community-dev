<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Helper;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class WebClientHelper
{
    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function callRoute(
        KernelBrowser $client,
        string $route,
        array $routeArguments = [],
        string $method = 'GET',
        array $headers = [],
        array $parameters = [],
        string $content = null
    ): void {
        $url = $this->router->generate($route, $routeArguments);
        $client->request($method, $url, $parameters, [], $headers, $content);
    }

    public function callApiRoute(
        KernelBrowser $client,
        string $route,
        array $routeArguments = [],
        string $method = 'GET',
        array $parameters = [],
        string $content = null
    ): void {
        $headers = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'CONTENT_TYPE' => 'application/json',
        ];
        $url = $this->router->generate($route, $routeArguments);
        $client->request($method, $url, $parameters, [], $headers, $content);
    }

    public function assertStatusCode(Response $response, int $statusCode): void
    {
        Assert::assertSame($statusCode, $response->getStatusCode(), sprintf(
            'Expected response status code is not the same as the actual. Failed with content %s',
            $response->getContent()
        ));
    }

    public function assertContent(Response $response, string $expectedContent = ''): void
    {
        Assert::assertJsonStringEqualsJsonString(
            $expectedContent,
            $response->getContent(),
            'Expected response content is not the same as the actual.'
        );
        Assert::assertEquals(
            $expectedContent,
            $response->getContent(),
            'Expected response content is not the same as the actual.'
        );
    }

    public function assertResponse(Response $response, int $statusCode, string $expectedContent): void
    {
        $this->assertStatusCode($response, $statusCode);
        $this->assertContent($response, $expectedContent);
    }
}
