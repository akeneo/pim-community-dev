<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class LocalizableAttributeContext implements Context
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
     * @Given a catalog with :numberOfLocalizableAttributes localizable attributes
     *
     * @param int $numberOfLocalizableAttributes
     */
    public function aCatalogWithLocalizableAttributes(int $numberOfLocalizableAttributes): void
    {
        $this->inMemoryQuery->setVolume($numberOfLocalizableAttributes);
    }

    /**
     * @Then the report returns that the number of localizable attributes is :numberOfLocalizableAttributes
     *
     * @param int $numberOfLocalizableAttributes
     */
    public function theReportReturnsThatTheNumberOfLocalizableAttributesIs(int $numberOfLocalizableAttributes): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfLocalizableAttributes, $volumes['count_localizable_attributes']['value']);
    }
}
