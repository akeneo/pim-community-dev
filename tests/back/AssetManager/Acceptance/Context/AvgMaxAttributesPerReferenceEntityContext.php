<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\AssetFamily\Acceptance\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context\ReportContext;
use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class AvgMaxAttributesPerAssetFamilyContext implements Context
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
     * @Given /^an asset family with (\d+) attributes$/
     */
    public function aAssetFamilyWithattributes(int $numberOfattributes)
    {
        $this->inMemoryQuery->addValue($numberOfattributes);
    }

    /**
     * @Then the report returns that the average number of attributes per asset family is :attributesPerAssetFamily
     */
    public function theReportReturnsThatTheattributesPerAssetFamilyIs(int $attributesPerAssetFamily): void
    {
        $volumes = $this->reportContext->getVolumes();
        Assert::eq($attributesPerAssetFamily, $volumes['average_max_attributes_per_asset_families']['value']['average']);
    }

    /**
     * @Given /^the report returns that the maximum number of attributes per asset family is (\d+)$/
     */
    public function theReportReturnsThatTheMaximumNumberOfattributesPerAssetFamilyIs($number)
    {
        $volumes = $this->reportContext->getVolumes();
        Assert::eq($volumes['average_max_attributes_per_asset_families']['value']['max'], $number);
    }
}
