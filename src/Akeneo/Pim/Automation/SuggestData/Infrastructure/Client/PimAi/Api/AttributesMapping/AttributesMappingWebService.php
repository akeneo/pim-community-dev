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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AttributesMapping;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\PimAiServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\AttributesMapping;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesMappingWebService implements AttributesMappingApiInterface
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
     * @param string $familyCode
     *
     * @throws BadRequestException
     * @throws PimAiServerException
     *
     * @return AttributesMapping
     */
    public function fetchByFamily(string $familyCode): AttributesMapping
    {
        $route = $this->uriGenerator->generate(sprintf('/mapping/%s/attributes', $familyCode));

        try {
            $response = $this->httpClient->request('GET', $route);

            $attributes = [];
            $responseContent = $response->getBody()->getContents();
            /*
             * TODO: should be removed later. see APAI-302
             */
            if (!empty($responseContent)) {
                $attributes = json_decode($responseContent, true);
            }

            return new AttributesMapping($attributes);
        } catch (ServerException $e) {
            throw new PimAiServerException(
                sprintf(
                    'Something went wrong on PIM.ai side when fetching the family attributes of family "%s" : %s',
                    $familyCode,
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            throw new BadRequestException(sprintf(
                'Something went wrong when fetching the family attributes of family "%s" : %s',
                $familyCode,
                $e->getMessage()
            ));
        }
    }

    /**
     * @param string $familyCode
     * @param array $mapping
     */
    public function update(string $familyCode, array $mapping): void
    {
        $route = $this->uriGenerator->generate(sprintf('/mapping/%s/attributes', $familyCode));

        $this->httpClient->request('PUT', $route, [
            'form_params' => $mapping,
        ]);
    }
}
