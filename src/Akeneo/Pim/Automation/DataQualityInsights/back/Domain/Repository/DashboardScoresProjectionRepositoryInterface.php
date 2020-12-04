<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

interface DashboardScoresProjectionRepositoryInterface
{
    public function save(Write\DashboardRatesProjection $dashboardRates): void;

    public function purgeRates(Write\DashboardPurgeDateCollection $purgeDates): void;
}
