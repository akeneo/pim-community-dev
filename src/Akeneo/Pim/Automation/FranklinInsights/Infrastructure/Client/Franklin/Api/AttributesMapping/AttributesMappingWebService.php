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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AttributesMapping;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AbstractApi;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\AttributesMapping;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesMappingWebService extends AbstractApi implements AuthenticatedApiInterface
{
    /**
     * {@inheritdoc}
     */
    public function fetchByFamily(string $familyCode): AttributesMapping
    {
        $route = $this->uriGenerator->generate(sprintf('/api/mapping/%s/attributes', $familyCode));

        try {
            $response = $this->httpClient->request('GET', $route);
            $content = json_decode($response->getBody()->getContents(), true);

            if (null === $content || !array_key_exists('mapping', $content)) {
                throw new FranklinServerException('Response data incorrect! No "mapping" key found');
            }

            return new AttributesMapping($content['mapping']);
        } catch (ServerException | FranklinServerException $e) {
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Franklin side when fetching the family attributes of family "%s" : %s',
                    $familyCode,
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                throw new InvalidTokenException();
            }

            throw new BadRequestException(sprintf(
                'Something went wrong when fetching the family attributes of family "%s" : %s',
                $familyCode,
                $e->getMessage()
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $familyCode, array $mapping): void
    {
        $route = $this->uriGenerator->generate(sprintf('/api/mapping/%s/attributes', $familyCode));

        try {
            $this->httpClient->request('PUT', $route, [
                'form_params' => $mapping,
            ]);
        } catch (ServerException $e) {
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Franklin side when fetching the attributes mapping of family "%s" : %s',
                    $familyCode,
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                throw new InvalidTokenException();
            }

            throw new BadRequestException(sprintf(
                'Something went wrong when fetching the attributes mapping of family "%s" : %s',
                $familyCode,
                $e->getMessage()
            ));
        }
    }
}
