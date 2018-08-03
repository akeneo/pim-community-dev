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
     * {inheritdoc}
     */
    public function authenticate(?string $token): bool
    {
        //Temporary hardcoded url to really ensure the token is OK, because we have no "check token" route and PIM.ai does not check the token for "/" uri.
        $route = $this->uriGenerator->generate('/subscriptions/7cca51be-bbf0-4b03-9338-555d50c7f357');

        try {
            $options = [];
            if (! empty($token)) {
                $options = [
                    'headers' => ['Authorization' => $token],
                ];
            }

            $response = $this->httpClient->request('GET', $route, $options);
            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return false;
            }
        } catch (ClientException $e) {
            return false;
        }

        return true;
    }
}
