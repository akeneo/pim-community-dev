<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class ChannelContext implements Context
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
     * @Given a catalog with :numberOfChannels channels
     *
     * @param int $numberOfChannels
     */
    public function aCatalogWithChannels(int $numberOfChannels): void
    {
        $this->inMemoryQuery->setVolume($numberOfChannels);
    }

    /**
     * @Then the report returns that the number of channels is :numberOfChannels
     *
     * @param int $numberOfChannels
     */
    public function theReportReturnsThatTheNumberOfChannelsIs(int $numberOfChannels): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfChannels, $volumes['count_channels']['value']);
    }
}
