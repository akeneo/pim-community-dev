<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\AssetFamily\Acceptance\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context\ReportContext;
use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class AvgMaxAssetsPerAssetFamilyContext implements Context
{
    /** @var ReportContext */
    private $reportContext;

    /** @var InMemoryAverageMaxQuery */
    private $inMemoryQuery;

    public function __construct(ReportContext $reportContext, InMemoryAverageMaxQuery $inMemoryQuery)
    {
        $this->reportContext = $reportContext;
        $this->inMemoryQuery = $inMemoryQuery;
    }

    /**
     * @Given /^an asset family with (\d+) assets$/
     */
    public function aAssetFamilyWithAssets(int $numberOfAssets)
    {
        $this->inMemoryQuery->addValue($numberOfAssets);
    }

    /**
     * @Then the report returns that the average number of assets per asset family is :assetsPerAssetFamily
     */
    public function theReportReturnsThatTheAssetsPerAssetFamilyIs(int $assetsPerAssetFamily): void
    {
        $volumes = $this->reportContext->getVolumes();
        Assert::eq($assetsPerAssetFamily, $volumes['average_max_assets_per_asset_families']['value']['average']);
    }

    /**
     * @Given /^the report returns that the maximum number of assets per asset family is (\d+)$/
     */
    public function theReportReturnsThatTheMaximumNumberOfAssetsPerAssetFamilyIs($number)
    {
        $volumes = $this->reportContext->getVolumes();
        Assert::eq($volumes['average_max_assets_per_asset_families']['value']['max'], $number);
    }
}
