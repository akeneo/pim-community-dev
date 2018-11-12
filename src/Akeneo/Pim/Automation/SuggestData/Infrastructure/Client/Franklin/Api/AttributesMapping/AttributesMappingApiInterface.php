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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AttributesMapping;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AuthenticatedApi;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\AttributesMapping;

/**
 * Interface for the API Service to manage attributes mapping.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
interface AttributesMappingApiInterface extends AuthenticatedApi
{
    /**
     * @param string $familyCode
     *
     * @return AttributesMapping
     */
    public function fetchByFamily(string $familyCode): AttributesMapping;

    /**
     * @param string $familyCode
     * @param array $mapping
     */
    public function update(string $familyCode, array $mapping): void;
}
