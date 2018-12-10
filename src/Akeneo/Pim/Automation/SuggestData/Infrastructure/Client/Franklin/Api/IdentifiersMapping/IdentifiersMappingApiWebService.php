<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\IdentifiersMapping;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AbstractApi;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;

/**
 * API Web Service to manage identifiers mapping.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IdentifiersMappingApiWebService extends AbstractApi implements AuthenticatedApiInterface
{
    /**
     * {@inheritdoc}
     */
    public function update(array $mapping): void
    {
        $route = $this->uriGenerator->generate('/api/mapping/identifiers');

        $this->httpClient->request('PUT', $route, [
            'form_params' => $mapping,
        ]);
    }
}
