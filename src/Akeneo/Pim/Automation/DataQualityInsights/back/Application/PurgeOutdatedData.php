<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardPurgeDateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardRatesProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\ProductAxisRateRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;

final class PurgeOutdatedData
{
    public const RETENTION_DAYS = 7;
    public const RETENTION_WEEKS = 4;
    public const RETENTION_MONTHS = 15;
    public const RETENTION_YEARS = 3;

    /** @var DashboardRatesProjectionRepositoryInterface */
    private $dashboardRatesProjectionRepository;

    /** @var CriterionEvaluationRepositoryInterface */
    private $productCriterionEvaluationRepository;

    /** @var ProductAxisRateRepositoryInterface */
    private $productAxisRateRepository;

    /** @var CriterionEvaluationRepositoryInterface */
    private $productModelCriterionEvaluationRepository;

    /** @var ProductAxisRateRepositoryInterface */
    private $productModelAxisRateRepository;

    public function __construct(
        DashboardRatesProjectionRepositoryInterface $dashboardRatesProjectionRepository,
        CriterionEvaluationRepositoryInterface $productCriterionEvaluationRepository,
        CriterionEvaluationRepositoryInterface $productModelCriterionEvaluationRepository,
        ProductAxisRateRepositoryInterface $productAxisRateRepository,
        ProductAxisRateRepositoryInterface $productModelAxisRateRepository
    ) {
        $this->dashboardRatesProjectionRepository = $dashboardRatesProjectionRepository;
        $this->productCriterionEvaluationRepository = $productCriterionEvaluationRepository;
        $this->productModelCriterionEvaluationRepository = $productModelCriterionEvaluationRepository;
        $this->productAxisRateRepository = $productAxisRateRepository;
        $this->productModelAxisRateRepository = $productModelAxisRateRepository;
    }

    public function purgeDashboardProjectionRatesFrom(\DateTimeImmutable $date): void
    {
        $purgeDate = new ConsolidationDate($date);
        $purgeDates = new DashboardPurgeDateCollection();
        $daily = TimePeriod::daily();

        for ($day = PurgeOutdatedData::RETENTION_DAYS; $day < PurgeOutdatedData::RETENTION_DAYS * 2; $day++) {
            $purgeDates->add($daily, $purgeDate->modify(sprintf('-%d DAY', $day)));
        }

        $purgeDates
            ->add(
                TimePeriod::weekly(),
                $purgeDate->modify(sprintf('-%d WEEK', self::RETENTION_WEEKS))->modify('Sunday last week')
            )
            ->add(
                TimePeriod::monthly(),
                $purgeDate->modify(sprintf('-%d MONTH', self::RETENTION_MONTHS))->modify('Last day of last month')
            )
            ->add(
                TimePeriod::yearly(),
                $purgeDate->modify(sprintf('-%d YEAR', self::RETENTION_YEARS))->modify('Last day of december last year')
            );

        $this->dashboardRatesProjectionRepository->purgeRates($purgeDates);
    }

    public function purgeCriterionEvaluationsFrom(\DateTimeImmutable $date): void
    {
        $purgeDate = $date->modify(sprintf('-%d DAY', self::RETENTION_DAYS));
        $this->productCriterionEvaluationRepository->purgeUntil($purgeDate);
        $this->productModelCriterionEvaluationRepository->purgeUntil($purgeDate);
    }

    public function purgeProductAxisRatesFrom(\DateTimeImmutable $date): void
    {
        $purgeDate = $date->modify(sprintf('-%d DAY', self::RETENTION_DAYS));
        $this->productAxisRateRepository->purgeUntil($purgeDate);
        $this->productModelAxisRateRepository->purgeUntil($purgeDate);
    }
}
