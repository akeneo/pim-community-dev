<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\ReferenceEntity\Acceptance\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context\ReportContext;
use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class MaxRecordsPerReferenceEntityContext implements Context
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
     * @Given a catalog with maximum :recordsPerReferenceEntity records per reference entity
     */
    public function aCatalogWithReferenceEntities(int $recordsPerReferenceEntity): void
    {
        $this->inMemoryQuery->setVolume($recordsPerReferenceEntity);
    }

    /**
     * @Given the max limit of records per reference entity is set to :limit
     */
    public function theLimitOfRecordsPerReferenceEntityIsSetTo(int $limit): void
    {
        $this->inMemoryQuery->setLimit($limit);
    }

    /**
     * @Then the report returns that the max number of records per reference entityis :recordsPerReferenceEntity
     */
    public function theReportReturnsThatTheRecordsPerReferenceEntityIs(int $recordsPerReferenceEntity): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($recordsPerReferenceEntity, $volumes['max_records_per_reference_entities']['value']);
    }

    /**
     * @Then the report warns the users that the max number of records per reference entity is high
     */
    public function theReportWarnsTheUsersThatTheRecordsPerReferenceEntityIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::true($volumes['max_records_per_reference_entities']['has_warning']);
    }

    /**
     * @Then the report does not warn the users that the max number of records per reference entity is high
     */
    public function theReportDoesNotWarnTheUsersThatTherecordsPerReferenceEntityIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::false($volumes['max_records_per_reference_entities']['has_warning']);
    }
}
