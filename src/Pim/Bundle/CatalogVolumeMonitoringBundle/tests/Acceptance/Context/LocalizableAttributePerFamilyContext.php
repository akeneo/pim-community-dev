<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryCountQuery;
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
     * @Given a family with :numberOfLocalizableAttributes localizable attributes
     *
     * @param int $numberOfLocalizableAttributes
     */
    public function aFamilyWithLocalizableAttributes(int $numberOfLocalizableAttributes): void
    {
        $this->averageMaxQuery->addValue($numberOfLocalizableAttributes);
    }

    /**
     * @Then the report returns that the average of localizable attributes per family is :numberOfLocalizableAttributes
     *
     * @param int $numberOfLocalizableAttributes
     */
    public function theReportReturnsThatTheAverageOfLocalizableAttributesPerFamilyIs(int $numberOfLocalizableAttributes): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfLocalizableAttributes, $volumes['avg_localizable_attributes_per_family']['value']['average']);
    }
}
