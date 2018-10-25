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

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\OptionsMapping;

/**
 * Interface for the API Service to manage attribute options mapping.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
interface OptionsMappingInterface
{
    /**
     * Fetches options mapping from family and attribute.
     *
     * @param string $familyCode
     * @param string $franklinAttributeId
     *
     * @return OptionsMapping
     */
    public function fetchByFamilyAndAttribute(string $familyCode, string $franklinAttributeId): OptionsMapping;
}
