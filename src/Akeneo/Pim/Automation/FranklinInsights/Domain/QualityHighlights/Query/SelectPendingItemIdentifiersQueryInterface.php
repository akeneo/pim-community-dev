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

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;

interface SelectPendingItemIdentifiersQueryInterface
{
    public function getUpdatedAttributeCodes(Lock $lock, int $batchSize): array;

    public function getDeletedAttributeCodes(Lock $lock, int $batchSize): array;

    public function getUpdatedFamilyCodes(Lock $lock, int $batchSize): array;

    public function getDeletedFamilyCodes(Lock $lock, int $batchSize): array;

    public function getUpdatedProductIds(Lock $lock, int $batchSize): array;

    public function getDeletedProductIds(Lock $lock, int $batchSize): array;
}
