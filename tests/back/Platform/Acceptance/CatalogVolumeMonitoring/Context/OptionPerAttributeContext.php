<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class OptionPerAttributeContext implements Context
{
    /** @var ReportContext */
    private $reportContext;

    /** @var InMemoryAverageMaxQuery */
    private $inMemoryQuery;

    /**
     * @param ReportContext           $reportContext
     * @param InMemoryAverageMaxQuery $inMemoryQuery
     */
    public function __construct(ReportContext $reportContext, InMemoryAverageMaxQuery $inMemoryQuery)
    {
        $this->reportContext = $reportContext;
        $this->inMemoryQuery = $inMemoryQuery;
    }

    /**
     * @Given an attribute with :numberOfOptions options
     *
     * @param int $numberOfOptions
     */
    public function anAttributeWithOptions(int $numberOfOptions): void
    {
        $this->inMemoryQuery->addValue($numberOfOptions);
    }

    /**
     * @Then the report returns that the average number of options per attribute is :number
     *
     * @param int $number
     */
    public function theReportReturnsThatTheMeanNumberOfOptionsPerAttributeIs(int $number): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($volumes['average_max_options_per_attribute']['value']['average'], $number);
    }

    /**
     * @Then the report returns that the maximum number of options per attribute is :number
     *
     * @param int $number
     */
    public function theReportReturnsThatTheMaximumNumberOfOptionsPerAttributeIs(int $number): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($volumes['average_max_options_per_attribute']['value']['max'], $number);
    }
}
