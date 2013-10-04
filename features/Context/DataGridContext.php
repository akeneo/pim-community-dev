<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;

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
        $this->datagrid = $pageFactory->createPage('Base\Grid');
    }

    /**
     * @param integer $count
     *
     * @Given /^the grid should contain (\d+) elements?$/
     */
    public function theGridShouldContainElement($count)
    {
        if (intval($count) !== $actualCount = $this->datagrid->countRows()) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to see %d row(s) in the datagrid, actually saw %d.',
                    $count,
                    $actualCount
                )
            );
        }
    }

    /**
     * @param string $action
     * @param string $value
     * @param string $currency
     *
     * @When /^I filter per price (>|>=|=|<|<=) "([^"]*)" and currency "([^"]*)"$/
     */
    public function iFilterPerPrice($action, $value, $currency)
    {
        $this->getPage('Product index')->filterPerPrice($action, $value, $currency);
        $this->wait();
    }

    /**
     * @param string $code
     *
     * @Given /^I filter per category "([^"]*)"$/
     */
    public function iFilterPerCategory($code)
    {
        $category = $this->getWebUserContext()->getCategory($code);
        $this->getPage('Product index')->clickCategoryFilterLink($category);
        $this->wait();
    }

    /**
     * @Given /^I filter per unclassified category$/
     */
    public function iFilterPerUnclassifiedCategory()
    {
        $this->getPage('Product index')->clickUnclassifiedCategoryFilterLink();
        $this->wait();
    }

    /**
     * @param string $code
     *
     * @Given /^I filter per family ([^"]*)$/
     */
    public function iFilterPerFamily($code)
    {
        $this->getPage('Product index')->filterPerFamily($code);
        $this->wait();
    }

    /**
     * @param string $code
     *
     * @Given /^I filter per channel ([^"]*)$/
     */
    public function iFilterPerChannel($code)
    {
        $this->getPage('Product index')->filterPerChannel($code);
        $this->wait();
    }

    /**
     * @param string $column
     * @param string $row
     * @param string $expectation
     *
     * @Given /^Value of column "([^"]*)" of the row which contains "([^"]*)" should be "([^"]*)"$/
     */
    public function valueOfColumnOfTheRowWhichContainsShouldBe($column, $row, $expectation)
    {
        $column = strtoupper($column);
        if ($expectation !== $actual = $this->datagrid->getColumnValue($column, $row)) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting column "%s" to contain "%s", got "%s".',
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
            $filterNode = $this->datagrid->getFilter($filter);
            if (!$filterNode->isVisible()) {
                $this->createExpectationException(
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
            $filterNode = $this->datagrid->getFilter($filter);
            if ($filterNode->isVisible()) {
                $this->createExpectationException(
                    sprintf('Filter "%s" should not be visible', $filter)
                );
            }
        }
    }

    /**
     * @param string $filterName
     *
     * @Then /^I make visible the filter "([^"]*)"$/
     */
    public function iMakeVisibleTheFilter($filterName)
    {
        $this->datagrid->showFilter($filterName);
    }

    /**
     * @param string $filterName
     *
     * @Then /^I hide the filter "([^"]*)"$/
     */
    public function iHideTheFilter($filterName)
    {
        $this->datagrid->hideFilter($filterName);
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
        $countColumns    = $this->datagrid->countColumns();
        if ($expectedColumns !== $countColumns) {
            throw $this->createExpectationException(
                sprintf('Expected %d columns but contains %d', $expectedColumns, $countColumns)
            );
        }

        $expectedPosition = 0;
        foreach ($columns as $column) {
            $position = $this->datagrid->getColumnPosition(strtoupper($column));
            if ($expectedPosition++ !== $position) {
                throw $this->createExpectationException("The columns are not well ordered");
            }
        }
    }

    /**
     * @param string $order
     * @param string $columnName
     *
     * @Then /^the datas are sorted (ascending|descending) by (.*)$/
     */
    public function theDatasAreSortedBy($order, $columnName)
    {
        $columnName = strtoupper($columnName);

        if (!$this->datagrid->isSortedAndOrdered($columnName, $order)) {
            $this->createExpectationException(
                sprintf('The datas are not sorted %s on column %s', $order, $columnName)
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
        $this->datagrid->clickOnAction($element, $action);
    }

    /**
     * @param string $columnName
     * @param string $order
     *
     * @When /^I sort by "(.*)" value (ascending|descending)$/
     */
    public function iSortByValue($columnName, $order = 'ascending')
    {
        $columnName = strtoupper($columnName);

        if (!$this->datagrid->isSortedAndOrdered($columnName, $order)) {
            if ($this->datagrid->isSortedColumn($columnName)) {
                $this->sortByColumn($columnName);
            } else {
                // get the actual sorted columns
                $sortedColumns = $this->datagrid->getSortedColumns();

                // if we ask sorting by descending we must sort twice
                if ($order === 'descending') {
                    $this->sortByColumn($columnName);
                }
                $this->sortByColumn($columnName);

                // And we must remove the default sorted column
                foreach ($sortedColumns as $column) {
                    $this->removeSortOnColumn($column);
                    $this->wait();
                }
            }
        }
    }

    /**
     * Remove sort on a column with a loop but using a threshold to prevent
     * against infinite loop
     *
     * @param string $column
     *
     * @return null
     */
    private function removeSortOnColumn($column)
    {
        $threshold = 0;
        while ($this->datagrid->isSortedColumn($column)) {
            $this->datagrid->getColumnSorter($column)->click();
            $this->wait();

            if ($threshold++ === 3) {
                return;
            }
        }
    }

    /**
     * Sort by a column name
     * @param strign $columnName
     */
    protected function sortByColumn($columnName)
    {
        $this->datagrid->getColumnSorter($columnName)->click();
        $this->wait();
    }

    /**
     * @param string $columns
     *
     * @Then /^the datas can be sorted by (.*)$/
     */
    public function theDatasCanBeSortedBy($columns)
    {
        $columns = $this->getMainContext()->listToArray($columns);

        try {
            foreach ($columns as $columnName) {
                $this->datagrid->getColumnSorter($columnName);
            }
        } catch (\InvalidArgumentException $e) {
            $this->createExpectationException($e->getMessage());
        }
    }

    /**
     * @param string $elements
     *
     * @throws ExpectationException
     *
     * @Then /^I should see products? ((?!with data).)*$/
     * @Then /^I should see attributes? ((?!in group).)*$/
     * @Then /^I should see channels? (.*)$/
     * @Then /^I should see locales? (.*)$/
     * @Then /^I should see (?:import|export) profiles? (.*)$/
     * @Then /^I should see (?:(?:entit|currenc)(?:y|ies)) (.*)$/
     * @Then /^I should see variants? (.*)$/
     */
    public function iShouldSeeEntities($elements)
    {
        $elements = $this->getMainContext()->listToArray($elements);

        foreach ($elements as $element) {
            if (!$this->datagrid->getRow($element)) {
                throw $this->createExpectationException(sprintf('Entity "%s" not found', $element));
            }
        }
    }

    /**
     * @param array $entities
     *
     * @Then /^I should not see products? ((?!with data).)*$/
     * @Then /^I should not see attributes? ((?!in group).)*$/
     * @Then /^I should not see channels? (.*)$/
     * @Then /^I should not see locales? (.*)$/
     * @Then /^I should not see (?:import|export) profiles? (.*)$/
     * @Then /^I should not see (?:(?:entit|currenc)(?:y|ies)) (.*)$/
     * @Then /^I should not see variants? (.*)$/
     */
    public function iShouldNotSeeEntities($entities)
    {
        $entities = $this->getMainContext()->listToArray($entities);

        foreach ($entities as $entity) {
            try {
                $this->datagrid->getRow($entity);
                $this->createExpectationException(
                    sprintf('Entity "%s" should not be seen', $entity)
                );
            } catch (\InvalidArgumentException $e) {
                // here we must catch an exception because the row is not found
                continue;
            }
        }
    }

    /**
     * @param array $elements
     *
     * @throws \InvalidArgumentException
     *
     * @Then /^I should see sorted channels (.*)$/
     * @Then /^I should see sorted currencies (.*)$/
     * @Then /^I should see sorted locales (.*)$/
     * @Then /^I should see sorted attributes (.*)$/
     * @Then /^I should see sorted (?:import|export) profiles (.*)$/
     * @Then /^I should see sorted (?:entities) (.*)$/
     * @Then /^I should see sorted variants (.*)$/
     */
    public function iShouldSeeSortedEntities($elements)
    {
        $elements = $this->getMainContext()->listToArray($elements);

        if ($this->datagrid->countRows() !== count($elements)) {
            throw $this->createExpectationException(
                'You must define all the entities in the grid to check the sorting'
            );
        }

        $expectedPosition = 0;
        foreach ($elements as $element) {
            $position = $this->datagrid->getRowPosition($element);
            if ($expectedPosition !== $position) {
                $errorMsg = sprintf(
                    'Value %s is expected at position %d but is at position %d',
                    $element,
                    $expectedPosition,
                    $position
                );
                throw $this->createExpectationException(
                    sprintf("The entities are not well sorted\n%s", $errorMsg)
                );
            }
            $expectedPosition++;
        }
    }

    /**
     * @param string $filterName
     * @param string $value
     *
     * @Then /^I filter by "([^"]*)" with value "([^"]*)"$/
     */
    public function iFilterBy($filterName, $value)
    {
        $this->datagrid->filterBy($filterName, $value);
        $this->wait();
    }

    /**
     * @param string $row
     *
     * @When /^I click on the "([^"]*)" row$/
     */
    public function iClickOnTheRow($row)
    {
        $this->datagrid->getRow($row)->click();
        $this->wait(4000, null);
    }

    /**
     * @Then /^I reset the grid$/
     */
    public function iResetTheGrid()
    {
        $this->datagrid->clickOnResetButton();
        $this->wait();
    }

    /**
     * @Then /^I refresh the grid$/
     */
    public function iRefrestTheGrid()
    {
        $this->datagrid->clickOnRefreshButton();
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
     *
     * @return void
     */
    private function wait($time = 4000, $condition = 'document.readyState == "complete" && !$.active')
    {
        return $this->getMainContext()->wait($time, $condition);
    }

    /**
     *
     * @return \Behat\Behat\Context\ExtendedContextInterface
     */
    private function getWebUserContext()
    {
        return $this->getMainContext()->getSubcontext('webUser');
    }

    /**
     * @param string $name
     *
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Page
     */
    public function getPage($name)
    {
        return $this->getWebUserContext()->getPage($name);
    }
}
