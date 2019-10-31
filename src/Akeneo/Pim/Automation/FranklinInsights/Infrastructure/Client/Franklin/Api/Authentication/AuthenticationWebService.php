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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Authentication;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AbstractApi;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AuthenticationWebService extends AbstractApi
{
    /**
     * {@inheritdoc}
     */
    public function authenticate(?string $token): bool
    {
        $route = $this->uriGenerator->generate('/api/stats');

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
        } catch (ConnectException $e) {
            $this->logger->error('Cannot connect to Ask Franklin during authentication process', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);

            return false;
        } catch (ServerException | ClientException $e) {
            $this->logger->error('Authentication to Ask Franklin failed', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'token' => $token,
            ]);

            return false;
        }

        return true;
    }
}
