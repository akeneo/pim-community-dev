<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryCountQuery;
use Webmozart\Assert\Assert;

final class VariantProductContext implements Context
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
     * @Given a catalog with :numberOfVariantProducts variant products
     *
     * @param int $numberOfVariantProducts
     */
    public function aCatalogWithVariantProducts(int $numberOfVariantProducts): void
    {
        $this->inMemoryQuery->setVolume($numberOfVariantProducts);
    }

    /**
     * @Then the report returns that the number of variant products is :numberOfVariantProducts
     *
     * @param int $numberOfVariantProducts
     */
    public function theReportReturnsThatTheNumberOfVariantProductsIs(int $numberOfVariantProducts): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfVariantProducts, $volumes['count_variant_products']['value']);
    }
}
