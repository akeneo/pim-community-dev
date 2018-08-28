<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryCountQuery;
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
     * @Given a family with :numberOfAttributes scopable attributes
     *
     * @param int $numberOfAttributes
     */
    public function aFamilyWithScopableAttributes(int $numberOfAttributes): void
    {
        $this->averageMaxQuery->addValue($numberOfAttributes);
    }

    /**
     * @Then the report returns that the average of scopable attributes per family is :numberOfAttributes
     *
     * @param int $numberOfAttributes
     */
    public function theReportReturnsThatTheAverageOfScopableAttributesPerFamilyIs(int $numberOfAttributes): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfAttributes, $volumes['avg_scopable_attributes_per_family']['value']['average']);
    }
}
