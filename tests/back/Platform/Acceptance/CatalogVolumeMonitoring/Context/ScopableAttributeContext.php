<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class ScopableAttributeContext implements Context
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
     * @Given a catalog with :numberOfScopableAttributes scopable attributes
     *
     * @param int $numberOfScopableAttributes
     */
    public function aCatalogWithScopableAttributes(int $numberOfScopableAttributes): void
    {
        $this->inMemoryQuery->setVolume($numberOfScopableAttributes);
    }

    /**
     * @Then the report returns that the number of scopable attributes is :numberOfScopableAttributes
     *
     * @param int $numberOfScopableAttributes
     */
    public function theReportReturnsThatTheNumberOfScopableAttributesIs(int $numberOfScopableAttributes): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfScopableAttributes, $volumes['count_scopable_attributes']['value']);
    }
}
