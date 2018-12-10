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

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;

/**
 * Interface for the API Service to manage identifiers mapping.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
interface IdentifiersMappingApiInterface extends AuthenticatedApiInterface
{
    /**
     * Call the API to update the identifiers mapping.
     *
     * @see Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer
     *
     * @param array $mapping
     */
    public function update(array $mapping): void;
}
