<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class LocalizableAndScopableAttributeContext implements Context
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
     * @Given a catalog with :number localizable and scopable attributes
     *
     * @param int $number
     */
    public function aCatalogWithLocalizableAndScopableAttributes(int $number): void
    {
        $this->inMemoryQuery->setVolume($number);
    }

    /**
     * @Then the report returns that the number of localizable and scopable attributes is :number
     *
     * @param int $number
     */
    public function theReportReturnsThatTheNumberOfLocalizableAndScopableAttributesIs(int $number): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($number, $volumes['count_localizable_and_scopable_attributes']['value']);
    }
}
