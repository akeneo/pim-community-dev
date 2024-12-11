<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\tests\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

trait MockGuzzle
{
    protected static array $guzzleRequests = [];

    protected function resetGuzzleMock(): void
    {
        self::$guzzleRequests = [];
    }

    protected function mockGuzzleResponse(
        MockObject&Client $client,
        string $method,
        array $arguments,
        callable $contents,
        array $headers = [],
        int $statusCode = 200,
    ): void {
        $client
            ->method($method)
            ->with(...$arguments)
            ->willReturnCallback(
                function (string $method, UriInterface $path, array $options) use (
                    $contents,
                    $statusCode,
                    $headers,
                ): ResponseInterface {
                    if (!isset(self::$guzzleRequests[$method.' '.$path->getPath()])) {
                        self::$guzzleRequests[$method.' '.$path->getPath()] = [];
                    }
                    self::$guzzleRequests[$method.' '.$path->getPath()][] = $options;

                    $response = $this->createMock(ResponseInterface::class);
                    $body = $this->createMock(StreamInterface::class);

                    $response->method('getStatusCode')->willReturn($statusCode);
                    $response->method('getBody')->willReturn($body);
                    $response->method('getHeaders')->willReturn($headers);
                    $body->method('getContents')->willReturn($contents($options));

                    if (!empty($headers)) {
                        $keyHeaders = array_keys($headers);
                        $valueHeaders = array_values($headers);

                        $invokedCount = $this->exactly(count($headers));
                        $response
                            ->expects($invokedCount)
                            ->method('getHeaderLine')
                            ->willReturnCallback(function ($parameters) use ($invokedCount, $keyHeaders, $valueHeaders) {
                                $this->assertSame($keyHeaders[$invokedCount->getInvocationCount()-1], $parameters);
                                return $valueHeaders[$invokedCount->getInvocationCount()-1];
                            })
                        ;
                    }

                    return $response;
                },
            );
    }

    protected function mockGuzzleException(
        MockObject&Client $client,
        string $method,
        array $arguments,
        array $headers = [],
    ) {
        $client
            ->method($method)
            ->with(...$arguments)
            ->willThrowException(new RequestException('RequestException', new Request($arguments[0], $arguments[1], $headers)));
    }

    protected function assertGuzzleRequestWasMade(
        string $method,
        string $path,
        array $options,
    ): void {
        if (!isset(self::$guzzleRequests[$method.' '.$path])) {
            throw new ExpectationFailedException(\sprintf('No HTTP requests were made to "%s %s"', $method, $path));
        }

        $match = false;
        foreach (self::$guzzleRequests[$method.' '.$path] as $snapshot) {
            if ($this->arrayMatchPartial($snapshot, $options)) {
                $match = true;
                break;
            }
        }

        if (!$match) {
            throw new ExpectationFailedException(\sprintf('No HTTP requests made to "%s %s" match.', $method, $path));
        }
    }

    private function arrayMatchPartial(array $full, array $partial): bool
    {
        return $this->recursiveArrayIntersect($full, $partial) === $partial;
    }

    private function recursiveArrayIntersect(&$a, &$b): mixed
    {
        if (!\is_array($a) || !\is_array($b)) {
            return (string) $a === (string) $b ? $a : null;
        }
        $keys = \array_intersect(\array_keys($a), \array_keys($b));
        $result = [];
        foreach ($keys as $key) {
            $sub = $this->recursiveArrayIntersect($a[$key], $b[$key]);
            $result[$key] = $sub;
        }

        return $result;
    }
}
