<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class CategoryContext implements Context
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
     * @Given a catalog with :numberOfCategories categories
     *
     * @param int $numberOfCategories
     */
    public function aCatalogWithCategories(int $numberOfCategories): void
    {
        $this->inMemoryQuery->setVolume($numberOfCategories);
    }

    /**
     * @Given the limit of the number of categories is set to :limit
     *
     * @param int $limit
     */
    public function theLimitOfTheNumberOfCategoriesIsSetTo(int $limit): void
    {
        $this->inMemoryQuery->setLimit($limit);
    }

    /**
     * @Then the report returns that the number of categories is :numberOfCategories
     *
     * @param int $numberOfCategories
     */
    public function theReportReturnsThatTheNumberOfCategoriesIs(int $numberOfCategories): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfCategories, $volumes['count_categories']['value']);
    }

    /**
     * @Then the report warns the users that the number of categories is high
     */
    public function theReportWarnsTheUsersThatTheNumberOfCategoriesIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::true($volumes['count_categories']['has_warning']);
    }

    /**
     * @Then the report does not warn the users that the number of categories is high
     */
    public function theReportDoesNotWarnTheUsersThatTheNumberOfCategoriesIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::false($volumes['count_categories']['has_warning']);
    }
}
