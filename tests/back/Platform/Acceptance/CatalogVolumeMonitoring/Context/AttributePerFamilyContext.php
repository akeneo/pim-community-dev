<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class AttributePerFamilyContext implements Context
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
     * @Given a family with :numberOfAttributes attributes
     *
     * @param int $numberOfAttributes
     */
    public function aFamilyWithAttributes(int $numberOfAttributes): void
    {
        $this->inMemoryQuery->addValue($numberOfAttributes);
    }

    /**
     * @Then the report returns that the average number of attributes per family is :number
     *
     * @param int $number
     */
    public function theReportReturnsThatTheMeanNumberOfAttributesPerFamilyIs(int $number): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($volumes['average_max_attributes_per_family']['value']['average'], $number);
    }

    /**
     * @Then the report returns that the maximum number of attributes per family is :number
     *
     * @param int $number
     */
    public function theReportReturnsThatTheMaximumNumberOfAttributesPerFamilyIs(int $number): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($volumes['average_max_attributes_per_family']['value']['max'], $number);
    }
}
