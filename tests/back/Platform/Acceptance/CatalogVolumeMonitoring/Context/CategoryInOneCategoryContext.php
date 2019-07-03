<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class CategoryInOneCategoryContext implements Context
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
     * @Given a catalog with :maxOfCategoryInOneCategory categories in one category
     *
     * @param int $maxOfCategoryInOneCategory
     */
    public function aCatalogWithCategoryInOneCategory(int $maxOfCategoryInOneCategory): void
    {
        $this->inMemoryQuery->addValue($maxOfCategoryInOneCategory);
    }

    /**
     * @Given the limit of the number of category in one category is set to :limit
     *
     * @param int $limit
     */
    public function theLimitOfTheNumberOfCategoryInOneCategoryIsSetTo(int $limit): void
    {
        $this->inMemoryQuery->setLimit($limit);
    }

    /**
     * @Then the report returns that the maximum of category in one category is :maxOfCategoryInOneCategory
     *
     * @param int $maxOfCategoryInOneCategory
     */
    public function theReportReturnsThatTheMaximumOfCategoryInOneCategoryIs(int $maxOfCategoryInOneCategory): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($maxOfCategoryInOneCategory, $volumes['average_max_category_in_one_category']['value']['max']);
    }
}
