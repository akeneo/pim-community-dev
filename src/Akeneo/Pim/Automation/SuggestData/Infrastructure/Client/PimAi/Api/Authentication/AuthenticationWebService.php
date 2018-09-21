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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AuthenticationWebService implements AuthenticationApiInterface
{
    private $uriGenerator;

    private $httpClient;

    public function __construct(UriGenerator $uriGenerator, Client $httpClient)
    {
        $this->uriGenerator = $uriGenerator;
        $this->httpClient = $httpClient;
    }

    /**
     * {inheritdoc}.
     */
    public function authenticate(?string $token): bool
    {
        $route = $this->uriGenerator->generate('/stats');

        try {
            $options = [];
            if (!empty($token)) {
                $options = [
                    'headers' => ['Authorization' => $token],
                ];
            }

            $response = $this->httpClient->request('GET', $route, $options);
            if (Response::HTTP_OK !== $response->getStatusCode()) {
                return false;
            }
        } catch (ClientException $e) {
            return false;
        }

        return true;
    }
}
