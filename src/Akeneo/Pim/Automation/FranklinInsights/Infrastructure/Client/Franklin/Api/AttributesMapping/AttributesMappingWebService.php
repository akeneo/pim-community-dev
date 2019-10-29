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
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\UnableToConnectToFranklinException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\AttributesMapping;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

class AttributesMappingWebService extends AbstractApi implements AuthenticatedApiInterface
{
    public function fetchByFamily(string $familyCode): AttributesMapping
    {
        $route = $this->uriGenerator->generate(sprintf('/api/mapping/%s/attributes', $familyCode));

        try {
            $response = $this->httpClient->request('GET', $route);
            $content = json_decode($response->getBody()->getContents(), true);

            if (null === $content || !array_key_exists('mapping', $content)) {
                throw new FranklinServerException('Response data incorrect! No "mapping" key found');
            }

            $attributesMapping = new AttributesMapping();

            foreach ($content['mapping'] as $attribute) {
                try {
                    $attributesMapping->add(new AttributeMapping($attribute));
                } catch (\Exception $e) {
                    $this->logger->error('Unable to hydrate following AttributeMapping object', [
                            'attribute' => $attribute,
                            'error_message' => $e->getMessage()
                        ]
                    );
                    continue;
                }
            }

            return $attributesMapping;
        } catch (ConnectException $e) {
            $this->logger->error('Cannot connect to Ask Franklin to retrieve attributes mapping', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);
            throw new UnableToConnectToFranklinException();
        } catch (ServerException | FranklinServerException $e) {
            $this->logger->error('Something went wrong on Ask Franklin side when retrieving attributes mapping', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'family_code' => $familyCode,
            ]);
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Franklin side when fetching the family attributes of family "%s" : %s',
                    $familyCode,
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                $this->logger->warning('Invalid token to retrieve attributes mapping', [
                    'exception' => $e->getMessage(),
                    'route' => $route,
                ]);
                throw new InvalidTokenException();
            }

            $this->logger->error('Invalid attributes mapping request sent to Ask Franklin', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'family_code' => $familyCode,
            ]);
            throw new BadRequestException(sprintf(
                'Something went wrong when fetching the family attributes of family "%s" : %s',
                $familyCode,
                $e->getMessage()
            ));
        }
    }

    public function save(string $familyCode, array $mapping): void
    {
        $route = $this->uriGenerator->generate(sprintf('/api/mapping/%s/attributes', $familyCode));

        try {
            $this->httpClient->request('PUT', $route, [
                'form_params' => $mapping,
            ]);
        } catch (ConnectException $e) {
            $this->logger->error('Cannot connect to Ask Franklin to save attributes mapping', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);
            throw new UnableToConnectToFranklinException();
        } catch (ServerException $e) {
            $this->logger->error('Something went wrong on Ask Franklin side during attributes mapping saving', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'request_body' => $mapping,
            ]);
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Franklin side when saving the attributes mapping of family "%s" : %s',
                    $familyCode,
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                $this->logger->warning('Invalid token to save attributes mapping', [
                    'exception' => $e->getMessage(),
                    'route' => $route,
                ]);
                throw new InvalidTokenException();
            }

            $this->logger->error('Invalid attributes mapping request sent to Ask Franklin', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'request_body' => $mapping,
            ]);
            throw new BadRequestException(sprintf(
                'Something went wrong when saving the attributes mapping of family "%s" : %s',
                $familyCode,
                $e->getMessage()
            ));
        }
    }
}
