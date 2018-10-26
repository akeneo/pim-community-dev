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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\OptionsMapping;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\FranklinServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\OptionsMapping;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class OptionsMappingWebService implements OptionsMappingInterface
{
    /** @var UriGenerator */
    private $uriGenerator;

    /** @var Client */
    private $httpClient;

    /**
     * @param UriGenerator $uriGenerator
     * @param Client $httpClient
     */
    public function __construct(UriGenerator $uriGenerator, Client $httpClient)
    {
        $this->uriGenerator = $uriGenerator;
        $this->httpClient = $httpClient;
    }

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
                    'Something went wrong on PIM.ai side when fetching the options mapping ' .
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
}
