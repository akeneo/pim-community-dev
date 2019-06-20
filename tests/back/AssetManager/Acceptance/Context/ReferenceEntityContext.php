<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\AssetFamily\Acceptance\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context\ReportContext;
use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class AssetFamilyContext implements Context
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
     * @Given a catalog with :numberOfAssetFamilies asset families
     */
    public function aCatalogWithAssetFamilies(int $numberOfAssetFamilies): void
    {
        $this->inMemoryQuery->setVolume($numberOfAssetFamilies);
    }

    /**
     * @Given the limit of asset families is set to :limit
     */
    public function theLimitOfAssetFamiliesIsSetTo(int $limit): void
    {
        $this->inMemoryQuery->setLimit($limit);
    }

    /**
     * @Then the report returns that the number of asset families is :numberOfAssetFamilies
     */
    public function theReportReturnsThatTheNumberOfAssetFamiliesIs(int $numberOfAssetFamilies): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfAssetFamilies, $volumes['count_asset_families']['value']);
    }

    /**
     * @Then the report warns the users that the number of asset families is high
     */
    public function theReportWarnsTheUsersThatTheNumberOfAssetFamiliesIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::true($volumes['count_asset_families']['has_warning']);
    }

    /**
     * @Then the report does not warn the users that the number of asset families is high
     */
    public function theReportDoesNotWarnTheUsersThatTheNumberOfAssetFamiliesIsHigh(): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::false($volumes['count_asset_families']['has_warning']);
    }
}
