<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 */
interface FindRecordsUsedAsProductVariantAxisInterface
{
    /**
     * @param string[] $recordCodes
     */
    public function areUsed(
        array $recordCodes,
        string $referenceEntityIdentifier
    ): bool;

    /**
     * @param string[] $recordCodes
     * @return string[]
     */
    public function getUsedCodes(
        array $recordCodes,
        string $referenceEntityIdentifier
    ): array;
}
