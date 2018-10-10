<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class LocalizableAttributePerFamilyContext implements Context
{
    /** @var ReportContext */
    private $reportContext;

    /** @var InMemoryAverageMaxQuery */
    private $averageMaxQuery;

    /**
     * @param ReportContext           $reportContext
     * @param InMemoryAverageMaxQuery $averageMaxQuery
     */
    public function __construct(ReportContext $reportContext, InMemoryAverageMaxQuery $averageMaxQuery)
    {
        $this->reportContext = $reportContext;
        $this->averageMaxQuery = $averageMaxQuery;
    }

    /**
     * @Given a family with :numberOfLocAttributes localizable attributes, :numberOfScopAttributes scopable attributes, :numberOfLocScopAttributes localizable and scopable attributes and :numberOfAttributes attributes
     *
     * @param int $numberOfLocAttributes
     * @param int $numberOfScopAttributes
     * @param int $numberOfLocScopAttributes
     * @param int $numberOfAttributes
     */
    public function aFamilyWithAttributes(
        int $numberOfLocAttributes,
        int $numberOfScopAttributes,
        int $numberOfLocScopAttributes,
        int $numberOfAttributes
    ): void {
        $totalAttributes = ($numberOfAttributes+$numberOfLocAttributes+$numberOfScopAttributes+$numberOfLocScopAttributes);

        $this->averageMaxQuery->addValue(intval(($numberOfLocAttributes*100)/$totalAttributes));
    }

    /**
     * @Then the report returns that the average percentage of localizable attributes per family is :numberOfLocalizableAttributes
     *
     * @param int $numberOfLocalizableAttributes
     */
    public function theReportReturnsThatTheAverageOfPercentageLocalizableAttributesPerFamilyIs(int $numberOfLocalizableAttributes): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfLocalizableAttributes, $volumes['avg_percentage_localizable_attributes_per_family']['value']['average']);
    }
}
