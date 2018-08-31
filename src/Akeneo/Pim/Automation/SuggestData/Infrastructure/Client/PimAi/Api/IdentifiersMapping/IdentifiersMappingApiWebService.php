<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;

/**
 * API Web Service to manage identifiers mapping
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IdentifiersMappingApiWebService implements IdentifiersMappingApiInterface
{
    /** @var UriGenerator */
    private $uriGenerator;

    /** @var Client */
    private $httpClient;

    /**
     * @param UriGenerator $uriGenerator
     * @param Client       $httpClient
     */
    public function __construct(
        UriGenerator $uriGenerator,
        Client $httpClient
    ) {
        $this->uriGenerator = $uriGenerator;
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $mapping): void
    {
        $route = $this->uriGenerator->generate('/mapping/identifiers');

        $this->httpClient->request('PUT', $route, [
            'form_params' => $mapping,
        ]);
    }
}
