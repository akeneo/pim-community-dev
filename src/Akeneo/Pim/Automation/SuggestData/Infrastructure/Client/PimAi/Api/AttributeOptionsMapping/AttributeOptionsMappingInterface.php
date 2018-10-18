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

/**
 * Interface for the API Service to manage attribute options mapping.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
interface AttributeOptionsMappingInterface
{
    public function fetchByFamilyAndAttribute(string $familyCode, string $franklinAttributeId);
}
