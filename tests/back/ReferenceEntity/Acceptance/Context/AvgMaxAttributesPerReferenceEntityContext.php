<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\ReferenceEntity\Acceptance\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context\ReportContext;
use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class AvgMaxAttributesPerReferenceEntityContext implements Context
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
     * @Given /^a reference entity with (\d+) attributes$/
     */
    public function aReferenceEntityWithattributes(int $numberOfattributes)
    {
        $this->inMemoryQuery->addValue($numberOfattributes);
    }

    /**
     * @Then the report returns that the average number of attributes per reference entity is :attributesPerReferenceEntity
     */
    public function theReportReturnsThatTheattributesPerReferenceEntityIs(int $attributesPerReferenceEntity): void
    {
        $volumes = $this->reportContext->getVolumes();
        Assert::eq($attributesPerReferenceEntity, $volumes['average_max_attributes_per_reference_entities']['value']['average']);
    }

    /**
     * @Given /^the report returns that the maximum number of attributes per reference entity is (\d+)$/
     */
    public function theReportReturnsThatTheMaximumNumberOfattributesPerReferenceEntityIs($number)
    {
        $volumes = $this->reportContext->getVolumes();
        Assert::eq($volumes['average_max_attributes_per_reference_entities']['value']['max'], $number);
    }
}
