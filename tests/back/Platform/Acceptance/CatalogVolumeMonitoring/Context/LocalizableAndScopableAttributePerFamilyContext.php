<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class LocalizableAndScopableAttributePerFamilyContext implements Context
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
     * @Given a family with :numberOfLocScopAttributes localizable and scopable attributes, :numberOfLocAttributes localizable attributes, :numberOfScopAttributes scopable attributes and :numberOfAttributes attributes
     *
     * @param int $numberOfAttributes
     * @param int $numberOfLocAttributes
     * @param int $numberOfScopAttributes
     * @param int $numberOfLocScopAttributes
     */
    public function aFamilyWithAttributes(
        int $numberOfLocScopAttributes,
        int $numberOfLocAttributes,
        int $numberOfScopAttributes,
        int $numberOfAttributes
    ): void {
        $totalAttributes = ($numberOfAttributes+$numberOfLocAttributes+$numberOfScopAttributes+$numberOfLocScopAttributes);

        $this->averageMaxQuery->addValue(intval(($numberOfLocScopAttributes*100)/$totalAttributes));
    }

    /**
     * @Then the report returns that the average percentage of localizable and scopable attributes per family is :numberOfAttributes
     *
     * @param int $numberOfAttributes
     */
    public function theReportReturnsThatTheAveragePercentageOfLocalizableAndScopableAttributesPerFamilyIs(int $numberOfAttributes): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfAttributes, $volumes['avg_percentage_localizable_and_scopable_attributes_per_family']['value']['average']);
    }
}
