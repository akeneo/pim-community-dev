<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class ProductContext implements Context
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
     * @Given a catalog with :numberOfProducts products
     *
     * @param int $numberOfProducts
     */
    public function aCatalogWithProducts(int $numberOfProducts): void
    {
        $this->inMemoryQuery->setVolume($numberOfProducts);
    }

    /**
     * @Given the limit of the number of products is set to :limit
     *
     * @param int $limit
     */
    public function theLimitOfTheNumberOfProductsIsSetTo(int $limit): void
    {
        $this->inMemoryQuery->setLimit($limit);
    }

    /**
     * @Then the report returns that the number of products is :numberOfProducts
     *
     * @param int $numberOfProducts
     */
    public function theReportReturnsThatTheNumberOfProductsIs(int $numberOfProducts): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfProducts, $volumes['count_products']['value']);
    }

    /**
     * @Then the report warns the users that the number of products is high
     */
    public function theReportWarnsTheUsersThatTheNumberIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::true($volumes['count_products']['has_warning']);
    }

    /**
     * @Then the report does not warn the users that the number of products is high
     */
    public function theReportDoesNotWarnTheUsersThatTheNumberIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::false($volumes['count_products']['has_warning']);
    }
}
