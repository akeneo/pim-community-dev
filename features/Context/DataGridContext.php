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
            'products'           => 'product-grid',
            'published products' => 'published-product-grid'
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
     * @return mixed
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
            }, 'Expecting grid to be empty');

            return;
        }

        $this->theGridToolbarCountShouldBe($count);

        if ($count > 10) {
            $this->getCurrentPage()->getCurrentGrid()->setPageSize(100);
        }

        $this->spin(function () use ($count) {
            assertEquals(
                $count,
                $actualCount = $this->datagrid->countRows()
            );

            return true;
        }, sprintf(
            'Expecting to see %d row(s) in the datagrid, actually saw %d.',
            $count,
            $this->datagrid->countRows()
        ));
    }

    /**
     * @param int $count
     *
     * @throws TimeoutException
     *
     * @Then /^the grid toolbar count should be (\d+) elements?$/
     */
    public function theGridToolbarCountShouldBe($count)
    {
        $count = (int) $count;
        $this->spin(function () use ($count) {
            return $this->datagrid->getToolbarCount() === $count;
        }, sprintf(
            'Expecting to see %d record(s) in the datagrid toolbar, actually saw %d',
            $count,
            $this->datagrid->getToolbarCount()
        ));
    }

    /**
     * @param string $filterName
     * @param string $value
     *
     * @Then /^the filter "([^"]*)" should be set to operator "([^"]*)" and value "([^"]*)"$/
     */
    public function theFilterShouldBeSetTo($filterName, $operator, $value)
    {
        $filter = $this->datagrid->getFilter($filterName);
        $this->spin(function () use ($filter, $value) {
            return $filter->find('css', sprintf('.filter-criteria-hint:contains("%s")', $value));
        }, sprintf('Filter "%s" should be set to "%s".', $filterName, $value));
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
        $column = mb_strtoupper($column);
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
     * @throws ExpectationException
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
     * @throws ExpectationException
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
        $this->datagrid->showFilter($filterName);
    }

    /**
     * @param string $filterName
     *
     * @Then /^I hide the filter "([^"]*)"$/
     * @Then /^I collapse the "([^"]*)" sidebar$/
     */
    public function iHideTheFilter($filterName)
    {
        $this->datagrid->hideFilter($filterName);
    }

    /**
     * @param string $filterName
     *
     * @Then /^I expand the "([^"]*)" sidebar$/
     */
    public function iExpandTheCategoriesSidebar($filterName)
    {
        $this->datagrid->expandFilter($filterName);
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
     * @throws ExpectationException
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

        $this->spin(function () use ($columnName, $order) {
            return $this->datagrid->isSortedAndOrdered($columnName, $order);
        }, sprintf('The rows are not sorted %s by column %s', $order, $columnName));
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
            $count = null;
            if (isset($item['result']) && '' !== $item['result']) {
                $count = count($this->getMainContext()->listToArray($item['result']));
            }
            $filter = $item['filter'];
            $isCategoryFilter = false !== strpos(strtolower($filter), 'category');

            if (!$isCategoryFilter) {
                $steps[] = new Step\Then(sprintf('I show the filter "%s"', $filter));
            }
            $steps[] = new Step\Then(sprintf(
                'I filter by "%s" with operator "%s" and value "%s"',
                $filter,
                $item['operator'],
                $item['value']
            ));

            if (null !== $count) {
                $steps[] = new Step\Then(sprintf('the grid should contain %d elements', $count));
                $steps[] = new Step\Then(sprintf('I should see entities %s', $item['result']));
            }
            if (!$isCategoryFilter) {
                $steps[] = new Step\Then(sprintf('I hide the filter "%s"', $filter));
            }
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
        $this->datagrid->sortBy($columnName, $order);

        $loadingMask = $this->datagrid
            ->getElement('Grid container')
            ->find('css', '.hash-loading-mask .loading-mask');

        $this->spin(function () use ($loadingMask) {
            return (null === $loadingMask) || !$loadingMask->isVisible();
        }, '".loading-mask" is still visible');
    }

    /**
     * @param string $columns
     *
     * @throws ExpectationException
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
     * @return Step
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
            $this->getCurrentPage()->getCurrentGrid()->setPageSize(100);
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
     * @throws ExpectationException
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
        $entitiesArray = $this->getMainContext()->listToArray($entities);

        $this->spin(function () use ($entitiesArray) {
            if ($this->datagrid->isGridEmpty()) {
                return true;
            }

            foreach ($entitiesArray as $entity) {
                if ($this->datagrid->hasRow($entity)) {
                    return false;
                }
            }

            return true;
        }, sprintf('Expected not to see "%s"', $entities));
    }

    /**
     * @param string $filterName
     * @param string $operator
     * @param string $value
     *
     * @Then /^I filter by "(.*)" with operator "(.*)" and value "(.*)"$/
     */
    public function iFilterBy($filterName, $operator, $value)
    {
        $this->datagrid->filterBy($filterName, $operator, $value);
        $this->wait();
    }

    /**
     * @Then /^I should( not)? see the input filter for "([^"]*)"$/
     *
     * @param boolean $not
     * @param string  $filterName
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheInputFilterFor($not, $filterName)
    {
        $filter = $this->datagrid->getFilter($filterName);
        $inputVisible = $filter->isInputValueVisible();

        if (('' !== $not && false !== $inputVisible) || ('' === $not && false === $inputVisible)) {
            throw $this->createExpectationException(
                sprintf(
                    'Input for filter "%s" should%s be visible.',
                    $filterName,
                    $not
                )
            );
        }
    }

    /**
     * @param string $optionNames
     * @param string $filterName
     *
     * @throws ExpectationException
     *
     * @Then /^I should see options? "([^"]*)" in filter "([^"]*)"$/
     */
    public function iShouldSeeOptionInFilter($optionNames, $filterName)
    {
        $optionNames = $this->getMainContext()->listToArray($optionNames);

        $this->datagrid->checkOptionInFilter($optionNames, $filterName);
    }

    /**
     * @param string $row
     *
     * @When /^I click on the "([^"]*)" row$/
     */
    public function iClickOnTheRow($row)
    {
        $this->spin(function () use ($row) {
            $row = $this->datagrid->getRow($row);
            if (null === $row) {
                return false;
            }

            $row->click();

            return true;
        }, sprintf('Row with "%s", not found.', $row));
    }

    /**
     * @param string $rows
     *
     * @When /^I check the rows? "([^"]*)"$/
     */
    public function iCheckTheRows($rows)
    {
        $rows = $this->getMainContext()->listToArray($rows);

        foreach ($rows as $row) {
            $this->spin(function () use ($row) {
                $gridRow  = $this->datagrid->getRow($row);
                $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');

                if (null !== $checkbox) {
                    $checkbox->check();
                }

                if ($checkbox->isChecked()) {
                    return true;
                }

                return false;
            }, sprintf('Unable to check the row "%s"', $row));
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
            $this->spin(function () use ($row) {
                $gridRow  = $this->datagrid->getRow($row);
                $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');

                if (null !== $checkbox) {
                    $checkbox->uncheck();
                }

                if (!$checkbox->isChecked()) {
                    return true;
                }

                return false;

            }, sprintf('Unable to uncheck the row "%s"', $row));
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
            $this->spin(function () use ($row) {
                $gridRow  = $this->datagrid->getRow($row);
                $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');

                if (!$checkbox) {
                    return false;
                }

                return $checkbox->isChecked();
            }, sprintf('Fail asserting that "%s" row was checked', $row));
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
        $rows = $this->getMainContext()->listToArray($rows);

        foreach ($rows as $row) {
            $this->spin(function () use ($row) {
                $gridRow  = $this->datagrid->getRow($row);
                $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');

                if (!$checkbox) {
                    return false;
                }

                return !$checkbox->isChecked();
            }, sprintf('Fail asserting that "%s" row was unchecked', $row));
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
    public function iRefreshTheGrid()
    {
        $this->spin(function () {
            $this->getSession()->getPage()->clickLink('Refresh');

            return true;
        }, 'Cannot find the button "Refresh"');
    }

    /**
     * @Then /^I click back to grid$/
     */
    public function iClickBackToGrid()
    {
        $this->spin(function () {
            $this->getSession()->getPage()->clickLink('Back to grid');

            return true;
        }, 'Cannot find the button "Back to grid"');
    }

    /**
     * @Then /^I click on import profile$/
     */
    public function iClickOnImportProfile()
    {
        $collectLink = $this->spin(function () {
            return $this->getSession()->getPage()->findLink('Collect');
        }, 'Cannot find the button "Collect"');
        $collectLink->click();

        $importProfileLink = $this->spin(function () {
            return $this->getSession()->getPage()->findLink('Import profiles');
        }, 'Cannot find the button "Import profiles"');
        $importProfileLink->click();
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
     * @Then /^I select all visible entities$/
     */
    public function iSelectAllVisible()
    {
        $this->getCurrentPage()->selectAllVisible();
    }

    /**
     * @Then /^I select none entity$/
     */
    public function iSelectNone()
    {
        $this->getCurrentPage()->selectNone();
    }

    /**
     * @Then /^I select all entities$/
     */
    public function iSelectAll()
    {
        $this->getCurrentPage()->selectAll();
    }

    /**
     * @When /^I press sequential-edit button$/
     */
    public function iPressSequentialEditButton()
    {
        $this
            ->getCurrentPage()
            ->getDropdownButtonItem('Sequential Edit', 'Bulk Actions')
            ->click();

        $this->getNavigationContext()->currentPage = 'Product edit';
    }

    /**
     * @param string $viewLabel
     *
     * @When /^I apply the "([^"]*)" view$/
     */
    public function iApplyTheView($viewLabel)
    {
        $this->getCurrentPage()->getViewSelector()->setValue($viewLabel);
    }

    /**
     * @When /^I delete the view "([^"]*)"$/
     */
    public function iDeleteTheView($viewLabel)
    {
        $this->getCurrentPage()->removeView($viewLabel);
    }

    /**
     * @param TableNode $table
     *
     * @return Then[]
     *
     * @When /^I create the view:$/
     */
    public function iCreateTheView(TableNode $table)
    {
        $this->getCurrentPage()->getViewSelector()->click();

        return [
            new Step\Then('I press the "Create view" button'),
            new Step\Then('I fill in the following information in the popin:', $table),
            new Step\Then('I press the "OK" button')
        ];
    }

    /**
     * @When /^I update the view$/
     */
    public function iUpdateTheView()
    {
        $this->getCurrentPage()->saveView();
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
        $availableViews = $this->getCurrentPage()->getAvailableViews();

        if (
                ('' !== $not && in_array($viewLabel, $availableViews)) ||
                ('' === $not && !in_array($viewLabel, $availableViews))
        ) {
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
}
