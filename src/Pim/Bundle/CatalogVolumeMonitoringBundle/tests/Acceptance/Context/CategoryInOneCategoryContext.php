<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryCountQuery;
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
     * @Given a catalog with :maxOfCategoryInOneCategory category in one category
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

    /**
     * @Then the report returns that the average number of categories in one category is :avgOfCategoryInOneCategory
     *
     * @param int $avgOfCategoryInOneCategory
     */
    public function theReportReturnsThatTheAverageOfCategoryInOneCategoryIs(int $avgOfCategoryInOneCategory): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($avgOfCategoryInOneCategory, $volumes['average_max_category_in_one_category']['value']['average']);
    }

    /**
     * @Then the report warns the users that the number of category in one category is high
     */
    public function theReportWarnsTheUsersThatTheNumberOfCategoryInOneCategoryIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::true($volumes['average_max_category_in_one_category']['has_warning']);
    }

    /**
     * @Then the report does not warn the users that the number of category in one category is high
     */
    public function theReportDoesNotWarnTheUsersThatTheNumberOfCategoryInOneCategoryIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::false($volumes['average_max_category_in_one_category']['has_warning']);
    }
}
