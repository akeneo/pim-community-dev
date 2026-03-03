<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class LocaleContext implements Context
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
     * @Given a catalog with :numberOfLocales locales
     *
     * @param int $numberOfLocales
     */
    public function aCatalogWithLocales(int $numberOfLocales): void
    {
        $this->inMemoryQuery->setVolume($numberOfLocales);
    }

    /**
     * @Then the report returns that the number of locales is :numberOfLocales
     *
     * @param int $numberOfLocales
     */
    public function theReportReturnsThatTheNumberOfLocalesIs(int $numberOfLocales): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfLocales, $volumes['count_locales']['value']);
    }
}
