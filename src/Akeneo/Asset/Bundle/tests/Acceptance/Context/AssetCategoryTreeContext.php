<?php

declare(strict_types=1);

namespace Akeneo\Asset\Bundle\tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context\ReportContext;
use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryCountQuery;
use Webmozart\Assert\Assert;

final class AssetCategoryTreeContext implements Context
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
     * @Given a catalog with :numberOfAssetCategoryTrees asset category trees
     *
     * @param int $numberOfAssetCategoryTrees
     */
    public function aCatalogWithAssetCategoryTrees(int $numberOfAssetCategoryTrees): void
    {
        $this->inMemoryQuery->setVolume($numberOfAssetCategoryTrees);
    }

    /**
     * @Then the report returns that the number of asset category trees is :numberOfAssetCategoryTrees
     *
     * @param int $numberOfAssetCategoryTrees
     */
    public function theReportReturnsThatTheNumberOfAssetCategoryTreesIs(int $numberOfAssetCategoryTrees): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfAssetCategoryTrees, $volumes['count_asset_category_trees']['value']);
    }
}
