<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\CatalogVolumeLimits;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Pim\Component\Catalog\VolumeLimits\Model\Query\AttributesPerFamily;
use Pim\Component\Catalog\VolumeLimits\Application\GetVolumes;
use Webmozart\Assert\Assert;

final class AttributePerFamilyContext implements Context, SnippetAcceptingContext
{
    private $limits = [];

    /**
     * @Given a family with :numberOfAttributes attributes
     */
    public function aFamilyWithAttributes($numberOfAttributes)
    {
        $this->familiesNumbers[] = $numberOfAttributes;
    }

    /**
     * @When the administrator user asks for the catalog volume monitoring report
     */
    public function theAdministratorUserAsksForTheCatalogVolumeMonitoringReport()
    {
        $numbers = [
            'mean' => array_sum($this->familiesNumbers) / count($this->familiesNumbers),
            'max' => max($this->familiesNumbers),
        ];
        $attributesPerFamily = $this->mockAttributesPerFamilyQuery($numbers);

        $this->getVolumes = new GetVolumes($attributesPerFamily, $this->limits);

        $this->volumes = ($this->getVolumes)();
    }

    /**
     * @Then the report returns that the mean number of attributes per family is :number
     */
    public function theReportReturnsThatTheMeanNumberOfAttributesPerFamilyIs($number)
    {
        Assert::eq($this->volumes['attributes_per_family']['value']['mean'], $number);
    }

    /**
     * @Then the report returns that the maximum number of attributes per family is :number
     */
    public function theReportReturnsThatTheMaximumNumberOfAttributesPerFamilyIs($number)
    {
        Assert::eq($this->volumes['attributes_per_family']['value']['max'], $number);
    }

    private function mockAttributesPerFamilyQuery($numbers): AttributesPerFamily
    {
        return new class($numbers) implements AttributesPerFamily {
            private $numbers;

            public function __construct(array $numbers)
            {
                $this->numbers = $numbers;
            }

            public function __invoke(): array
            {
                return $this->numbers;
            }
        };
    }

    /**
     * @Given the limit of the number of :what is set to :limit
     */
    public function theLimitOfTheNumberOfIsSetTo($what, $limit)
    {
        $this->limits[$what] = $limit;
    }

    /**
     * @Then the report warns the users that the number of :what is high
     */
    public function theReportWarnsTheUsersThatTheNumberIsHigh($what)
    {
        Assert::true($this->volumes[$what]['limit_reached']);
    }
}
