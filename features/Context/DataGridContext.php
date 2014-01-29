<?php

namespace Context;

use Behat\Behat\Context\Step\Then;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;
use Context\Page\Base\Grid;

/**
 * Feature context for the datagrid related steps
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataGridContext extends RawMinkContext implements PageObjectAwareInterface
{
    /**
     * @var \SensioLabs\Behat\PageObjectExtension\Context\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Context\Page\Base\Grid
     */
    protected $datagrid;

    /**
     * @param PageFactory $pageFactory
     */
    public function setPageFactory(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * @param integer $count
     *
     * @Given /^the grid should contain (\d+) elements?$/
     */
    public function theGridShouldContainElement($count)
    {
        $this->wait();
        if ($count > 10) {
            $this->getCurrentPage()->changePageSize(100);
            $this->wait();
        }

        assertEquals(
            $count,
            $actualCount = $this->getCurrentPage()->getToolbarCount(),
            sprintf('Expecting to see %d record(s) in the datagrid toolbar, actually saw %d', $count, $actualCount)
        );

        assertEquals(
            $count,
            $actualCount = $this->getCurrentPage()->countRows(),
            sprintf('Expecting to see %d row(s) in the datagrid, actually saw %d.', $count, $actualCount)
        );
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
        $this->getCurrentPage()->filterPerPrice($filterName, $action, $value, $currency);
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
        $this->getCurrentPage()->filterPerMetric($filterName, $action, $value, $unit);
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
     * @param string    $code
     * @param TableNode $table
     *
     * @Then /^the row "([^"]*)" should contain:$/
     */
    public function theRowShouldContain($code, TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->assertColumnContainsValue($code, $data['column'], $data['value']);
        }
    }

    /**
     * @param string $row
     * @param string $column
     * @param string $expectation
     *
     * @throws ExpectationException
     */
    protected function assertColumnContainsValue($row, $column, $expectation)
    {
        $column = strtoupper($column);
        $actual = $this->getCurrentPage()->getColumnValue($column, $row);

        if ($expectation !== $actual) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting column "%s" to contain "%s", got "%s"',
                    $column,
                    $expectation,
                    $actual
                )
            );
        }
    }

    /**
     * @param string $filters
     *
     * @Then /^I should see the filters? (.*)$/
     */
    public function iShouldSeeTheFilters($filters)
    {
        $filters = $this->getMainContext()->listToArray($filters);
        foreach ($filters as $filter) {
            $filterNode = $this->getCurrentPage()->getFilter($filter);
            if (!$filterNode->isVisible()) {
                throw $this->createExpectationException(
                    sprintf('Filter "%s" should be visible', $filter)
                );
            }
        }
    }

    /**
     * @param string $filters
     *
     * @Then /^I should not see the filters? (.*)$/
     */
    public function iShouldNotSeeTheFilters($filters)
    {
        $filters = $this->getMainContext()->listToArray($filters);
        foreach ($filters as $filter) {
            $filterNode = $this->getCurrentPage()->getFilter($filter);
            if ($filterNode->isVisible()) {
                throw $this->createExpectationException(
                    sprintf('Filter "%s" should not be visible', $filter)
                );
            }
        }
    }

    /**
     * @param string $filterName
     *
     * @Then /^I show the filter "([^"]*)"$/
     */
    public function iShowTheFilter($filterName)
    {
        if (strtolower($filterName) !== 'category') {
            $this->getCurrentPage()->showFilter($filterName);
        }
    }

    /**
     * @param string $filterName
     *
     * @Then /^I hide the filter "([^"]*)"$/
     */
    public function iHideTheFilter($filterName)
    {
        if (strtolower($filterName) !== 'category') {
            $this->getCurrentPage()->hideFilter($filterName);
        }
    }

    /**
     * @param string $columns
     *
     * @Then /^I should see the columns? (.*)$/
     */
    public function iShouldSeeTheColumns($columns)
    {
        $columns = $this->getMainContext()->listToArray($columns);

        $expectedColumns = count($columns);
        $countColumns    = $this->getCurrentPage()->getGridColumnsCount();
        if ($expectedColumns !== $countColumns) {
            throw $this->createExpectationException(
                sprintf('Expected %d columns but contains %d', $expectedColumns, $countColumns)
            );
        }

        $expectedPosition = 0;
        foreach ($columns as $column) {
            $position = $this->getCurrentPage()->getColumnPosition($column);
            if ($expectedPosition++ !== $position) {
                throw $this->createExpectationException(
                    sprintf(
                        'Column "%s" was expected in position %d, but was at %d',
                        $column,
                        $expectedPosition,
                        $position
                    )
                );
            }
        }
    }

    /**
     * @param string $order
     * @param string $columnName
     *
     * @Then /^the rows should be sorted (ascending|descending) by (.*)$/
     */
    public function theRowsShouldBeSortedBy($order, $columnName)
    {
        $columnName = strtoupper($columnName);

        if (!$this->getCurrentPage()->isSortedAndOrdered($columnName, $order)) {
            throw $this->createExpectationException(
                sprintf('The rows are not sorted %s by column %s', $order, $columnName)
            );
        }
    }

    /**
     * @param string $actionName
     * @param string $element
     *
     * @When /^I (delete) the "([^"]*)" job$/
     * @Given /^I click on the "([^"]*)" action of the row which contains "([^"]*)"$/
     */
    public function iClickOnTheActionOfTheRowWhichContains($actionName, $element)
    {
        $action = ucfirst(strtolower($actionName));
        $this->getCurrentPage()->clickOnAction($element, $action);
    }

    /**
     * @param string $columns
     *
     * @Then /^I should be able to sort the rows by (.*)$/
     *
     * @return Then[]
     */
    public function iShouldBeAbleToSortTheRowsBy($columns)
    {
        $steps = array(
            new Then(sprintf('the rows should be sortable by %s', $columns))
        );
        $columns = $this->getMainContext()->listToArray($columns);

        foreach ($columns as $column) {
            $steps[] = new Then(sprintf('I sort by "%s" value ascending', $column));
            $steps[] = new Then(sprintf('the rows should be sorted ascending by %s', $column));
            $steps[] = new Then(sprintf('I sort by "%s" value descending', $column));
            $steps[] = new Then(sprintf('the rows should be sorted descending by %s', $column));
        }

        return $steps;
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
        $steps = array();

        foreach ($table->getHash() as $item) {
            $count = count($this->getMainContext()->listToArray($item['result']));
            $filter = $item['filter'];
            $steps[] = new Then(sprintf('I show the filter "%s"', $filter));
            $steps[] = new Then(sprintf('I filter by "%s" with value "%s"', $filter, $item['value']));
            $steps[] = new Then(sprintf('the grid should contain %d elements', $count));
            $steps[] = new Then(sprintf('I should see entities %s', $item['result']));
            $steps[] = new Then(sprintf('I hide the filter "%s"', $filter));
        }

        return $steps;
    }

    /**
     * @param string $columnName
     * @param string $order
     *
     * @When /^I sort by "(.*)" value (ascending|descending)$/
     */
    public function iSortByValue($columnName, $order = 'ascending')
    {
        $this->getCurrentPage()->sortBy($columnName, $order);
        $this->wait();
    }

    /**
     * @param string $columns
     *
     * @Then /^the rows should be sortable by (.*)$/
     */
    public function theRowsShouldBeSortableBy($columns)
    {
        $columns = $this->getMainContext()->listToArray($columns);

        try {
            foreach ($columns as $columnName) {
                $this->getCurrentPage()->getColumnSorter($columnName);
            }
        } catch (\InvalidArgumentException $e) {
            throw $this->createExpectationException($e->getMessage());
        }
    }

    /**
     * @param string $elements
     *
     * @throws ExpectationException
     *
     * @Then /^I should see products? (.*)$/
     * @Then /^I should see attributes? (?!(?:.*)in group )(.*)$/
     * @Then /^I should see channels? (.*)$/
     * @Then /^I should see locales? (.*)$/
     * @Then /^I should see (?:import|export) profiles? (.*)$/
     * @Then /^I should see (?:(?:entit|currenc)(?:y|ies)) (.*)$/
     * @Then /^I should see groups? (?:types )?(.*)$/
     * @Then /^I should see association (?:types? )?(.*)$/
     * @Then /^I should see users? (.*)$/
     * @Then /^I should see famil(?:y|ies) (.*)$/
     */
    public function iShouldSeeEntities($elements)
    {
        $elements = $this->getMainContext()->listToArray($elements);

        if (count($elements) > 10) {
            $this->getCurrentPage()->changePageSize(100);
            $this->wait();
        }

        foreach ($elements as $element) {
            if (!$this->getCurrentPage()->getRow($element)) {
                throw $this->createExpectationException(sprintf('Entity "%s" not found', $element));
            }
        }
    }

    /**
     * @param array $entities
     *
     * @Then /^I should not see products? (.*)$/
     * @Then /^I should not see attributes? (?!(.*)in group )(.*)$/
     * @Then /^I should not see channels? (.*)$/
     * @Then /^I should not see locales? (.*)$/
     * @Then /^I should not see (?:import|export) profiles? (.*)$/
     * @Then /^I should not see (?:(?:entit|currenc)(?:y|ies)) (.*)$/
     * @Then /^I should not see group(?: type)?s? (.*)$/
     * @Then /^I should not see association (?:types? )?(.*)$/
     * @Then /^I should not see famil(?:y|ies) (.*)$/
     */
    public function iShouldNotSeeEntities($entities)
    {
        $entities = $this->getMainContext()->listToArray($entities);

        foreach ($entities as $entity) {
            try {
                $this->getCurrentPage()->getRow($entity);
                throw $this->createExpectationException(
                    sprintf('Entity "%s" should not be seen', $entity)
                );
            } catch (\InvalidArgumentException $e) {
                // here we must catch an exception because the row is not found
                continue;
            }
        }
    }

    /**
     * @param string $filterName
     * @param string $value
     *
     * @Then /^I filter by "((?!category)[^"]*)" with value "([^">=<]*)"$/
     */
    public function iFilterBy($filterName, $value)
    {
        $operatorPattern = '/^(contains|does not contain|is equal to|(?:starts|ends) with) ([^">=<]*)$/';
        $operator = false;

        $matches = array();
        if (preg_match($operatorPattern, $value, $matches)) {
            $operator = $matches[1];
            $value    = $matches[2];

            $operators = array(
                'contains'         => Grid::FILTER_CONTAINS,
                'does not contain' => Grid::FILTER_DOES_NOT_CONTAIN,
                'is equal to'      => Grid::FILTER_IS_EQUAL_TO,
                'starts with'      => Grid::FILTER_STARTS_WITH,
                'ends with'        => Grid::FILTER_ENDS_WITH
            );

            $operator = $operators[$operator];
        }

        $this->getCurrentPage()->filterBy($filterName, $value, $operator);
        $this->wait();
    }

    /**
     * @param string $row
     *
     * @When /^I click on the "([^"]*)" row$/
     */
    public function iClickOnTheRow($row)
    {
        $this->getCurrentPage()->getRow($row)->click();
        $this->wait();
    }

    /**
     * @param string $rows
     *
     * @throws ExpectationException
     *
     * @When /^I check the rows? "([^"]*)"$/
     */
    public function iCheckTheRows($rows)
    {
        $this->wait();
        $rows = $this->getMainContext()->listToArray($rows);

        foreach ($rows as $row) {
            $gridRow = $this->getCurrentPage()->getRow($row);
            $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');

            if (!$checkbox) {
                throw $this->createExpectationException(sprintf('Unable to find a checkbox for row %s', $row));
            }

            $checkbox->check();
        }
        $this->wait();
    }

    /**
     * @param string $rows
     *
     * @throws ExpectationException
     *
     * @Then /^the rows? "([^"]*)" should be checked$/
     */
    public function theRowShouldBeChecked($rows)
    {
        $rows = $this->getMainContext()->listToArray($rows);

        foreach ($rows as $row) {
            $gridRow = $this->getCurrentPage()->getRow($row);
            $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');

            if (!$checkbox) {
                throw $this->createExpectationException(sprintf('Unable to find a checkbox for row %s', $row));
            }

            if (!$checkbox->isChecked()) {
                throw $this->createExpectationException(sprintf('Expecting row %s to be checked', $row));
            }
        }
    }

    /**
     * @Then /^I reset the grid$/
     */
    public function iResetTheGrid()
    {
        $this->getCurrentPage()->clickOnResetButton();
        $this->wait();
    }

    /**
     * @Then /^I refresh the grid$/
     */
    public function iRefrestTheGrid()
    {
        $this->getCurrentPage()->clickOnRefreshButton();
        $this->wait();
    }

    /**
     * @Then /^I click back to grid$/
     */
    public function iClickBackToGrid()
    {
        $this->getSession()->getPage()->clickLink('Back to grid');
        $this->wait();
    }

    /**
     * @When /^I hide the "([^"]*)" column$/
     */
    public function iHideTheColumn($column)
    {
        $this->getCurrentPage()->openConfigurationPopin();
        $this->wait();
        $this->getCurrentPage()->hideColumn($column);
        $this->wait();
    }

    /**
     * Create an expectation exception
     *
     * @param string $message
     *
     * @return ExpectationException
     */
    private function createExpectationException($message)
    {
        return $this->getMainContext()->createExpectationException($message);
    }

    /**
     * Wait
     *
     * @param integer $time
     * @param string  $condition
     */
    private function wait($time = 10000, $condition = null)
    {
        $this->getMainContext()->wait($time, $condition);
    }

    /**
     * @return \Behat\Behat\Context\ExtendedContextInterface
     */
    private function getNavigationContext()
    {
        return $this->getMainContext()->getSubcontext('navigation');
    }

    /**
     * @return \Behat\Behat\Context\ExtendedContextInterface
     */
    private function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }

    /**
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Page
     */
    public function getCurrentPage()
    {
        return $this->getNavigationContext()->getCurrentPage();
    }
}
