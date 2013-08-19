<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;

/**
 * Feature context for the datagrid related steps
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
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
     * @param string $not
     * @param string $elements
     *
     * @Given /^the grid should (not )?contain the elements? (.*)$/
     */
    public function theGridShouldContainTheElements($not, $elements)
    {
        $elements = $this->getMainContext()->listToArray($elements);

        foreach ($elements as $element) {
            if ($not) {
                try {
                    $this->datagrid->getRow($element);
                } catch (\InvalidArgumentException $e) {
                    continue;
                }
                throw new \InvalidArgumentException(
                    sprintf('The grid should not contain the element %s', $element)
                );
            } else {
                $this->datagrid->getRow($element);
            }
        }
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
     * @Given /^I should see the filters? (.*)$/
     */
    public function iShouldSeeTheFilters($filters)
    {
        $filters = $this->getMainContext()->listToArray($filters);
        foreach ($filters as $filter) {
            $this->datagrid->getFilter($filter);
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
        $countColumns    = $this->datagrid->countColumns()-1;
        if ($expectedColumns !== $countColumns) {
            throw $this->createExpectationException(
                'Expected %d columns but contains %d',
                $expectedColumns,
                $countColumns
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
     * @Given /^I click on the "([^"]*)" action of the row which contains "([^"]*)"$/
     */
    public function iClickOnTheActionOfTheRowWhichContains($actionName, $element)
    {
        $this->datagrid->clickOnAction($element, $actionName);
    }

    /**
     * @param string $column
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
                // if we ask sorting by descending we must sort twice
                if ($order === 'descending') {
                    $this->sortByColumn($columnName);
                }
                $this->sortByColumn($columnName);
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
     * @Then /^I should see entities (.*)$/
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
     * @param array $currencies
     *
     * @throws \InvalidArgumentException
     *
     * @Then /^I should see sorted entities (.*)$/
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
     * @param array $entities
     *
     * @When /^I should not see entities (.*)$/
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
     * @param string $filter
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
        $this->wait(5000, null);
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
    private function wait($time = 5000, $condition = 'document.readyState == "complete" && !$.active')
    {
        return $this->getMainContext()->wait($time, $condition);
    }
}
