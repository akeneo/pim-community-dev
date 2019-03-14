<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\ReferenceEntity\Acceptance\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context\ReportContext;
use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class ReferenceEntityContext implements Context
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
     * @Given a catalog with :numberOfReferenceEntities reference entities
     */
    public function aCatalogWithReferenceEntities(int $numberOfReferenceEntities): void
    {
        $this->inMemoryQuery->setVolume($numberOfReferenceEntities);
    }

    /**
     * @Given the limit of reference entities is set to :limit
     */
    public function theLimitOfReferenceEntitiesIsSetTo(int $limit): void
    {
        $this->inMemoryQuery->setLimit($limit);
    }

    /**
     * @Then the report returns that the number of reference entities is :numberOfReferenceEntities
     */
    public function theReportReturnsThatTheNumberOfReferenceEntitiesIs(int $numberOfReferenceEntities): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfReferenceEntities, $volumes['count_reference_entities']['value']);
    }

    /**
     * @Then the report warns the users that the number of reference entities is high
     */
    public function theReportWarnsTheUsersThatTheNumberOfReferenceEntitiesIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::true($volumes['count_reference_entities']['has_warning']);
    }

    /**
     * @Then the report does not warn the users that the number of reference entities is high
     */
    public function theReportDoesNotWarnTheUsersThatTheNumberOfReferenceEntitiesIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::false($volumes['count_reference_entities']['has_warning']);
    }
}
