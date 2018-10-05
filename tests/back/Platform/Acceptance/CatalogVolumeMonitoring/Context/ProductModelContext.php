<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class ProductModelContext implements Context
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
     * @Given a catalog with :numberOfProductModels product models
     *
     * @param int $numberOfProductModels
     */
    public function aCatalogWithProductModels(int $numberOfProductModels): void
    {
        $this->inMemoryQuery->setVolume($numberOfProductModels);
    }

    /**
     * @Then the report returns that the number of product models is :numberOfProductModels
     *
     * @param int $numberOfProductModels
     */
    public function theReportReturnsThatTheNumberOfProductModelsIs(int $numberOfProductModels): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfProductModels, $volumes['count_product_models']['value']);
    }
}
