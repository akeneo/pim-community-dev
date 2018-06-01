<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryCountQuery;
use Webmozart\Assert\Assert;

final class CategoryLevelContext implements Context
{
    /** @var ReportContext */
    private $reportContext;

    /** @var InMemoryAverageMaxQuery */
    private $inMemoryQuery;

    /**
     * @param ReportContext      $reportContext
     * @param InMemoryAverageMaxQuery $inMemoryQuery
     */
    public function __construct(ReportContext $reportContext, InMemoryAverageMaxQuery $inMemoryQuery)
    {
        $this->reportContext = $reportContext;
        $this->inMemoryQuery = $inMemoryQuery;
    }

    /**
     * @Given a catalog with :maxOfCategoryLevels category levels
     *
     * @param int $maxOfCategoryLevels
     */
    public function aCatalogWithCategoryLevels(int $maxOfCategoryLevels): void
    {
        $this->inMemoryQuery->addValue($maxOfCategoryLevels);
    }

    /**
     * @Given the limit of the number of category levels is set to :limit
     *
     * @param int $limit
     */
    public function theLimitOfTheNumberOfCategoryLevelsIsSetTo(int $limit): void
    {
        $this->inMemoryQuery->setLimit($limit);
    }

    /**
     * @Then the report returns that the maximum of category levels is :maxOfCategoryLevels
     *
     * @param int $maxOfCategoryLevels
     */
    public function theReportReturnsThatTheMaximumOfCategoryLevelsIs(int $maxOfCategoryLevels): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($maxOfCategoryLevels, $volumes['average_max_category_levels']['value']['max']);
    }

    /**
     * @Then the report returns that the average of category levels is :avgOfCategoryLevels
     *
     * @param int $avgOfCategoryLevels
     */
    public function theReportReturnsThatTheAverageOfCategoryLevelsIs(int $avgOfCategoryLevels): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($avgOfCategoryLevels, $volumes['average_max_category_levels']['value']['average']);
    }

    /**
     * @Then the report warns the users that the number of category levels is high
     */
    public function theReportWarnsTheUsersThatTheNumberOfCategoryLevelsIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::true($volumes['average_max_category_levels']['has_warning']);
    }

    /**
     * @Then the report does not warn the users that the number of category levels is high
     */
    public function theReportDoesNotWarnTheUsersThatTheNumberOfCategoryLevelsIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::false($volumes['average_max_category_levels']['has_warning']);
    }
}
