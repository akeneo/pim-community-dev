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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\OptionsMapping;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AbstractApi;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\OptionsMapping;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class OptionsMappingWebService extends AbstractApi implements OptionsMappingInterface
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
            if (null === $responseData) {
                throw new FranklinServerException();
            }

            return new OptionsMapping($responseData);
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

        $this->httpClient->request('PUT', $route, [
            'form_params' => $attributeOptionsMapping,
        ]);
    }
}
