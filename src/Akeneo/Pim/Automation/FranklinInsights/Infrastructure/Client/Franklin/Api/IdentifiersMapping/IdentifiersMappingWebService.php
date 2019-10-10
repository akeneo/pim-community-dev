<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\IdentifiersMapping;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AbstractApi;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\UnableToConnectToFranklinException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

class IdentifiersMappingWebService extends AbstractApi implements AuthenticatedApiInterface
{
    public function save(array $mapping): void
    {
        $route = $this->uriGenerator->generate('/api/mapping/identifiers');

        try {
            $this->httpClient->request('PUT', $route, [
                'form_params' => $mapping,
            ]);
        } catch (ConnectException $e) {
            $this->logger->error('Cannot connect to Ask Franklin to save identifiers mapping', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);
            throw new UnableToConnectToFranklinException();
        } catch (ServerException $e) {
            $this->logger->error('Something went wrong on Ask Franklin side during identifiers mapping saving', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'request_body' => $mapping,
            ]);
            throw new FranklinServerException(sprintf(
                'Something went wrong on Franklin side when updating the identifiers mapping : %s',
                $e->getMessage()
            ));
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                $this->logger->warning('Invalid token to save identifiers mapping', [
                    'exception' => $e->getMessage(),
                    'route' => $route,
                ]);
                throw new InvalidTokenException();
            }

            $this->logger->error('Invalid identifiers mapping request sent to Ask Franklin', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'request_body' => $mapping,
            ]);
            throw new BadRequestException(sprintf(
                'Something went wrong when updating the identifiers mapping : %s',
                $e->getMessage()
            ));
        }
    }
}
