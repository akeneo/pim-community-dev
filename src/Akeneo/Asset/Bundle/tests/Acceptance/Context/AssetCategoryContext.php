<?php

declare(strict_types=1);

namespace Akeneo\Asset\Bundle\tests\Acceptance\Context;

use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context\ReportContext;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class AssetCategoryContext implements Context
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
     * @Given a catalog with :numberOfAssetCategories asset categories
     *
     * @param int $numberOfAssetCategories
     */
    public function aCatalogWithAssetCategories(int $numberOfAssetCategories): void
    {
        $this->inMemoryQuery->setVolume($numberOfAssetCategories);
    }

    /**
     * @Then the report returns that the number of asset categories is :numberOfAssetCategories
     *
     * @param int $numberOfAssetCategories
     */
    public function theReportReturnsThatTheNumberOfAssetCategoriesIs(int $numberOfAssetCategories): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfAssetCategories, $volumes['count_asset_categories']['value']);
    }
}
