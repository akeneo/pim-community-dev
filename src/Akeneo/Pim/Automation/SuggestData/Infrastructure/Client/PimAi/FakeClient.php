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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi;

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

    /** @var string */
    private $token;

    /** @var bool */
    private $hasCredits = true;

    /** @var string */
    private $lastFetchDate;

    /** @var array */
    private $identifiersMapping = [];

    /**
     * {@inheritdoc}
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        // Clean base uri
        $uri = str_replace('https://pim-ai.prod.cloud.akeneo.com/api', '', $uri);

        $this->handleToken($method, $uri, $options);

        $this->handleCredits($method, $uri);

        if ('/mapping/identifiers' === $uri) {
            $this->identifiersMapping = $options['form_params'];
        }

        if ('/subscriptions/updated-since/yesterday' === $uri) {
            $filename = sprintf('subscriptions/updated-since/%s.json', $this->lastFetchDate);
            $jsonContent = file_get_contents(
                sprintf(__DIR__ . '/Api/resources/%s', $filename)
            );

            return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK, [], $jsonContent);
        }

        if ('/subscriptions' === $uri) {
            // TODO: Assert sent parameters
            if (isset($options['form_params'][0]['asin'])) {
                $filename = sprintf('subscriptions/post/asin-%s.json', $options['form_params'][0]['asin']);
                $jsonContent = file_get_contents(
                    sprintf(__DIR__ . '/Api/resources/%s', $filename)
                );

                return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK, [], $jsonContent);
            }
        }

        if ('/mapping/router/attributes' === $uri) {
            $filename = 'attributes-mapping-family-router.json';
            $jsonContent = file_get_contents(
                sprintf(__DIR__ . '/Api/resources/%s', $filename)
            );

            return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK, [], $jsonContent);
        }

        if ('/mapping/router/attributes/color/options' === $uri) {
            $filename = 'get_family_router_attribute_color.json';
            $jsonContent = file_get_contents(
                sprintf(
                    '%s/%s',
                    realpath(__DIR__ . '/../../../tests/back/Resources/fake/franklin-api/attribute-options-mapping'),
                    $filename
                )
            );

            return new \GuzzleHttp\Psr7\Response(Response::HTTP_OK, [], $jsonContent);
        }

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

    public function disableCredit(): void
    {
        $this->hasCredits = false;
    }

    public function defineLastFetchDate($lastFetchDate): void
    {
        $this->lastFetchDate = $lastFetchDate;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    private function handleToken(string $method, string $uri, array $options): \GuzzleHttp\Psr7\Response
    {
        // api/stats does not need token or status
        if ('/api/stats' === $uri) {
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
}
