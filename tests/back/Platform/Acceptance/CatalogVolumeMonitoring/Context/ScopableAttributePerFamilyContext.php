<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class ScopableAttributePerFamilyContext implements Context
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
     * @Given a family with :numberOfScopAttributes scopable attributes, :numberOfLocAttributes localizable attributes, :numberOfLocScopAttributes localizable and scopable attributes and :numberOfAttributes attributes
     *
     * @param int $numberOfScopAttributes
     * @param int $numberOfLocAttributes
     * @param int $numberOfLocScopAttributes
     * @param int $numberOfAttributes
     */
    public function aFamilyWithAttributes(
        int $numberOfScopAttributes,
        int $numberOfLocAttributes,
        int $numberOfLocScopAttributes,
        int $numberOfAttributes
    ): void {
        $totalAttributes = ($numberOfAttributes+$numberOfLocAttributes+$numberOfScopAttributes+$numberOfLocScopAttributes);

        $this->averageMaxQuery->addValue(intval(($numberOfScopAttributes*100)/$totalAttributes));
    }

    /**
     * @Then the report returns that the average percentage of scopable attributes per family is :numberOfAttributes
     *
     * @param int $numberOfAttributes
     */
    public function theReportReturnsThatTheAveragePercentageOfScopableAttributesPerFamilyIs(int $numberOfAttributes): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfAttributes, $volumes['avg_percentage_scopable_attributes_per_family']['value']['average']);
    }
}
