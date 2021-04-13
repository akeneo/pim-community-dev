<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Common\Helper;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;

/**
 * This class is responsible for helping calling web routes.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class WebClientHelper
{
    private const SHARED_RESPONSES_FILE_PATH_PREFIX = __DIR__ . '/../../../shared/responses/';

    private RouterInterface $router;

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
        $errorMessage = sprintf(
            'Expected response status code is not the same as the actual. Failed with content %s',
            $response->getContent()
        );
        Assert::assertSame($statusCode, $response->getStatusCode(), $errorMessage);
        if ($expectedContent !== '') {
            Assert::assertJsonStringEqualsJsonString($expectedContent, $response->getContent(), 'Expected response content is not the same as the actual.');
            Assert::assertEquals($expectedContent, $response->getContent(), 'Expected response content is not the same as the actual.');
        }
    }

    public function assert403Forbidden(Response $response)
    {
        $expectedForbiddenContent = <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>An Error Occurred: Forbidden</title>
        <style>
            body { background-color: #fff; color: #222; font: 16px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; margin: 0; }
            .container { margin: 30px; max-width: 600px; }
            h1 { color: #dc3545; font-size: 24px; }
            h2 { font-size: 18px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Oops! An Error Occurred</h1>
            <h2>The server returned a "403 Forbidden".</h2>

            <p>
                Something is broken. Please let us know what you were doing when this error occurred.
                We will fix it as soon as possible. Sorry for any inconvenience caused.
            </p>
        </div>
    </body>
</html>

HTML;
        Assert::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode(), 'Expected 403 Forbidden response');
        Assert::assertSame(
            $expectedForbiddenContent,
            ltrim($response->getContent()),
            'The content of the 403 forbidden response is not the same'
        );
    }

    public function assert404NotFound(Response $response): void
    {
        Assert::assertSame(404, $response->getStatusCode());
    }

    public function assert400BadRequest(Response $response): void
    {
        Assert::assertSame(400, $response->getStatusCode());
    }

    public function assert202Accepted(Response $response): void
    {
        Assert::assertSame(202, $response->getStatusCode());
    }

    public function assert500ServerError(Response $response): void
    {
        Assert::assertSame(500, $response->getStatusCode());
    }

    public function assertRequest(Client $client, string $relativeFilePath): void
    {
        $response = $this->requestFromFile($client, $relativeFilePath);
        $this->assertFromFile($response, $relativeFilePath);
    }

    public function assertJsonFromFile(Response $response, string $relativeFilePath): void
    {
        $fileContents = file_get_contents(self::SHARED_RESPONSES_FILE_PATH_PREFIX . $relativeFilePath);
        $expectedResponseArray = json_decode($fileContents, true);
        $expectedResponseObject = json_decode($fileContents);
        if (null === $expectedResponseArray) {
            throw new \RuntimeException(
                sprintf('Impossible to load "%s" file, the file is not be present or is malformed', $relativeFilePath)
            );
        }
        $expectedContent = $this->getBody($expectedResponseArray['response']);
        $expectedContentJson = json_encode($expectedResponseObject->response->body);

        $errorMessage = sprintf(
            'Expected response status code is not the same as the actual. Failed with content %s',
            $response->getContent()
        );
        Assert::assertSame($expectedResponseArray['response']['status'], $response->getStatusCode(), $errorMessage);

        $expectedContent !== ''
            ? Assert::assertJsonStringEqualsJsonString($expectedContentJson, $response->getContent(), 'Expected response content is not the same as the actual.')
            : Assert::assertSame('', $response->getContent(), 'Expected response content should be empty but it is not.');

        if (isset($expectedResponseArray['response']['headers'])) {
            $this->assertResponseHeaders($expectedResponseArray['response']['headers'], $response);
        }
    }

    public function requestFromFile(Client $client, string $relativeFilePath): Response
    {
        $fileData = json_decode(file_get_contents(self::SHARED_RESPONSES_FILE_PATH_PREFIX . $relativeFilePath), true);
        if (null === $fileData) {
            throw new \RuntimeException(
                sprintf('Impossible to load "%s" file, the file is not be present or is malformed', $relativeFilePath)
            );
        }
        $request = $fileData['request'];
        $headers = $this->getRequestHeaders($request);
        $requestFiles = $this->getRequestFiles($request);
        $body = $this->getBody($request);
        $url = $this->router->generate($request['route'], $request['query']);

        $client->request($request['method'], $url, [], $requestFiles, $headers, $body);

        return $client->getResponse();
    }

    public function assertStreamedResponseFromFile(StreamedResponse $response, string $responseContent, string $relativeFilePath): void
    {
        $expectedResponse = json_decode(file_get_contents(self::SHARED_RESPONSES_FILE_PATH_PREFIX . $relativeFilePath), true);
        if (null === $expectedResponse) {
            throw new \RuntimeException(
                sprintf('Impossible to load "%s" file, the file is not be present or is malformed', $relativeFilePath)
            );
        }

        $statusCodeErrorMessage = sprintf(
            'Expected response status code is not the same as the actual. Failed with content %s',
            $response->getContent()
        );
        Assert::assertSame($expectedResponse['response']['status'], $response->getStatusCode(), $statusCodeErrorMessage);
        Assert::assertSame($expectedResponse['response']['body'], $responseContent, 'Expected response content is not the same as the actual.');

        if (isset($expectedResponse['response']['headers'])) {
            $this->assertResponseHeaders($expectedResponse['response']['headers'], $response);
        }
    }

    private function assertFromFile(Response $response, string $relativeFilePath): void
    {
        $expectedResponse = json_decode(file_get_contents(self::SHARED_RESPONSES_FILE_PATH_PREFIX . $relativeFilePath), true);
        if (null === $expectedResponse) {
            throw new \RuntimeException(
                sprintf('Impossible to load "%s" file, the file is not be present or is malformed', $relativeFilePath)
            );
        }
        $expectedContent = $this->getBody($expectedResponse['response']);
        $this->assertResponse($response, $expectedResponse['response']['status'], $expectedContent);
    }

    private function getBody(array $expectedResponse): string
    {
        $expectedContent = '';
        if (array_key_exists('body', $expectedResponse)) {
            $expectedContent = $this->encodeBody($expectedResponse['body']);
        }

        return $expectedContent;
    }

    private function encodeBody($expectedBody): string
    {
        if (is_array($expectedBody)) {
            return json_encode($expectedBody, JSON_HEX_QUOT);
        }
        if (!$expectedBody || empty($expectedBody)) {
            return '';
        }

        return json_encode($expectedBody, JSON_HEX_QUOT);
    }

    private function assertResponseHeaders(array $expectedHeaders, Response $response): void
    {
        foreach ($expectedHeaders as $headerKey => $headerValue) {
            Assert::assertTrue($response->headers->has($headerKey), 'Expected header does not exist.');
            Assert::assertSame($headerValue, $response->headers->get($headerKey), 'Expected header has not the same value in the response.');
        }
    }

    private function getRequestHeaders(array $request): array
    {
        $headers = [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
            'CONTENT_TYPE'          => 'application/json',
        ];

        if (isset($request['headers'])) {
            $headers = array_merge($headers, $request['headers']);
        }

        return $headers;
    }

    private function getRequestFiles(array $request): array
    {
        $files = [];

        if (isset($request['files'])) {
            $files = array_map(function ($requestFile) {
                return new UploadedFile(
                    self::SHARED_RESPONSES_FILE_PATH_PREFIX . $requestFile['path'],
                    $requestFile['name'],
                    $requestFile['mime_type']
                );
            }, $request['files']);
        }

        return $files;
    }
}
