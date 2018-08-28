<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Pim\Bundle\AnalyticsBundle\DataCollector\AttributeDataCollector;
use Webmozart\Assert\Assert;

final class SystemInfoAttributeContext implements Context
{
    /** @var array */
    private $collector = [];

    /** @var AttributeDataCollector */
    private $attributeDataCollector;

    /**
     * @param AttributeDataCollector $attributeDataCollector
     */
    public function __construct(AttributeDataCollector $attributeDataCollector)
    {
        $this->attributeDataCollector = $attributeDataCollector;
    }

    /**
     * @When attribute statistics of the customer's catalog are collected
     */
    public function theStatisticsAreCollectedFromThisCustomerCatalog(): void
    {
        $this->collector = $this->attributeDataCollector->collect();
    }

    /**
     * @Then Akeneo statistics engine stores a number of :numberOfAttribute useable as grid filter attribute for this customer
     *
     * @param int $numberOfAttribute
     */
    public function theSystemInformationReturnsThatTheNumberOfUseableAsGridFilterAttributeIs(int $numberOfAttribute): void
    {
        $collectedInfo = $this->getCollectedInformation();
        Assert::eq($numberOfAttribute, $collectedInfo['nb_useable_as_grid_filter_attributes']);
    }

    /**
     * @Then Akeneo statistics engine stores an average of :averageOfAttributes scopable attributes per family for this customer
     *
     * @param int $averageOfAttributes
     */
    public function theSystemInformationReturnsThatTheAverageOfScopableAttributesPerFamilyIs(int $averageOfAttributes): void
    {
        $collectedInfo = $this->getCollectedInformation();
        Assert::eq($averageOfAttributes, $collectedInfo['avg_scopable_attributes_per_family']);
    }

    /**
     * @Then Akeneo statistics engine stores an average of :averageOfAttributes localizable attributes per family for this customer
     *
     * @param int $averageOfAttributes
     */
    public function theSystemInformationReturnsThatTheAverageOfLocalizableAttributesPerFamilyIs(int $averageOfAttributes): void
    {
        $collectedInfo = $this->getCollectedInformation();
        Assert::eq($averageOfAttributes, $collectedInfo['avg_localizable_attributes_per_family']);
    }

    /**
     * @Then Akeneo statistics engine stores an average of :averageOfAttributes localizable and scopable attributes per family for this customer
     *
     * @param int $averageOfAttributes
     */
    public function theSystemInformationReturnsThatTheAverageOfLocalizableAndScopableAttributesPerFamilyIs(int $averageOfAttributes): void
    {
        $collectedInfo = $this->getCollectedInformation();
        Assert::eq($averageOfAttributes, $collectedInfo['avg_scopable_localizable_attributes_per_family']);
    }

    /**
     * @return array
     */
    public function getCollectedInformation(): array
    {
        return $this->collector;
    }
}
