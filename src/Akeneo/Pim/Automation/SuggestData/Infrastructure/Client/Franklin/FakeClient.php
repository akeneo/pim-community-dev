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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FakeClient implements ClientInterface
{
    /** @const string */
    public const VALID_TOKEN = 'valid-token';

    /** @const string */
    public const INVALID_TOKEN = 'invalid-token';

    public const FAKE_PATH = __DIR__ . '/../../../tests/back/Resources/fake/franklin-api/';

    /** @var string */
    private $token;

    /** @var bool */
    private $hasCredits = true;

    /** @var string */
    private $lastFetchDate;

    /** @var array */
    private $identifiersMapping = [];

    private $attributesMapping = [];

    private $optionsMapping = [];

    /**
     * {@inheritdoc}
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        // Clean base uri
        $uri = str_replace('https://pim-ai.prod.cloud.akeneo.com/api/', '', $uri);

        $this->handleToken($method, $uri, $options);

        $this->handleCredits($method, $uri);

        if (false !== strpos($uri, 'mapping')) {
            return $this->handleMapping($method, $uri, $options);
        }

        if ('subscriptions/updated-since/yesterday' === $uri) {
            $filename = sprintf('subscriptions/updated-since/%s.json', $this->lastFetchDate);
            $jsonContent = file_get_contents(
                sprintf(__DIR__ . '/Api/resources/%s', $filename)
            );

            return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK, [], $jsonContent);
        }

        if ('subscriptions' === $uri) {
            // TODO: Assert sent parameters
            if (isset($options['form_params'][0]['asin'])) {
                $filename = sprintf('subscriptions/post/asin-%s.json', $options['form_params'][0]['asin']);
                $jsonContent = file_get_contents(
                    sprintf(__DIR__ . '/Api/resources/%s', $filename)
                );

                return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK, [], $jsonContent);
            }
        }

        // TODO: Should return null in order to break everything :D
        return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getIdentifiersMapping(): array
    {
        return $this->identifiersMapping;
    }

    public function getOptionsMapping(): array
    {
        return $this->optionsMapping;
    }

    public function disableCredit(): void
    {
        $this->hasCredits = false;
    }

    public function defineLastFetchDate($lastFetchDate): void
    {
        $this->lastFetchDate = $lastFetchDate;
    }

    private function handleToken(string $method, string $uri, array $options)
    {
        // api/stats does not need token or status
        if ('stats' === $uri) {
            if (self::VALID_TOKEN !== $options['headers']['Authorization']) {
                $request = new Request($method, $uri);
                $response = new \GuzzleHttp\Psr7\Response(Response::HTTP_FORBIDDEN);
                throw new ClientException('Invalid token', $request, $response);
            }

            return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK);
        }

        if (self::INVALID_TOKEN === $this->token) {
            $request = new Request($method, $uri);
            $response = new \GuzzleHttp\Psr7\Response(Response::HTTP_FORBIDDEN);
            throw new ClientException('Invalid token', $request, $response);
        }
    }

    /**
     * @param string $method
     * @param string $uri
     */
    private function handleCredits(string $method, string $uri): void
    {
        if (!$this->hasCredits) {
            $request = new Request($method, $uri);
            $response = new \GuzzleHttp\Psr7\Response(Response::HTTP_PAYMENT_REQUIRED);
            throw new ClientException('Invalid token', $request, $response);
        }
    }

    private function handleMapping(string $method, string $uri, array $options)
    {
        if ('GET' === $method) {
            $fakeFilepath = sprintf('%s/%s.json', realpath(self::FAKE_PATH), $uri);
            if (!file_exists($fakeFilepath)) {
                throw new \LogicException(
                    sprintf('File "%s" not found. The FakeClient cannot provide you fake data.', $fakeFilepath)
                );
            }

            return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK, [], file_get_contents($fakeFilepath));
        }
        if (false !== strpos($uri, 'identifiers')) {
            $this->identifiersMapping = $options['form_params'];
        } elseif (false !== strpos($uri, 'options')) {
            $this->optionsMapping = $options['form_params'];
        } elseif (false !== strpos($uri, 'attributes') && false === strpos($uri, 'options')) {
            $this->attributesMapping = $options['form_params'];
        } else {
            throw new \LogicException('Something went wrong when trying to save mapping');
        }

        return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK);
    }
}
