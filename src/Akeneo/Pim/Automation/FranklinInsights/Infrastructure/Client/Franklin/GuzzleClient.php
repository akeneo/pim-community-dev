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
    /** @var ClientInterface */
    private $httpClient;

    /** @var string */
    private $token;

    /**
     * @param ClientInterface $httpClient
     */
    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        $options = $options + [
            'headers' => ['Authorization' => $this->token],
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
