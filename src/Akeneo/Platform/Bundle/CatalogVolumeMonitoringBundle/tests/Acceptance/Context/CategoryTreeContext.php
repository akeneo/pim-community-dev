<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryCountQuery;
use Webmozart\Assert\Assert;

final class CategoryTreeContext implements Context
{
    /** @var ReportContext */
    private $reportContext;

    /** @var InMemoryCountQuery */
    private $inMemoryQuery;

    /**
     * @param ReportContext      $reportContext
     * @param InMemoryCountQuery $inMemoryQuery
     */
    public function __construct(ReportContext $reportContext, InMemoryCountQuery $inMemoryQuery)
    {
        $this->reportContext = $reportContext;
        $this->inMemoryQuery = $inMemoryQuery;
    }

    /**
     * @Given a catalog with :numberOfCategoryTrees category trees
     *
     * @param int $numberOfCategoryTrees
     */
    public function aCatalogWithCategoryTrees(int $numberOfCategoryTrees): void
    {
        $this->inMemoryQuery->setVolume($numberOfCategoryTrees);
    }

    /**
     * @Given the limit of the number of category trees is set to :limit
     *
     * @param int $limit
     */
    public function theLimitOfTheNumberOfCategoryTreesIsSetTo(int $limit): void
    {
        $this->inMemoryQuery->setLimit($limit);
    }

    /**
     * @Then the report returns that the number of category trees is :numberOfCategoryTrees
     *
     * @param int $numberOfCategoryTrees
     */
    public function theReportReturnsThatTheNumberOfCategoryTreesIs(int $numberOfCategoryTrees): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfCategoryTrees, $volumes['count_category_trees']['value']);
    }

    /**
     * @Then the report warns the users that the number of category trees is high
     */
    public function theReportWarnsTheUsersThatTheNumberOfCategoryTreesIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::true($volumes['count_category_trees']['has_warning']);
    }

    /**
     * @Then the report does not warn the users that the number of category trees is high
     */
    public function theReportDoesNotWarnTheUsersThatTheNumberOfCategoryTreesIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::false($volumes['count_category_trees']['has_warning']);
    }
}
