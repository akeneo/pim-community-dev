<?php

namespace Context;

use Behat\Behat\Context\Step;
use Behat\Behat\Context\Step\Then;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\RawMinkContext;
use Context\Page\Base\Grid;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;

/**
 * Feature context for the datagrid related steps
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataGridContext extends RawMinkContext implements PageObjectAwareInterface
{
    use SpinCapableTrait;

    /** @var PageFactory */
    protected $pageFactory;

    /** @var Grid */
    public $datagrid;

    /** @var array $gridNames */
    protected $gridNames;

    public function __construct()
    {
        $this->gridNames = [
            'products' => 'product-grid'
        ];
    }

    /**
     * Returns the internal grid name from a human readable label
     *
     * @param string $gridLabel
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function getGridName($gridLabel)
    {
        if (array_key_exists($gridLabel, $this->gridNames)) {
            return $this->gridNames[$gridLabel];
        }

        throw new \InvalidArgumentException(sprintf('No grid found for label %s.', $gridLabel));
    }

    /**
     * @param PageFactory $pageFactory
     */
    public function setPageFactory(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
        $this->datagrid    = $pageFactory->createPage('Base\Grid');
    }

    /**
     * @param int $count
     *
     * @Given /^the grid should contain (\d+) elements?$/
     */
    public function theGridShouldContainElement($count)
    {
        $count = (int) $count;

        if (0 === $count) {
            $this->spin(function () {
                assertTrue($this->datagrid->isGridEmpty());

                return true;
            }, 'Fail to assert that the grid is empty');

            return;
        }

        if ($count > 10) {
            $this->iChangePageSize(100);
        }

        $this->spin(function () use ($count) {
            assertEquals(
                $count,
                $actualCount = $this->datagrid->getToolbarCount()
            );

            return true;
        }, sprintf(
            'Expecting to see %d record(s) in the datagrid toolbar, actually saw %d',
            $count,
            $this->datagrid->getToolbarCount()
        ));

        $this->spin(function () use ($count) {
            assertEquals(
                $count,
                $actualCount = $this->datagrid->countRows()
            );

            return true;
        }, sprintf(
            'Expecting to see %d row(s) in the datagrid, actually saw %d.',
            $count,
            $this->datagrid->getToolbarCount()
        ));
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
        $this->datagrid->filterPerPrice($filterName, $action, $value, $currency);
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
        $this->datagrid->filterPerMetric($filterName, $action, $value, $unit);
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
        $this->datagrid->filterPerPrice($filterName, 'is empty', null, $currency);
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
        $this->datagrid->filterPerNumber($filterName, $action, $value);
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
     * @param string    $code
     * @param TableNode $table
     *
     * @Then /^the row "([^"]*)" should contain the texts:$/
     */
    public function theRowShouldContainText($code, TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->assertColumnContainsText($code, $data['column'], $data['value']);
        }
    }

    /**
     * @param string    $code
     * @param TableNode $table
     *
     * @Then /^the row "([^"]*)" should contain the images:$/
     */
    public function theRowShouldContainImages($code, TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->assertColumnContainsImage($code, $data['column'], $data['title']);
        }
    }

    /**
     * @param string $row
     * @param string $column
     * @param string $expectation
     *
     * @throws ExpectationException
     */
    public function assertColumnContainsText($row, $column, $expectation)
    {
        $column = strtoupper($column);
        $actual = $this->datagrid->getColumnValue($column, $row);

        if (!preg_match('/'.preg_quote($expectation, '/').'/ui', $actual)) {
            throw $this->createExpectationException(
                sprintf('Expecting column "%s" to contain the text "%s", got "%s"', $column, $expectation, $actual)
            );
        }
    }

    /**
     * @param string $row
     * @param string $column
     * @param string $expectation
     *
     * @throws ExpectationException
     */
    public function assertColumnContainsValue($row, $column, $expectation)
    {
        $column = strtoupper($column);
        $actual = $this->datagrid->getColumnValue($column, $row);

        // do not consider the elements' order of "actual" and "expectation"
        $expectation = explode(',', $expectation);
        $expectation = array_map(
            function ($row) {
                return trim($row);
            },
            $expectation
        );
        $actual = explode(',', $actual);
        $actual = array_map(
            function ($row) {
                return trim($row);
            },
            $actual
        );

        $diff = array_diff($actual, $expectation);

        if (!empty($diff)) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting column "%s" to contain "%s", got "%s"',
                    $column,
                    implode(',', $expectation),
                    implode(',', $actual)
                )
            );
        }
    }

    /**
     * @param string $row
     * @param string $column
     * @param string $titleExpectation
     *
     * @throws ExpectationException
     */
    public function assertColumnContainsImage($row, $column, $titleExpectation)
    {
        $node = $this->datagrid->getColumnNode($column, $row);

        if ('**empty**' === $titleExpectation) {
            if (null !== $node->find('css', 'img')) {
                throw $this->createExpectationException(
                    sprintf('Expecting column "%s" to be empty, but one image found.', $column)
                );
            }
        } else {
            $locator = sprintf('img[title="%s"]', $titleExpectation);

            if (null === $node->find('css', $locator)) {
                throw $this->createExpectationException(
                    sprintf(
                        'Expecting column "%s" to contain "%s".',
                        $column,
                        $titleExpectation
                    )
                );
            }
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
                throw $this->createExpectationException(
                    sprintf('Filter "%s" should be visible', $filter)
                );
            }
        }
    }

    /**
     * @Given /^I should( not)? see the available filters (.*)$/
     */
    public function iShouldSeeTheAvailableFilters($not, $filters)
    {
        $available = !(bool)$not;

        $filters = $this->getMainContext()->listToArray($filters);
        foreach ($filters as $filter) {
            if ($available && !$this->datagrid->isFilterAvailable($filter)) {
                throw $this->createExpectationException(
                    sprintf('Filter "%s" should be available.', $filter)
                );
            } elseif (!$available && $this->datagrid->isFilterAvailable($filter)) {
                throw $this->createExpectationException(
                    sprintf('Filter "%s" should not be available.', $filter)
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
            try {
                $filterNode = $this->datagrid->getFilter($filter);
                if ($filterNode->isVisible()) {
                    throw $this->createExpectationException(
                        sprintf('Filter "%s" should not be visible', $filter)
                    );
                }
            } catch (TimeoutException $e) {
                // Filter not rendered, all is good
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
        if (false === strpos(strtolower($filterName), 'category')) {
            $this->datagrid->showFilter($filterName);
            $this->datagrid->assertFilterVisible($filterName);
        }
    }

    /**
     * @param string $filterName
     *
     * @Then /^I hide the filter "([^"]*)"$/
     */
    public function iHideTheFilter($filterName)
    {
        if (false === strpos(strtolower($filterName), 'category')) {
            $this->datagrid->hideFilter($filterName);
        }
    }

    /**
     * @param string $columns
     * @param string $gridLabel
     *
     * @Given /^I display(?: in the (.*) grid)? the columns (.*)$/
     */
    public function iDisplayTheColumns($gridLabel, $columns)
    {
        $gridLabel = (null === $gridLabel || '' === $gridLabel) ? 'products' : $gridLabel;
        $gridName = $this->getGridName($gridLabel);

        $columns = $this->getMainContext()->listToArray($columns);

        $this->getMainContext()->executeScript(
            sprintf('sessionStorage.setItem("%s.columns", "%s");', $gridName, implode(',', $columns))
        );

        $this->getMainContext()->reload();

        $this->wait();
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

        $this->wait('$("table.grid").length > 0');

        $countColumns = $this->datagrid->countColumns();
        if ($expectedColumns !== $countColumns) {
            throw $this->createExpectationException(
                sprintf('Expected %d columns but contains %d', $expectedColumns, $countColumns)
            );
        }

        $expectedPosition = 0;
        foreach ($columns as $column) {
            $position = $this->datagrid->getColumnPosition($column, false, false);
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

        if (!$this->datagrid->isSortedAndOrdered($columnName, $order)) {
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
        $this->datagrid->clickOnAction($element, $action);
        $this->wait();
    }

    /**
     * @param string $not
     * @param string $actionName
     * @param string $element
     *
     * @throws ExpectationException
     *
     * @Given /^I should( not)? be able to view the "([^"]*)" action of the row which contains "([^"]*)"$/
     */
    public function iViewTheActionOfTheRowWhichContains($not, $actionName, $element)
    {
        $action = ucfirst(strtolower($actionName));

        if ($not === $this->datagrid->findAction($element, $action)) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting action "%s" on the row which containe "%s", but none found.',
                    $action,
                    $element
                )
            );
        }
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
        $steps = [
            new Step\Then(sprintf('the rows should be sortable by %s', $columns))
        ];
        $columns = $this->getMainContext()->listToArray($columns);

        foreach ($columns as $column) {
            $steps[] = new Step\Then(sprintf('I sort by "%s" value ascending', $column));
            $steps[] = new Step\Then(sprintf('the rows should be sorted ascending by %s', $column));
            $steps[] = new Step\Then(sprintf('I sort by "%s" value descending', $column));
            $steps[] = new Step\Then(sprintf('the rows should be sorted descending by %s', $column));
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
        $steps = [];

        foreach ($table->getHash() as $item) {
            $count  = count($this->getMainContext()->listToArray($item['result']));
            $filter = $item['filter'];

            $steps[] = new Step\Then(sprintf('I show the filter "%s"', $filter));
            $steps[] = new Step\Then(sprintf('I filter by "%s" with value "%s"', $filter, $item['value']));
            $steps[] = new Step\Then(sprintf('the grid should contain %d elements', $count));
            $steps[] = new Step\Then(sprintf('I should see entities %s', $item['result']));
            $steps[] = new Step\Then(sprintf('I hide the filter "%s"', $filter));
        }

        return $steps;
    }

    /**
     * @param string $size
     *
     * @When /^I change (?:the) page size to (.*)$/
     */
    public function iChangePageSize($size)
    {
        $this->datagrid->changePageSize((int) $size);
        $this->wait();
    }

    /**
     * @param int $size
     *
     * @When /^page size should be (\d+)$/
     */
    public function pageSizeShouldBe($size)
    {
        $this->datagrid->pageSizeIs((int) $size);
        $this->wait();
    }

    /**
     * @param string $columnName
     * @param string $order
     *
     * @When /^I sort by "(.*)" value (ascending|descending)$/
     */
    public function iSortByValue($columnName, $order = 'ascending')
    {
        $this->datagrid->sortBy($columnName, $order);

        $loadlingMask = $this->datagrid
            ->getElement('Grid container')
            ->find('css', '.loading-mask .loading-mask');

        $this->spin(function () use ($loadlingMask) {
            return !$loadlingMask->isVisible();
        });
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
                $this->datagrid->getColumnSorter($columnName);
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
            $this->iChangePageSize(100);
        }

        foreach ($elements as $element) {
            if (!$this->datagrid->getRow($element)) {
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
        if ($this->datagrid->isGridEmpty()) {
            return;
        }

        foreach ($this->getMainContext()->listToArray($entities) as $entity) {
            if ($this->datagrid->hasRow($entity)) {
                throw $this->createExpectationException(
                    sprintf('Entity "%s" should not be seen', $entity)
                );
            }
        }
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
                'contains'         => Grid::FILTER_CONTAINS,
                'does not contain' => Grid::FILTER_DOES_NOT_CONTAIN,
                'is equal to'      => Grid::FILTER_IS_EQUAL_TO,
                'starts with'      => Grid::FILTER_STARTS_WITH,
                'ends with'        => Grid::FILTER_ENDS_WITH,
                'empty'            => Grid::FILTER_IS_EMPTY,
                'in list'          => Grid::FILTER_IN_LIST,
            ];

            $operator = $operators[$operator];
        }

        $this->datagrid->filterBy($filterName, $value, $operator, $this->getSession()->getDriver());
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
        $rows = $this->getMainContext()->listToArray($rows);

        foreach ($rows as $row) {
            $gridRow  = $this->datagrid->getRow($row);
            $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');

            if (!$checkbox) {
                throw $this->createExpectationException(sprintf('Unable to find a checkbox for row %s', $row));
            }

            $checkbox->check();
        }
    }

    /**
     * @param string $rows
     *
     * @throws ExpectationException
     *
     * @When /^I uncheck the rows? "([^"]+)"$/
     */
    public function iUncheckTheRows($rows)
    {
        $rows = $this->getMainContext()->listToArray($rows);

        foreach ($rows as $row) {
            $gridRow  = $this->datagrid->getRow($row);
            $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');

            if (!$checkbox) {
                throw $this->createExpectationException(sprintf('Unable to find a checkbox for row %s', $row));
            }

            $checkbox->uncheck();
        }
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
            $gridRow  = $this->datagrid->getRow($row);
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
     * @param string $rows
     *
     * @throws ExpectationException
     *
     * @Then /^the rows? "([^"]*)" should not be checked$/
     */
    public function theRowShouldBeUnchecked($rows)
    {
        //To rework on 1.4
        $rows = $this->getMainContext()->listToArray($rows);

        foreach ($rows as $row) {
            $gridRow = $this->datagrid->getRow($row);
            $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');

            if (!$checkbox) {
                throw $this->createExpectationException(sprintf('Unable to find a checkbox for row %s', $row));
            }

            if ($checkbox->isChecked()) {
                throw $this->createExpectationException(sprintf('Expecting row %s to be checked', $row));
            }
        }
    }

    /**
     * @Then /^I reset the grid$/
     */
    public function iResetTheGrid()
    {
        $this->datagrid->clickOnResetButton();
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
     * @Then /^I click back to grid$/
     */
    public function iClickBackToGrid()
    {
        $this->spin(function () {
            $this->getSession()->getPage()->clickLink('Back to grid');

            return true;
        });
    }

    /**
     * @param string $column
     *
     * @When /^I hide the "([^"]*)" column$/
     */
    public function iHideTheColumn($column)
    {
        $this->datagrid->openColumnsPopin();
        $this->wait();
        $this->datagrid->hideColumn($column);
        $this->wait();
    }

    /**
     * @param string $source
     * @param string $target
     *
     * @When /^I put the "([^"]*)" column before the "([^"]*)" one$/
     */
    public function iPutTheColumnBeforeTheOne($source, $target)
    {
        $this->datagrid->openColumnsPopin();
        $this->wait();
        $this->datagrid->moveColumn($source, $target);
        $this->wait();
    }

    /**
     * @param string $entities
     *
     * @return Then[]
     *
     * @When /^I mass-edit (?:products?|families) (.*)$/
     */
    public function iMassEditEntities($entities)
    {
        return [
            new Step\Then('I change the page size to 100'),
            new Step\Then(sprintf('I select rows %s', $entities)),
            new Step\Then('I press mass-edit button')
        ];
    }

    /**
     * @When /^I press mass-edit button$/
     */
    public function iPressMassEditButton()
    {
        $this->getCurrentPage()->massEdit();
        $this->wait();
    }

    /**
     * @param string $entities
     *
     * @Then /^I select rows? (.*)$/
     */
    public function iSelectRows($entities)
    {
        foreach ($this->getMainContext()->listToArray($entities) as $entity) {
            $this->getCurrentPage()->selectRow($entity, true);
        }
    }

    /**
     * @param string $entities
     *
     * @Then /^I unselect rows? (.*)$/
     */
    public function iUnSelectRows($entities)
    {
        foreach ($this->getMainContext()->listToArray($entities) as $entity) {
            $this->getCurrentPage()->selectRow($entity, false);
        }
    }

    /**
     * @Then /^I select all visible products$/
     */
    public function iSelectAllVisible()
    {
        $this->getCurrentPage()->selectAllVisible();
    }

    /**
     * @Then /^I select none product$/
     */
    public function iSelectNone()
    {
        $this->getCurrentPage()->selectNone();
    }

    /**
     * @Then /^I select all products$/
     */
    public function iSelectAll()
    {
        $this->getCurrentPage()->selectAll();
    }

    /**
     * @param string $entities
     *
     * @return Then[]
     *
     * @When /^I mass-delete products? (.*)$/
     */
    public function iMassDelete($entities)
    {
        return [
            new Step\Then('I change the page size to 100'),
            new Step\Then(sprintf('I select rows %s', $entities)),
            new Step\Then('I press mass-delete button')
        ];
    }

    /**
     * @When /^I press mass-delete button$/
     */
    public function iPressMassDeleteButton()
    {
        $this->getCurrentPage()->massDelete();
        $this->wait();
    }

    /**
     * @When /^I press sequential-edit button$/
     */
    public function iPressSequentialEditButton()
    {
        $this->getCurrentPage()->sequentialEdit();
        $this->wait();
        $this->getNavigationContext()->currentPage = 'Product edit';
    }

    /**
     * @param string $viewLabel
     *
     * @When /^I apply the "([^"]*)" view$/
     */
    public function iApplyTheView($viewLabel)
    {
        $this->datagrid->applyView($viewLabel);
        $this->wait();
    }

    /**
     * @When /^I delete the view$/
     */
    public function iDeleteTheView()
    {
        $this->getCurrentPage()->find('css', '#remove-view')->click();
        $this->wait();
    }

    /**
     * @param TableNode $table
     *
     * @return Then[]
     * @When /^I create the view:$/
     */
    public function iCreateTheView(TableNode $table)
    {
        $this->getCurrentPage()->find('css', '#create-view')->click();

        return [
            new Step\Then('I fill in the following information in the popin:', $table),
            new Step\Then('I press the "OK" button')
        ];
    }

    /**
     * @When /^I update the view$/
     */
    public function iUpdateTheView()
    {
        $this->getCurrentPage()->find('css', '#update-view')->click();
        $this->wait();
    }

    /**
     * @param string $not
     * @param string $viewLabel
     *
     * @Then /^I should( not)? see the "([^"]*)" view$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheView($not, $viewLabel)
    {
        $view = $this->datagrid->findView($viewLabel);

        if (('' !== $not && null !== $view) || ('' === $not && null === $view)) {
            throw $this->createExpectationException(
                sprintf(
                    'View "%s" should%s be available.',
                    $viewLabel,
                    $not
                )
            );
        }
    }

    /**
     * Create an expectation exception
     *
     * @param string $message
     *
     * @return ExpectationException
     */
    protected function createExpectationException($message)
    {
        return $this->getMainContext()->createExpectationException($message);
    }

    /**
     * Wait
     *
     * @param int    $time
     * @param string $condition
     */
    protected function wait($condition = null)
    {
        $this->getMainContext()->wait($condition);
    }

    /**
     * @return \Behat\Behat\Context\ExtendedContextInterface
     */
    protected function getNavigationContext()
    {
        return $this->getMainContext()->getSubcontext('navigation');
    }

    /**
     * @return \Behat\Behat\Context\ExtendedContextInterface
     */
    protected function getFixturesContext()
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

    /**
     * @param string $filterName
     * @param mixed  $values
     * @param string $operator
     *
     * @throws \InvalidArgumentException
     */
    protected function filterByDate($filterName, $values, $operator)
    {
        if (!is_array($values)) {
            $values = [$values, $values];
        }

        $filter = $this->datagrid->getFilter($filterName);

        $this->datagrid->openFilter($filter);

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
