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
     * @Given the limit of the number of locales is set to :limit
     *
     * @param int $limit
     */
    public function theLimitOfTheNumberOfLocalesIsSetTo(int $limit): void
    {
        $this->inMemoryQuery->setLimit($limit);
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

    /**
     * @Then the report warns the users that the number of locales is high
     */
    public function theReportWarnsTheUsersThatTheNumberOfLocalesIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::true($volumes['count_locales']['has_warning']);
    }

    /**
     * @Then the report does not warn the users that the number of locales is high
     */
    public function theReportDoesNotWarnTheUsersThatTheNumberOfLocalesIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::false($volumes['count_locales']['has_warning']);
    }
}
