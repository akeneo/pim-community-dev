<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\IntegrationTestsBundle\Helper;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * This class is responsible for helping calling web routes.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebClientHelper
{
    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function callRoute(
        Client $client,
        string $route,
        array $arguments = [],
        string $method = 'GET',
        array $headers = [],
        $content
    ): void {
        $url = $this->router->generate($route, $arguments);
        $client->request($method, $url, [], [], $headers, json_encode($content));
    }

    public function assertResponse(Response $response, string $statusCode, string $expectedContent = ''): void
    {
        Assert::assertEquals($statusCode, $response->getStatusCode());
        Assert::assertEquals($expectedContent, $response->getContent());
    }

    public function assert404(Response $response)
    {
        Assert::assertEquals('404', $response->getStatusCode());
    }
}
