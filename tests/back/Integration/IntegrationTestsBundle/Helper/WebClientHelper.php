<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        $content = null
    ): void {
        $url = $this->router->generate($route, $arguments);
        $client->request($method, $url, [], [], $headers, json_encode($content));
    }

    public function assertResponse(Response $response, int $statusCode, string $expectedContent = ''): void
    {
        Assert::assertEquals($statusCode, $response->getStatusCode());
        Assert::assertEquals($expectedContent, $response->getContent());
    }

    public function assert404(Response $response): void
    {
        Assert::assertEquals(404, $response->getStatusCode());
    }
}
