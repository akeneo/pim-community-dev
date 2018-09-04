<?php

declare(strict_types=1);

namespace Akeneo\Asset\Bundle\tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context\ReportContext;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryCountQuery;
use Webmozart\Assert\Assert;

final class AssetContext implements Context
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
     * @Given a catalog with :numberOfAssets assets
     *
     * @param int $numberOfAssets
     */
    public function aCatalogWithAssets(int $numberOfAssets): void
    {
        $this->inMemoryQuery->setVolume($numberOfAssets);
    }

    /**
     * @Then the report returns that the number of assets is :numberOfAssets
     *
     * @param int $numberOfAssets
     */
    public function theReportReturnsThatTheNumberOfAssetsIs(int $numberOfAssets): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfAssets, $volumes['count_assets']['value']);
    }
}
