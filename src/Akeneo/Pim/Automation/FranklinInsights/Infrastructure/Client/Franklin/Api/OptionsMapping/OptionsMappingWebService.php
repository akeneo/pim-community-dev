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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\OptionsMapping;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AbstractApi;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\UnableToConnectToFranklinException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\OptionsMapping;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

class OptionsMappingWebService extends AbstractApi implements AuthenticatedApiInterface
{
    public function fetchByFamilyAndAttribute(string $familyCode, string $franklinAttributeId): OptionsMapping
    {
        $route = $this->uriGenerator->generate(
            sprintf('/api/mapping/%s/attributes/%s/options', $familyCode, $franklinAttributeId)
        );

        try {
            $response = $this->httpClient->request('GET', $route);
            $responseData = json_decode($response->getBody()->getContents(), true);

            if (null === $responseData || !array_key_exists('mapping', $responseData)) {
                throw new FranklinServerException('Response data incorrect! No "mapping" key found');
            }

            return new OptionsMapping($responseData['mapping']);
        } catch (ConnectException $e) {
            $this->logger->error('Cannot connect to Ask Franklin to retrieve attribute options mapping', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);
            throw new UnableToConnectToFranklinException();
        } catch (ServerException | FranklinServerException $e) {
            $this->logger->error('Something went wrong on Ask Franklin side when retrieving attribute options mapping', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'family_code' => $familyCode,
                'franklin_attribute' => $franklinAttributeId,
            ]);
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Franklin side when fetching the options mapping ' .
                        'for attribute "%s" and family "%s": %s',
                    $franklinAttributeId,
                    $familyCode,
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                $this->logger->warning('Invalid token to retrieve attribute options mapping', [
                    'exception' => $e->getMessage(),
                    'route' => $route,
                ]);
                throw new InvalidTokenException();
            }

            $this->logger->error('Invalid attribute options mapping request sent to Ask Franklin', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'family_code' => $familyCode,
                'franklin_attribute' => $franklinAttributeId,
            ]);
            throw new BadRequestException(
                sprintf(
                    'Something went wrong when fetching the options mapping for attribute "%s" and family "%s": %s',
                    $franklinAttributeId,
                    $familyCode,
                    $e->getMessage()
                )
            );
        }
    }

    public function update(string $familyCode, string $franklinAttributeId, array $attributeOptionsMapping): void
    {
        $route = $this->uriGenerator->generate(
            sprintf('/api/mapping/%s/attributes/%s/options', $familyCode, $franklinAttributeId)
        );

        try {
            $this->httpClient->request('PUT', $route, [
                'form_params' => $attributeOptionsMapping,
            ]);
        } catch (ConnectException $e) {
            $this->logger->error('Cannot connect to Ask Franklin to save attribute options mapping', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);
            throw new UnableToConnectToFranklinException();
        } catch (ServerException $e) {
            $this->logger->error('Something went wrong on Ask Franklin side during attribute options mapping saving', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'family_code' => $familyCode,
                'franklin_attribute' => $franklinAttributeId,
                'options_mapping' => $attributeOptionsMapping,
            ]);
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Franklin side when fetching the options mapping of family "%s" and attribute "%s" : %s',
                    $familyCode,
                    $franklinAttributeId,
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                $this->logger->warning('Invalid token to save attribute options mapping', [
                    'exception' => $e->getMessage(),
                    'route' => $route,
                ]);
                throw new InvalidTokenException();
            }

            $this->logger->error('Invalid attribute options mapping request sent to Ask Franklin', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'family_code' => $familyCode,
                'franklin_attribute' => $franklinAttributeId,
                'options_mapping' => $attributeOptionsMapping,
            ]);
            throw new BadRequestException(sprintf(
                'Something went wrong when fetching the options mapping of family "%s" and attribute "%s" : %s',
                $familyCode,
                $franklinAttributeId,
                $e->getMessage()
            ));
        }
    }
}
