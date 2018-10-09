<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector\DBDataCollector;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class SystemInfoContext implements Context
{
    /** @var array */
    private $collector = [];

    /** @var DBDataCollector */
    private $dbDataCollector;

    /**
     * @param DBDataCollector $dbDataCollector
     */
    public function __construct(DBDataCollector $dbDataCollector)
    {
        $this->dbDataCollector = $dbDataCollector;
    }

    /**
     * @When statistics of the customer's catalog are collected
     */
    public function theStatisticsAreCollectedFromThisCustomerCatalog(): void
    {
        $this->collector = $this->dbDataCollector->collect();
    }

    /**
     * @Then Akeneo statistics engine stores a number of :numberOfChannels channels for this customer
     *
     * @param int $numberOfChannels
     */
    public function theSystemInformationReturnsThatTheNumberOfChannelsIs(int $numberOfChannels): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfChannels, $collectedInfo['nb_channels']);
    }

    /**
     * @Then Akeneo statistics engine stores a number of :numberOfLocales locales for this customer
     *
     * @param int $numberOfLocales
     */
    public function theSystemInformationReturnsThatTheNumberOfLocalesIs(int $numberOfLocales): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfLocales, $collectedInfo['nb_locales']);
    }

    /**
     * @Then Akeneo statistics engine stores a number of :numberOfProducts products for this customer
     *
     * @param int $numberOfProducts
     */
    public function theSystemInformationReturnsThatTheNumberOfProductsIs(int $numberOfProducts): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfProducts, $collectedInfo['nb_products']);
    }

    /**
     * @Then Akeneo statistics engine stores a number of :numberOfProductModels product models for this customer
     *
     * @param int $numberOfProductModels
     */
    public function theSystemInformationReturnsThatTheNumberOfProductModesIs(int $numberOfProductModels): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfProductModels, $collectedInfo['nb_product_models']);
    }

    /**
     * @Then Akeneo statistics engine stores a number of :numberOfVariantProducts variant products for this customer
     *
     * @param int $numberOfVariantProducts
     */
    public function theSystemInformationReturnsThatTheNumberOfVariantProductsIs(int $numberOfVariantProducts): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfVariantProducts, $collectedInfo['nb_variant_products']);
    }

    /**
     * @Then Akeneo statistics engine stores a number of :numberOfFamilies families for this customer
     *
     * @param int $numberOfFamilies
     */
    public function theSystemInformationReturnsThatTheNumberOfFamiliesIs(int $numberOfFamilies): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfFamilies, $collectedInfo['nb_families']);
    }

    /**
     * @Then Akeneo statistics engine stores a number of :numberOfUsers users for this customer
     *
     * @param int $numberOfUsers
     */
    public function theSystemInformationReturnsThatTheNumberOfUsersIs(int $numberOfUsers): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfUsers, $collectedInfo['nb_users']);
    }

    /**
     * @Then Akeneo statistics engine stores a number of :numberOfCategories categories for this customer
     *
     * @param int $numberOfCategories
     */
    public function theSystemInformationReturnsThatTheNumberOfCategoriesIs(int $numberOfCategories): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfCategories, $collectedInfo['nb_categories']);
    }

    /**
     * @Then Akeneo statistics engine stores a number of :numberOfCategoryTrees category trees for this customer
     *
     * @param int $numberOfCategoryTrees
     */
    public function theSystemInformationReturnsThatTheNumberOfCategoryTreesIs(int $numberOfCategoryTrees): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfCategoryTrees, $collectedInfo['nb_category_trees']);
    }

    /**
     * @Then Akeneo statistics engine stores a maximum number of :maxCategoryInOneCategory categories in one category for this customer
     *
     * @param int $maxCategoryInOneCategory
     */
    public function theSystemInformationReturnsThatTheMaxOfCategoryInOneCategoryIs(int $maxCategoryInOneCategory): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($maxCategoryInOneCategory, $collectedInfo['max_category_in_one_category']);
    }

    /**
     * @Then Akeneo statistics engine stores a maximum number of :maxCategoryLevels category levels for this customer
     *
     * @param int $maxCategoryLevels
     */
    public function theSystemInformationReturnsThatTheMaxOfCategoryLevelsIs(int $maxCategoryLevels): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($maxCategoryLevels, $collectedInfo['max_category_levels']);
    }

    /**
     * @Then Akeneo statistics engine stores a number of :numberOfProductValues product values for this customer
     *
     * @param int $numberOfProductValues
     */
    public function theSystemInformationReturnsThatTheNumberOfProductValuesIs(int $numberOfProductValues): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($numberOfProductValues, $collectedInfo['nb_product_values']);
    }

    /**
     * @Then Akeneo statistics engine stores an average number of :avgOfProductValuesByProduct product values for this customer
     *
     * @param int $avgOfProductValuesByProduct
     */
    public function theSystemInformationReturnsThatTheAverageOfProductValuesByProductIs(int $avgOfProductValuesByProduct): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($avgOfProductValuesByProduct, $collectedInfo['avg_product_values_by_product']);
    }

    /**
     * @Then Akeneo statistics engine stores an average number of :avgOfProductValuesByProduct product values per family for this customer
     *
     * @param int $avgOfProductValuesByProduct
     */
    public function theSystemInformationReturnsThatTheAverageOfProductValuesPerFamilyIs(int $avgOfProductValuesByProduct): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($avgOfProductValuesByProduct, $collectedInfo['avg_product_values_by_family']);
    }

    /**
     * @Then Akeneo statistics engine stores a maximum number of :avgOfProductValuesByProduct product values per family for this customer
     *
     * @param int $avgOfProductValuesByProduct
     */
    public function theSystemInformationReturnsThatTheMaximumOfProductValuesPerFamilyIs(int $avgOfProductValuesByProduct): void
    {
        $collectedInfo = $this->getCollectedInformation();

        Assert::eq($avgOfProductValuesByProduct, $collectedInfo['max_product_values_by_family']);
    }

    /**
     * @return array
     */
    public function getCollectedInformation(): array
    {
        return $this->collector;
    }
}
