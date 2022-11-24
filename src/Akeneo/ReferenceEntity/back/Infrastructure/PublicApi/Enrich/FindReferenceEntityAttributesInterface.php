<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Enrich;

interface FindReferenceEntityAttributesInterface
{
    /**
     * Returns an array of the requested Reference Entity attributes, optionally filtered on type
     *
     * @param ?array<string> $types
     *
     * @return AttributeDetails[]
     */
    public function findByCode(string $referenceEntityCode, ?array $types = null): array;
}
