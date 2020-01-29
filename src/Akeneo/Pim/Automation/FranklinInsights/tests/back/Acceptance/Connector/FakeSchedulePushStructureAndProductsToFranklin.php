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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Acceptance\Connector;

use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\SchedulePushStructureAndProductsToFranklinInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;

final class FakeSchedulePushStructureAndProductsToFranklin implements SchedulePushStructureAndProductsToFranklinInterface
{
    public function schedule(BatchSize $attributesBatchSize, BatchSize $familiesBatchSize, BatchSize $productsBatchSize): void
    {
    }
}
