<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
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
     * @Then the report returns that the number of category trees is :numberOfCategoryTrees
     *
     * @param int $numberOfCategoryTrees
     */
    public function theReportReturnsThatTheNumberOfCategoryTreesIs(int $numberOfCategoryTrees): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfCategoryTrees, $volumes['count_category_trees']['value']);
    }
}
