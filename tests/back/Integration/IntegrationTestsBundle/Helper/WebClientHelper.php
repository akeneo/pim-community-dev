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
        Assert::assertSame($statusCode, $response->getStatusCode(), 'Expected request status code is not the same as the actual.');
        Assert::assertSame($expectedContent, $response->getContent(), 'Expected request content is not the same as the actual.');
    }

    public function assert403Forbidden(Response $response)
    {
        $expectedForbiddenContent = <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>An Error Occurred: Forbidden</title>
    </head>
    <body>
        <h1>Oops! An Error Occurred</h1>
        <h2>The server returned a "403 Forbidden".</h2>

        <div>
            Something is broken. Please let us know what you were doing when this error occurred.
            We will fix it as soon as possible. Sorry for any inconvenience caused.
        </div>
    </body>
</html>

HTML;
        Assert::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), 'Expected 403 Forbidden response');
        Assert::assertSame(
            $expectedForbiddenContent,
            $response->getContent(),
            'The content of the 403 forbidden response is not the same'
        );
    }

    public function assert404NotFound(Response $response): void
    {
        Assert::assertSame(404, $response->getStatusCode());
    }
}
