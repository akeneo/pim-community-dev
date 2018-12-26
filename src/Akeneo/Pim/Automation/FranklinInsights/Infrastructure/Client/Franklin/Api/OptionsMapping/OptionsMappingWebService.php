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
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\OptionsMapping;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class OptionsMappingWebService extends AbstractApi implements AuthenticatedApiInterface
{
    /**
     * {@inheritdoc}
     */
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
        } catch (ServerException | FranklinServerException $e) {
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
            if (Response::HTTP_FORBIDDEN === $e->getCode()) {
                throw new InvalidTokenException('The Franklin token is missing or invalid');
            }

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

    /**
     * {@inheritdoc}
     */
    public function update(string $familyCode, string $franklinAttributeId, array $attributeOptionsMapping): void
    {
        $route = $this->uriGenerator->generate(
            sprintf('/api/mapping/%s/attributes/%s/options', $familyCode, $franklinAttributeId)
        );

        try {
            $this->httpClient->request('PUT', $route, [
                'form_params' => $attributeOptionsMapping,
            ]);
        } catch (ServerException $e) {
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Franklin side when fetching the options mapping of family "%s" and attribute "%s" : %s',
                    $familyCode,
                    $franklinAttributeId,
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_FORBIDDEN === $e->getCode()) {
                throw new InvalidTokenException('The Franklin token is missing or invalid');
            }

            throw new BadRequestException(sprintf(
                'Something went wrong when fetching the options mapping of family "%s" and attribute "%s" : %s',
                $familyCode,
                $franklinAttributeId,
                $e->getMessage()
            ));
        }
    }

    /**
     * @param $responseData
     *
     * @throws FranklinServerException
     */
    private function validateResponseData($responseData): void
    {
        if (null === $responseData || !array_key_exists('mapping', $responseData)) {
            throw new FranklinServerException('Response data incorrect');
        }
    }
}
