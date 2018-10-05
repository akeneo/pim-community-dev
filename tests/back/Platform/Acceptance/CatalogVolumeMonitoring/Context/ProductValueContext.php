<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class ProductValueContext implements Context
{
    /** @var ReportContext */
    private $reportContext;

    /** @var InMemoryCountQuery */
    private $countQuery;

    /** @var InMemoryAverageMaxQuery */
    private $averageMaxQuery;

    /**
     * @param ReportContext           $reportContext
     * @param InMemoryCountQuery      $countQuery
     * @param InMemoryAverageMaxQuery $averageMaxQuery
     */
    public function __construct(ReportContext $reportContext, InMemoryCountQuery $countQuery, InMemoryAverageMaxQuery $averageMaxQuery)
    {
        $this->reportContext = $reportContext;
        $this->countQuery = $countQuery;
        $this->averageMaxQuery = $averageMaxQuery;
    }

    /**
     * @Given a catalog with :numberOfProductValues product values
     *
     * @param int $numberOfProductValues
     */
    public function aCatalogWithProductValues(int $numberOfProductValues): void
    {
        $this->countQuery->setVolume($numberOfProductValues);
    }

    /**
     * @Then the report returns that the number of product values is :numberOfProductValues
     *
     * @param int $numberOfProductValues
     */
    public function theReportReturnsThatTheNumberOfProductValuesIs(int $numberOfProductValues): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfProductValues, $volumes['count_product_and_product_model_values']['value']);
    }

    /**
     * @Given /^a product(?: model)? with (\d+) product values$/i
     *
     * @param int $numberOfProductValues
     */
    public function aProductWithValues(int $numberOfProductValues): void
    {
        $this->averageMaxQuery->addValue($numberOfProductValues);
    }

    /**
     * @Then the report returns that the average number of product values per product is :number
     *
     * @param int $number
     */
    public function theReportReturnsThatTheMeanNumberOfProductValuesPerProductIs(int $number): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($volumes['average_max_product_and_product_model_values']['value']['average'], $number);
    }

    /**
     * @Then the report returns that the maximum number of product values per product is :number
     *
     * @param int $number
     */
    public function theReportReturnsThatTheMaximumNumberOfProductValuesPerProductIs(int $number): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($volumes['average_max_product_and_product_model_values']['value']['max'], $number);
    }
}
