<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Behat\Behat\Context\Context;
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
}
