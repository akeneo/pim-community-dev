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
     * @Then the report returns that the number of products is :numberOfProducts
     *
     * @param int $numberOfProducts
     */
    public function theReportReturnsThatTheNumberOfProductsIs(int $numberOfProducts): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfProducts, $volumes['count_products']['value']);
    }
}
