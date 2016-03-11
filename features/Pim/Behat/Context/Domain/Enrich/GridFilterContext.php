<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Behat\Behat\Context\Step;
use Behat\Behat\Context\Step\Then;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Context\Page\Base\Grid;
use Pim\Behat\Decorator\GridDecorator\FilterDecorator;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

/**
 * A context for managing the grid pagination and size
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GridFilterContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param string $filterName
     *
     * @Then /^I show the filter "([^"]*)"$/
     */
    public function iShowTheFilter($filterName)
    {
        $this->getCurrentPage()->getGridFilters()->showFilter($filterName);
    }

    /**
     * @param string $filterName
     *
     * @Then /^I hide the filter "([^"]*)"$/
     */
    public function iHideTheFilter($filterName)
    {
        $this->getCurrentPage()->getGridFilters()->hideFilter($filterName);
    }

    /**
     * @param string $not
     * @param string $filters
     *
     * @throws ExpectationException
     *
     * @Then /^I should( not)? see the filters? (.*)$/
     */
    public function iShouldSeeTheFilters($not, $filters)
    {
        $toBeSeen = !(bool)$not;
        $filters = $this->getMainContext()->listToArray($filters);
        $gridFilters = $this->getCurrentPage()->getGridFilters();

        foreach ($filters as $filter) {
            if ($toBeSeen) {
                $gridFilters->assertFilterVisible($filter);
            } else {
                $gridFilters->assertFilterNotVisible($filter);
            }
        }
    }

    /**
     * @param string $not
     * @param string $filters
     *
     * @throws ExpectationException
     *
     * @Given /^I should( not)? see the available filters (.*)$/
     */
    public function iShouldSeeTheAvailableFilters($not, $filters)
    {
        $available = !(bool)$not;
        $filters = $this->getMainContext()->listToArray($filters);
        $gridFilters = $this->getCurrentPage()->getGridFilters();

        foreach ($filters as $filter) {
            if ($available && !$gridFilters->isFilterAvailable($filter)) {
                throw $this->getMainContext()->createExpectationException(
                    sprintf('Filter "%s" should be available.', $filter)
                );
            } elseif (!$available && $gridFilters->isFilterAvailable($filter)) {
                throw $this->getMainContext()->createExpectationException(
                    sprintf('Filter "%s" should not be available.', $filter)
                );
            }
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^I should be able to use the following filters:$/
     *
     * @return Then[]
     */
    public function iShouldBeAbleToUseTheFollowingFilters(TableNode $table)
    {
        $steps = [];

        foreach ($table->getHash() as $item) {
            $count  = count($this->getMainContext()->listToArray($item['result']));
            $filter = $item['filter'];
            $isCategoryFilter = false !== strpos(strtolower($filter), 'category');

            if (!$isCategoryFilter) {
                $steps[] = new Step\Then(sprintf('I show the filter "%s"', $filter));
            }
            $steps[] = new Step\Then(sprintf('I filter by "%s" with value "%s"', $filter, $item['value']));
            $steps[] = new Step\Then(sprintf('the grid should contain %d elements', $count));
            $steps[] = new Step\Then(sprintf('I should see entities %s', $item['result']));
            if (!$isCategoryFilter) {
                $steps[] = new Step\Then(sprintf('I hide the filter "%s"', $filter));
            }
        }

        return $steps;
    }

    /**
     * @param string $filterName
     * @param string $value
     *
     * @Then /^I filter by "([^"]*(?<!category))" with value "([^">=<]*)"$/
     */
    public function iFilterBy($filterName, $value)
    {
        $operatorPattern = '/^(contains|does not contain|is equal to|(?:starts|ends) with|in list) ([^">=<]*)|^empty$/';

        $datePattern = '#^(more than|less than|between|not between) (\d{2}/\d{2}/\d{4})( and )?(\d{2}/\d{2}/\d{4})?$#';
        $operator    = false;

        $matches = [];
        if (preg_match($datePattern, $value, $matches)) {
            $operator = $matches[1];
            $date     = $matches[2];
            if (5 === count($matches)) {
                $date   = [$date];
                $date[] = $matches[4];
            }
            $this->filterByDate($filterName, $date, $operator);
            $this->wait();

            return;
        }

        if (preg_match($operatorPattern, $value, $matches)) {
            if (count($matches) === 1) {
                $operator = $matches[0];
                $value    = false;
            } else {
                $operator = $matches[1];
                $value    = $matches[2];
            }

            $operators = [
                'contains'         => FilterDecorator::FILTER_CONTAINS,
                'does not contain' => FilterDecorator::FILTER_DOES_NOT_CONTAIN,
                'is equal to'      => FilterDecorator::FILTER_IS_EQUAL_TO,
                'starts with'      => FilterDecorator::FILTER_STARTS_WITH,
                'ends with'        => FilterDecorator::FILTER_ENDS_WITH,
                'empty'            => FilterDecorator::FILTER_IS_EMPTY,
                'in list'          => FilterDecorator::FILTER_IN_LIST,
            ];

            $operator = $operators[$operator];
        }

        $this->getCurrentPage()->getGridFilters()->filterBy($filterName, $value, $operator, $this->getSession()->getDriver());
        $this->wait();
    }

    /**
     * @param string $filterName
     * @param string $action
     * @param string $value
     * @param string $currency
     *
     * @When /^I filter by "([^"]*)" with value "(>|>=|=|<|<=) (\d+[.]?\d*) ([A-Z]{3})"$/
     */
    public function iFilterByPrice($filterName, $action, $value, $currency)
    {
        $this->getCurrentPage()->getGridFilters()->filterPerPrice($filterName, $action, $value, $currency);
        $this->wait();
    }

    /**
     * @param string $filterName
     * @param string $action
     * @param string $value
     * @param string $unit
     *
     * @Then /^I filter by "([^"]*)" with value "(>|>=|=|<|<=) (\d+[.]?\d*) ([a-zA-Z_]{1,2}|[a-zA-Z_]{4,})"$/
     */
    public function iFilterByMetric($filterName, $action, $value, $unit)
    {
        $this->getCurrentPage()->getGridFilters()->filterPerMetric($filterName, $action, $value, $unit);
        $this->wait();
    }

    /**
     * @param string $filterName
     * @param string $currency
     *
     * @Then /^I filter by price "([^"]*)" with empty value on "([^"]*)" currency$/
     */
    public function iFilterByPriceWithEmptyValue($filterName, $currency)
    {
        $this->getCurrentPage()->getGridFilters()->filterPerPrice($filterName, 'is empty', null, $currency);
        $this->wait();
    }

    /**
     * @param string $filterName
     * @param string $action
     * @param string $value
     *
     * @Then /^I filter by "([^"]*)" with value "(>|>=|=|<|<=) (\d+[.]?\d*)"$/
     */
    public function iFilterByNumber($filterName, $action, $value)
    {
        $this->getCurrentPage()->getGridFilters()->filterPerNumber($filterName, $action, $value);
        $this->wait();
    }

    /**
     * @param string $code
     *
     * @Given /^I filter by "category" with value "([^"]*)"$/
     */
    public function iFilterByCategory($code)
    {
        $this->wait();
        if (strtolower($code) === 'unclassified') {
            $this->getCurrentPage()->clickUnclassifiedCategoryFilterLink();
        } else {
            $category = $this->getFixturesContext()->getCategory($code);
            $this->getCurrentPage()->clickCategoryFilterLink($category);
        }

        $this->wait();
    }

    /**
     * @param string $filterName
     * @param mixed  $values
     * @param string $operator
     */
    protected function filterByDate($filterName, $values, $operator)
    {
        if (!is_array($values)) {
            $values = [$values, $values];
        }

        $gridFilters = $this->getCurrentPage()->getGridFilters();
        $filter = $gridFilters->getFilter($filterName);
        $gridFilters->openFilter($filter);

        $criteriaElt = $filter->find('css', 'div.filter-criteria');
        $criteriaElt->find('css', 'select.filter-select-oro')->selectOption($operator);

        $datepickers = $filter->findAll('css', '.date-visual-element');
        foreach ($datepickers as $i => $datepicker) {
            if ($datepicker->isVisible()) {
                $datepicker->setValue($values[$i]);
            }
        }

        $filter->find('css', 'button.filter-update')->click();
    }
}
