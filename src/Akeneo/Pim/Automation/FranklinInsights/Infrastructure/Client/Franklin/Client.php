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

use GuzzleHttp\ClientInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class Client
{
    /** @var ClientInterface */
    private $httpClient;

    /** @var string */
    private $token;

    /**
     * @param ClientInterface $httpClient
     */
    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Send request to Franklin.
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request(string $method, string $uri, array $options = [])
    {
        $options = $options + [
            'headers' => ['Authorization' => $this->token],
        ];

        $response = $this->httpClient->request($method, $uri, $options);
        $response->getBody()->rewind();

        return $response;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }
}
