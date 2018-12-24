<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\IdentifiersMapping;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AbstractApi;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Web Service to manage identifiers mapping.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IdentifiersMappingWebService extends AbstractApi implements AuthenticatedApiInterface
{
    /**
     * @param array $mapping
     *
     * @throws BadRequestException
     * @throws FranklinServerException
     * @throws InvalidTokenException
     */
    public function save(array $mapping): void
    {
        $route = $this->uriGenerator->generate('/api/mapping/identifiers');

        try {
            $this->httpClient->request('PUT', $route, [
                'form_params' => $mapping,
            ]);
        } catch (ServerException $e) {
            throw new FranklinServerException(sprintf(
                'Something went wrong on Franklin side when updating the identifiers mapping : %s',
                $e->getMessage()
            ));
        } catch (ClientException $e) {
            if (Response::HTTP_FORBIDDEN === $e->getCode()) {
                throw new InvalidTokenException('The Franklin token is missing or invalid');
            }

            throw new BadRequestException(sprintf(
                'Something went wrong when updating the identifiers mapping : %s',
                $e->getMessage()
            ));
        }
    }
}
