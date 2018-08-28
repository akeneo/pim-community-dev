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
 * Interface for the API Service to manage identifiers mapping
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
interface IdentifiersMappingInterface
{
    /**
     * Call the API to update the identifiers mapping
     *
     * @param array $mapping
     */
    public function update(IdentifiersMapping $mapping): void;
}
