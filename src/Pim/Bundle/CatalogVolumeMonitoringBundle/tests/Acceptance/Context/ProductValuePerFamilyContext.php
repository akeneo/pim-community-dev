<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryCountQuery;
use Webmozart\Assert\Assert;

final class ProductValuePerFamilyContext implements Context
{
    /** @var ReportContext */
    private $reportContext;

    /** @var InMemoryAverageMaxQuery */
    private $averageMaxQuery;

    /**
     * @param ReportContext           $reportContext
     * @param InMemoryAverageMaxQuery $averageMaxQuery
     */
    public function __construct(ReportContext $reportContext, InMemoryAverageMaxQuery $averageMaxQuery)
    {
        $this->reportContext = $reportContext;
        $this->averageMaxQuery = $averageMaxQuery;
    }

    /**
     * @Given a family with :numberOfProductValues product values
     *
     * @param int $numberOfProductValues
     */
    public function aFamilyWithProductValues(int $numberOfProductValues): void
    {
        $this->averageMaxQuery->addValue($numberOfProductValues);
    }

    /**
     * @Then the report returns that the average of product values per family is :numberOfProductValues
     *
     * @param int $numberOfProductValues
     */
    public function theReportReturnsThatTheAverageOfProductValuesPerFamilyIs(int $numberOfProductValues): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfProductValues, $volumes['count_product_and_product_model_values']['value']['average']);
    }

    /**
     * @Then the report returns that the maximum of product values per family is :numberOfProductValues
     *
     * @param int $numberOfProductValues
     */
    public function theReportReturnsThatTheMaximumOfProductValuesPerFamilyIs(int $numberOfProductValues): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfProductValues, $volumes['count_product_and_product_model_values']['value']['max']);
    }
}
