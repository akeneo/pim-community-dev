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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GuzzleClient implements ClientInterface
{
    /** @var Client */
    private $httpClient;

    /** @var string */
    private $token;

    /** @var int */
    private $timeout;

    /**
     * @param Client $httpClient
     * @param int $timeout
     */
    public function __construct(Client $httpClient, int $timeout = 10)
    {
        $this->httpClient = $httpClient;
        $this->timeout = $timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        $options = $options + [
            'headers' => ['Authorization' => $this->token],
            'timeout' => $this->timeout,
        ];

        $response = $this->httpClient->request($method, $uri, $options);
        $response->getBody()->rewind();

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }
}
