<?php

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;

/**
 * API Service to manage identifiers mapping
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IdentifiersMappingWebService implements IdentifiersMappingInterface
{
    /** @var UriGenerator */
    private $uriGenerator;

    /** @var Client */
    private $httpClient;

    /** @var IdentifiersMappingNormalizer */
    private $normalizer;

    /**
     * @param UriGenerator                 $uriGenerator
     * @param Client                       $httpClient
     * @param IdentifiersMappingNormalizer $normalizer
     */
    public function __construct(
        UriGenerator $uriGenerator,
        Client $httpClient,
        IdentifiersMappingNormalizer $normalizer
    ) {
        $this->uriGenerator = $uriGenerator;
        $this->httpClient = $httpClient;
        $this->normalize = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function update(IdentifiersMapping $mapping): void
    {
        $route = $this->uriGenerator->generate('/mapping/identifiers');

        $this->httpClient->request('PUT', $route, [
            'form_params' => $this->normalizer->normalize($mapping),
        ]);
    }
}
