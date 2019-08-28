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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query;

interface SelectPendingItemIdentifiersQueryInterface
{
    public function getUpdatedAttributeCodes(int $offsetId, int $batchSize): array;

    public function getDeletedAttributeCodes(int $offsetId, int $batchSize): array;

    public function getUpdatedFamilyCodes(int $offsetId, int $batchSize): array;

    public function getDeletedFamilyCodes(int $offsetId, int $batchSize): array;
}
