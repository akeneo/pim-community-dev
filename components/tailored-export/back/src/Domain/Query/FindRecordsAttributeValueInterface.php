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

namespace Akeneo\Platform\TailoredExport\Domain\Query;

interface FindRecordsAttributeValueInterface
{
    /**
     * @param array<string> $recordCodes
     * 
     * @return array<string, string|array|null>
     */
    public function find(
        string $referenceEntityCode,
        array $recordCodes,
        string $referenceEntityAttributeCode,
        ?string $channel = null,
        ?string $locale = null,
    ): array;
}
