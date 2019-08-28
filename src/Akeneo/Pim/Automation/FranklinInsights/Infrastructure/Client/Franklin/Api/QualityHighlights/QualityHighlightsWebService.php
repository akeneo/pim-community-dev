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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AbstractApi;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class QualityHighlightsWebService extends AbstractApi implements AuthenticatedApiInterface
{
    public function applyAttributes(array $attributes): void
    {
        $route = $this->uriGenerator->generate('/api/quality-highlights/structure/attributes');

        try {
            $this->httpClient->request('POST', $route, [
                'json' => $attributes,
            ]);
        } catch (ServerException $e) {
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Ask Franklin side when sending attributes : %s',
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                throw new InvalidTokenException();
            }

            throw new BadRequestException(sprintf(
                'Something went wrong when sending attributes (bad request) : %s',
                $e->getMessage()
            ));
        }
    }

    public function deleteAttribute(string $attributeCode)
    {
        $route = $this->uriGenerator->generate(sprintf('/api/quality-highlights/structure/attributes/%s', $attributeCode));

        try {
            $this->httpClient->request('DELETE', $route);
        } catch (ServerException $e) {
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Ask Franklin side when deleting an attribute : %s',
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                throw new InvalidTokenException();
            }

            throw new BadRequestException(sprintf(
                'Something went wrong when deleting an attribute (bad request) : %s',
                $e->getMessage()
            ));
        }
    }

    public function applyFamilies(array $families): void
    {
        $route = $this->uriGenerator->generate('/api/quality-highlights/structure/families');

        try {
            $this->httpClient->request('POST', $route, [
                'json' => $families,
            ]);
        } catch (ServerException $e) {
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Ask Franklin side when sending families : %s',
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                throw new InvalidTokenException();
            }

            throw new BadRequestException(sprintf(
                'Something went wrong when sending families (bad request) : %s',
                $e->getMessage()
            ));
        }
    }

    public function deleteFamily(string $familyCode)
    {
        $route = $this->uriGenerator->generate(sprintf('/api/quality-highlights/structure/families/%s', $familyCode));

        try {
            $this->httpClient->request('DELETE', $route);
        } catch (ServerException $e) {
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Ask Franklin side when deleting a family : %s',
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                throw new InvalidTokenException();
            }

            throw new BadRequestException(sprintf(
                'Something went wrong when deleting a family (bad request) : %s',
                $e->getMessage()
            ));
        }
    }
}
