<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryAverageMaxQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class ProductValuePerFamilyContext implements Context
{
    /** @var ReportContext */
    private $reportContext;

    /** @var InMemoryAverageMaxQuery */
    private $averageMaxQuery;

    /** @var int */
    private $nbChannels = 0;

    /** @var int */
    private $nbLocales = 0;

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
     * @Given a channel defined with :numberOfLocales activated locales
     *
     * @param int $numberOfLocales
     */
    public function aChannelDefinedWithActivatedLocales(int $numberOfLocales): void
    {
        $this->nbChannels++;
        $this->nbLocales = $this->nbLocales + $numberOfLocales;
    }

    /**
     * @Given a family with :numberOfAttributes attributes, :numberOfLocAttributes localizable attributes, :numberOfScopAttributes scopable attributes and :numberOfLocScopAttributes scopable and localizable attributes
     *
     * @param int $numberOfAttributes
     * @param int $numberOfLocAttributes
     * @param int $numberOfScopAttributes
     * @param int $numberOfLocScopAttributes
     */
    public function aFamilyWithAttributes(
        int $numberOfAttributes,
        int $numberOfLocAttributes,
        int $numberOfScopAttributes,
        int $numberOfLocScopAttributes
    ): void {
        $nbPotentialProductValues = $numberOfAttributes
            + $numberOfLocAttributes * $this->nbLocales
            + $numberOfScopAttributes * $this->nbChannels
            + $numberOfLocScopAttributes * $this->nbLocales * $this->nbChannels;

        $this->averageMaxQuery->addValue($nbPotentialProductValues);
    }

    /**
     * @Then the report returns that the average of product values per family is :numberOfProductValues
     *
     * @param int $numberOfProductValues
     */
    public function theReportReturnsThatTheAverageOfProductValuesPerFamilyIs(int $numberOfProductValues): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfProductValues, $volumes['count_product_and_product_model_values']['value']['average']);
    }

    /**
     * @Then the report returns that the maximum of product values per family is :numberOfProductValues
     *
     * @param int $numberOfProductValues
     */
    public function theReportReturnsThatTheMaximumOfProductValuesPerFamilyIs(int $numberOfProductValues): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfProductValues, $volumes['count_product_and_product_model_values']['value']['max']);
    }
}
