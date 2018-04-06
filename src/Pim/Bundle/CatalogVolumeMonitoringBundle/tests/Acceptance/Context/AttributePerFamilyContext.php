<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Acceptance\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\Normalizer;
use Webmozart\Assert\Assert;

final class AttributePerFamilyContext implements Context, SnippetAcceptingContext
{
    /** @var array */
    private $limits = [];

    /** @var array */
    private $familiesNumbers = [];

    /** @var array */
    private $volumes = [];

    /** @var Normalizer\Volumes */
    private $volumesNormalizer;

    /** @var InMemoryAverageMaxQuery */
    private $inMemoryQuery;

    /**
     * @param Normalizer\Volumes $volumesNormalizer
     */
    public function __construct(Normalizer\Volumes $volumesNormalizer, InMemoryAverageMaxQuery $inMemoryQuery)
    {
        $this->volumesNormalizer = $volumesNormalizer;
        $this->inMemoryQuery = $inMemoryQuery;
    }

    /**
     * @Given a family with :numberOfAttributes attributes
     */
    public function aFamilyWithAttributes(int $numberOfAttributes)
    {
        $this->familiesNumbers[] = $numberOfAttributes;
    }

    /**
     * @When the administrator user asks for the catalog volume monitoring report
     */
    public function theAdministratorUserAsksForTheCatalogVolumeMonitoringReport()
    {
        $this->inMemoryQuery->setAverageVolume(array_sum($this->familiesNumbers) / count($this->familiesNumbers));
        $this->inMemoryQuery->setMaxVolume(max($this->familiesNumbers));

        $this->volumes = $this->volumesNormalizer->volumes();
    }

    /**
     * @Then the report returns that the average number of attributes per family is :number
     */
    public function theReportReturnsThatTheMeanNumberOfAttributesPerFamilyIs(int $number)
    {
        Assert::eq($this->volumes['attributes_per_family']['value']['average'], $number);
    }

    /**
     * @Then the report returns that the maximum number of attributes per family is :number
     */
    public function theReportReturnsThatTheMaximumNumberOfAttributesPerFamilyIs(int $number)
    {
        Assert::eq($this->volumes['attributes_per_family']['value']['max'], $number);
    }

    /**
     * @Given the limit of the number of attributes per family is set to :limit
     */
    public function theLimitOfTheNumberOfIsSetTo(int $limit)
    {
        $this->inMemoryQuery->setLimit($limit);
    }

    /**
     * @Then the report warns the users that the number of attributes per family is high
     */
    public function theReportWarnsTheUsersThatTheNumberIsHigh()
    {
        Assert::true($this->volumes['attributes_per_family']['has_warning']);
    }
}
