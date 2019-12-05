<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;

interface SchedulePushStructureAndProductsToFranklinInterface
{
    public function schedule(BatchSize $attributesBatchSize, BatchSize $familiesBatchSize, BatchSize $productsBatchSize): void;
}
