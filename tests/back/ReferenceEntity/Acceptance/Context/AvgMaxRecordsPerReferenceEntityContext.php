<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\ReferenceEntity\Acceptance\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context\ReportContext;
use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class AvgMaxRecordsPerReferenceEntityContext implements Context
{
    /** @var ReportContext */
    private $reportContext;

    /** @var InMemoryAverageMaxQuery */
    private $inMemoryQuery;

    public function __construct(ReportContext $reportContext, InMemoryAverageMaxQuery $inMemoryQuery)
    {
        $this->reportContext = $reportContext;
        $this->inMemoryQuery = $inMemoryQuery;
    }

    /**
     * @Given /^a reference entity with (\d+) records$/
     */
    public function aReferenceEntityWithRecords(int $numberOfRecords)
    {
        $this->inMemoryQuery->addValue($numberOfRecords);
    }

    /**
     * @Then the report returns that the average number of records per reference entity is :recordsPerReferenceEntity
     */
    public function theReportReturnsThatTheRecordsPerReferenceEntityIs(int $recordsPerReferenceEntity): void
    {
        $volumes = $this->reportContext->getVolumes();
        Assert::eq($recordsPerReferenceEntity, $volumes['average_max_records_per_reference_entities']['value']['average']);
    }

    /**
     * @Given /^the report returns that the maximum number of records per reference entity is (\d+)$/
     */
    public function theReportReturnsThatTheMaximumNumberOfRecordsPerReferenceEntityIs($number)
    {
        $volumes = $this->reportContext->getVolumes();
        Assert::eq($volumes['average_max_records_per_reference_entities']['value']['max'], $number);
    }
}
