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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AttributeOptionsMapping;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\PimAiServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\AttributeOptionsMapping;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeOptionsMappingWebService implements AttributeOptionsMappingInterface
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
    public function fetchByFamilyAndAttribute(string $familyCode, string $franklinAttributeId): AttributeOptionsMapping
    {
        $route = $this->uriGenerator->generate(
            sprintf('/api/mapping/%s/attributes/%s/options', $familyCode, $franklinAttributeId)
        );

        try {
            $response = $this->httpClient->request('GET', $route);

            $responseContent = $response->getBody()->getContents();
            // TODO: Temporary mock. Should be removed once Franklin API end-point will be available
//            $mockPath = realpath(__DIR__ . '/../resources/');
//            $filepath = sprintf('%s/%s', $mockPath, 'get_options_mapping_family_router_attribute_color.json');
//            $responseContent = file_get_contents($filepath);

            $responseData = json_decode($responseContent, true);
            if (null === $responseData) {
                throw new PimAiServerException();
            }

            return new AttributeOptionsMapping($responseData);
        } catch (ServerException | PimAiServerException $e) {
            throw new PimAiServerException(
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
