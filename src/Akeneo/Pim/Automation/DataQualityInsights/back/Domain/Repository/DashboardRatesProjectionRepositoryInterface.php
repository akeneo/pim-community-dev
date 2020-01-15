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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Periodicity;

interface DashboardRatesProjectionRepositoryInterface
{
    public function save(Write\DashboardRatesProjection $dashboardRates): void;

    public function findCatalogProjection(ChannelCode $channel, LocaleCode $locale, Periodicity $periodicity): ?Read\DashboardRates;

    public function findCategoryProjection(ChannelCode $channel, LocaleCode $locale, Periodicity $periodicity, CategoryCode $category): ?Read\DashboardRates;

    public function findFamilyProjection(ChannelCode $channel, LocaleCode $locale, Periodicity $periodicity, FamilyCode $family): ?Read\DashboardRates;

    public function removeRates(Periodicity $periodicity, ConsolidationDate $date): void;
}
