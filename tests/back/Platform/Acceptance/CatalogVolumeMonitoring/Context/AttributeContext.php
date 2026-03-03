<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class AttributeContext implements Context
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
     * @Given a catalog with :numberOfAttributes attributes
     *
     * @param int $numberOfAttributes
     */
    public function aCatalogWithAttributes(int $numberOfAttributes): void
    {
        $this->inMemoryQuery->setVolume($numberOfAttributes);
    }

    /**
     * @Then the report returns that the number of attributes is :numberOfAttributes
     *
     * @param int $numberOfAttributes
     */
    public function theReportReturnsThatTheNumberOfAttributesIs(int $numberOfAttributes): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfAttributes, $volumes['count_attributes']['value']);
    }
}
