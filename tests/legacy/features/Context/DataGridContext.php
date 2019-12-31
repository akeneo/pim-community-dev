<?php

namespace Context;

use Behat\ChainedStepsExtension\Step;
use Behat\ChainedStepsExtension\Step\Then;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Context\Page\Base\Grid;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAware;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory as PageObjectFactory;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Feature context for the datagrid related steps
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataGridContext extends PimContext implements PageObjectAware
{
    use SpinCapableTrait;

    /** @var PageObjectFactory\ */
    protected $pageFactory;

    /** @var Grid */
    public $datagrid;

    /** @var array $gridNames */
    protected $gridNames = [
        'products'           => 'product-grid',
        'published products' => 'published-product-grid'
    ];

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
     * {@inheritdoc}
     */
    public function setPageObjectFactory(PageObjectFactory $pageObjectFactory)
    {
        $this->pageFactory = $pageObjectFactory;
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
                Assert::assertTrue($this->getDatagrid()->isGridEmpty());

                return true;
            }, 'Expecting grid to be empty');

            return;
        }

        $this->spin(function () use ($count) {
            Assert::assertEquals(
                $count,
                $actualCount = $this->getDatagrid()->countRows()
            );

            return true;
        }, sprintf(
            'Expecting to see %d row(s) in the datagrid, actually saw %d.',
            $count,
            $this->getDatagrid()->countRows()
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
        $filter = $this->getDatagrid()->getFilter($filterName);
        $this->spin(function () use ($filter, $value) {
            return $filter->find('css', sprintf('.filter-criteria-hint:contains("%s")', $value));
        }, sprintf('Filter "%s" should be set to "%s".', $filterName, $value));
    }

    /**
     * @param string $text
     *
     * @Then /^I type "([^"]*)" in the manage filter input$/
     */
    public function iTypeInTheManageFilterInput($text)
    {
        $gridContainer = $this->getElementFromDatagrid('Grid container');
        $loadingMask = $gridContainer->find('css', '.loading-mask .loading-mask');

        $this->spin(function () use ($loadingMask) {
            return (null === $loadingMask) || !$loadingMask->isVisible();
        }, 'Loading mask is still visible');

        $this->spin(function () use ($text) {
            $this->getDatagrid()->typeInManageFilterInput($text);

            return true;
        }, sprintf('Cannot find the filter "%s" in the filter list', $text));
    }


    /**
     * @param string $title
     *
     * @throws ExpectationException
     *
     * @Then /^I could see "([^"]*)" in the manage filters list$/
     */
    public function iCouldSeeInTheManageFiltersList($title)
    {
        $filterElement = $this->spin(function () use ($title) {
            $manageFilterElement = $this->getElementFromDatagrid('Manage filters');
            return $manageFilterElement->find('css', sprintf('label:contains(%s)', $title));
        }, sprintf('Cannot find the filter "%s" in the filter list', $title));

        if ($filterElement == null || !$filterElement->isVisible()) {
            throw $this->createExpectationException(
                sprintf('Expecting "%s" to be displayed in the filters list', $title)
            );
        }
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
            $this->spin(function () use ($code, $data) {
                $this->assertColumnContainsValue($code, $data['column'], $data['value']);

                return true;
            }, sprintf(
                'Expecting column "%s" to contain "%s" on row "%s", found "%s"',
                $data['column'],
                $data['value'],
                $code,
                $this->getDatagrid()->getColumnValue($data['column'], $code)
            ));
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
        $actual = $this->getDatagrid()->getColumnValue($column, $row);

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
        $actual = $this->getDatagrid()->getColumnValue($column, $row);

        // do not consider the elements' order of "actual" and "expectation"
        $expectation = explode(',', $expectation);
        $expectation = array_map(
            function ($row) {
                return strtolower(trim($row));
            },
            $expectation
        );
        $actual = explode(',', $actual);
        $actual = array_map(
            function ($row) {
                return strtolower(trim($row));
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
        $node = $this->getDatagrid()->getColumnNode($column, $row);

        if ('**empty**' === $titleExpectation) {
            $thumbnailPath = '/media/show/undefined/thumbnail_small';
            $undefinedThumbnail = $node->find('css', 'img');

            if (null === $undefinedThumbnail || $thumbnailPath !== $undefinedThumbnail->getAttribute('src')) {
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
            $filterNode = $this->getDatagrid()->getFilter($filter);
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
        $available = !$not;

        $filters = $this->getMainContext()->listToArray($filters);
        foreach ($filters as $filter) {
            if ($available && !$this->getDatagrid()->isFilterAvailable($filter)) {
                throw $this->createExpectationException(
                    sprintf('Filter "%s" should be available.', $filter)
                );
            } elseif (!$available && $this->getDatagrid()->isFilterAvailable($filter)) {
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
        foreach ($filters as $filterName) {
            $this->spin(function () use ($filterName) {
                $filterNode = $this
                    ->getDatagrid()
                    ->getElement('Body')
                    ->find('css', sprintf('.filter-item[data-name="%s"]', $filterName));

                return null === $filterNode || !$filterNode->isVisible();
            }, sprintf('Filter "%s" should not be visible', $filterName));
        }
    }

    /**
     * @param string $filterName
     *
     * @Then /^I show the filter "([^"]*)"$/
     */
    public function iShowTheFilter($filterName)
    {
        $this->getDatagrid()->showFilter($filterName);
        $this->wait();
    }

    /**
     * @param string $filterName
     *
     * @Then /^I hide the filter "([^"]*)"$/
     */
    public function iHideTheFilter($filterName)
    {
        $loadingMask = $this->getCurrentPage()->find('css', '.loading-mask .loading-mask');

        $this->spin(function () use ($loadingMask) {
            return (null === $loadingMask) || !$loadingMask->isVisible();
        }, 'Loading mask is still visible');

        $this->getDatagrid()->hideFilter($filterName);
    }

    /**
     * @Then /^I should see available filters "([^"]*)"$/
     */
    public function iShouldSeeAvailableFilters($filters)
    {
        $arrayFilters = explode(',', $filters);
        $existingFiltersArray = array_map(function ($filter) {
            return strtolower(trim($filter->getHtml()));
        }, $this->getCurrentPage()->getFiltersList());

        foreach ($arrayFilters as $filter) {
            if (array_search($filter, $existingFiltersArray) === false) {
                throw $this->createExpectationException(
                    sprintf('Expected to see filter %s as available', $filter)
                );
            }
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
        $currentColumns = $this->getDatagrid()->getCurrentColumnLabels();
        $expectedColumns = $this->getMainContext()->listToArray($columns);

        $currentColumns = array_map('strtolower', $currentColumns);
        $expectedColumns = array_map('strtolower', $expectedColumns);

        $columnsToAdd = array_diff($expectedColumns, $currentColumns);
        $columnsToRemove = array_diff($currentColumns, $expectedColumns);

        // Temporary solution waiting for Category tree moving (PIM-6574)
        $this->getSession()->executeScript('$(".AknDefault-mainContent").scrollLeft(1000)');

        $this->getDatagrid()->openColumnsPopin();
        $this->getDatagrid()->addColumns($columnsToAdd);
        $this->getDatagrid()->removeColumns($columnsToRemove);
        $this->getDatagrid()->validateColumnsPopin();

        $this->getSession()->executeScript('$(".AknDefault-mainContent").scrollLeft(0)');
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

        $countColumns = $this->getDatagrid()->countColumns();
        if ($expectedColumns !== $countColumns) {
            throw $this->createExpectationException(
                sprintf('Expected %d columns but contains %d', $expectedColumns, $countColumns)
            );
        }

        $expectedPosition = 0;
        foreach ($columns as $column) {
            $position = $this->getDatagrid()->getColumnPosition($column, false, false);
            if ($expectedPosition++ !== $position) {
                throw $this->createExpectationException(
                    sprintf(
                        'Column "%s" was expected in position %d, but was at %d',
                        $column,
                        ($expectedPosition - 1),
                        $position
                    )
                );
            }
        }
    }

    /**
     * @param string $order
     * @param string $columnName
     * @param bool   $naturally If TRUE, empty values are taken in account when sorting
     *
     * @throws ExpectationException
     *
     * @Then /^the rows should be( naturally)? sorted (ascending|descending) by (.*)$/
     */
    public function theRowsShouldBeSortedBy($naturally, $order, $columnName)
    {
        $columnName = strtoupper($columnName);

        $this->spin(function () use ($naturally, $columnName, $order) {
            return $this->getDatagrid()->isSortedAndOrdered($columnName, $order, $naturally);
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
        //Wait for the JS action to be linked to the button because it will do the default action of the row otherwise
        $this->getSession()->wait(6000, false);
        $action = ucfirst(strtolower($actionName));
        $this->getDatagrid()->clickOnAction($element, $action);
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

        if ($not === $this->getDatagrid()->findAction($element, $action)) {
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
     * @param bool   $naturally If TRUE, empty values are taken in account when sorting
     * @param string $columns
     *
     * @Then /^I should be able to( naturally)? sort the rows by (.*)$/
     *
     * @return Then[]
     */
    public function iShouldBeAbleToSortTheRowsBy($naturally, $columns)
    {
        $steps = [
            new Step\Then(sprintf('the rows should be sortable by %s', $columns))
        ];
        $columns = $this->getMainContext()->listToArray($columns);

        foreach ($columns as $column) {
            $steps[] = new Step\Then(sprintf('I sort by "%s" value ascending', $column));
            $steps[] = new Step\Then(sprintf(
                'the rows should be%s sorted ascending by %s',
                $naturally ? ' naturally' : '',
                $column
            ));
            $steps[] = new Step\Then(sprintf('I sort by "%s" value descending', $column));
            $steps[] = new Step\Then(sprintf(
                'the rows should be%s sorted descending by %s',
                $naturally ? ' naturally' : '',
                $column
            ));
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
     * @param TableNode $table
     *
     * @Then /^I filter with the following filters:$/
     *
     * @return Then[]
     */
    public function iFilterWithTheFollowingFilters(TableNode $table)
    {
        $this->spin(
            function () {
                $loadingWrapper = $this->getDatagrid()->getElement('Grid container')->find('css', '.loading-mask');
                $filterBox = $this->getDatagrid()->getElement('Body')->find('css', '.filter-box, .filter-wrapper');
                $manageFilters = $this->getDatagrid()->getElement('Manage filters');

                return
                    (null === $loadingWrapper || !$loadingWrapper->isVisible())
                    && null !== $filterBox
                    && null !== $manageFilters
                ;
            }, 'Loading mask is still visible');

        $this->spin(
            function () {
                $filterList = $this->getDatagrid()->getElement('Body')->find('css', '.AknFilterBox-addFilterButton');
                if (null === $filterList) {
                    return false;
                }
                $filterList->click();

                return true;
            },
            'Could not open Manage filters'
        );

        $manageFilters = $this->getDatagrid()->getElement('Manage filters');
        foreach ($table->getHash() as $item) {
            ['filter' => $filterName, 'operator' => $operator, 'value' => $value] = $item;

            $this->spin(
                function () use ($filterName, $manageFilters) {
                    $searchField = $manageFilters->find('css', 'input[type="search"]');
                    if (null !== $searchField) {
                        $searchField->setValue($filterName);

                        return true;
                    }

                    return false;
                },
                'Impossible to search in filters.'
            );

            $this->spin(
                function () use ($filterName, $manageFilters) {
                    $filterElement = $manageFilters->find('css', sprintf('input[value="%s"]', $filterName));

                    if (null !== $filterElement && $filterElement->isVisible()) {
                        $filterElement->click();

                        return true;
                    }

                    return false;
                },
                sprintf('Impossible to activate filter "%s"', $filterName)
            );
        }
        $manageFilters->find('css', '.close')->click();

        foreach ($table->getHash() as $item) {
            ['filter' => $filterName, 'operator' => $operator, 'value' => $value] = $item;

            $this->spin(function () use ($filterName, $operator, $value) {
                $this->getDatagrid()->filterBy($filterName, $operator, $value);

                return true;
            }, sprintf('Can\'t filter by %s with operator %s and value %s', $filterName, $operator, $value));
        }

        $this->spin(
            function () {
                $loadingWrapper = $this->getDatagrid()->getElement('Grid container')->find('css', '.loading-mask');

                return (null === $loadingWrapper || !$loadingWrapper->isVisible());
            }, 'Loading mask is still visible');


        return true;
    }

    /**
     * @param string $columnName
     * @param string $order
     *
     * @When /^I sort by "(.*)" value (ascending|descending)$/
     */
    public function iSortByValue($columnName, $order = 'ascending')
    {
        $this->spin(function () use ($columnName, $order) {
            $this->getDatagrid()->sortBy($columnName, $order);

            return true;
        }, sprintf('Cannot sort by %s %s', $columnName, $order));

        $gridContainer = $this->getElementFromDatagrid('Grid container');
        $loadingMask = $gridContainer->find('css', '.loading-mask .loading-mask');

        $this->spin(function () use ($loadingMask) {
            return (null === $loadingMask) || !$loadingMask->isVisible();
        }, 'Loading mask is still visible');
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
                $this->getDatagrid()->getColumnSorter($columnName);
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
     * @Then /^I should see the product models? (.*)$/
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
     * @Then /^I should see client(?:|s) (.*)$/
     */
    public function iShouldSeeEntities($elements)
    {
        $elements = $this->getMainContext()->listToArray($elements);

        foreach ($elements as $element) {
            if (!$this->getDatagrid()->getRow($element)) {
                throw $this->createExpectationException(sprintf('Entity "%s" not found', $element));
            }
        }
    }

    /**
     * @param array $entities
     *
     * @throws ExpectationException
     *
     * @Then /^I should not see the product models? (.*)$/
     * @Then /^I should not see products? (.*)$/
     * @Then /^I should not see attributes? (?!(.*)in group )(.*)$/
     * @Then /^I should not see channels? (.*)$/
     * @Then /^I should not see locales? (.*)$/
     * @Then /^I should not see (?:import|export) profiles? (.*)$/
     * @Then /^I should not see (?:(?:entit|currenc)(?:y|ies)) (.*)$/
     * @Then /^I should not see group(?: type)?s? (.*)$/
     * @Then /^I should not see association (?:types? )?(.*)$/
     * @Then /^I should not see users? (.*)$/
     * @Then /^I should not see famil(?:y|ies) (.*)$/
     * @Then /^I should not see client(?:|s) (.*)$/
     */
    public function iShouldNotSeeEntities($entities)
    {
        $entitiesArray = $this->getMainContext()->listToArray($entities);

        $this->spin(function () use ($entitiesArray) {
            if ($this->getDatagrid()->isGridEmpty()) {
                return true;
            }

            foreach ($entitiesArray as $entity) {
                if ($this->getDatagrid()->hasRow($entity)) {
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
        $this->spin(function () use ($filterName, $operator, $value) {
            $this->getDatagrid()->filterBy($filterName, $operator, $value);

            return true;
        }, sprintf('Can\'t filter by %s with operator %s and value %s', $filterName, $operator, $value));


        $gridContainer = $this->getElementFromDatagrid('Grid container');

        $loadingMask = $gridContainer->find('css', '.loading-mask .loading-mask');

        $this->spin(function () use ($loadingMask) {
            return (null === $loadingMask) || !$loadingMask->isVisible();
        }, 'Loading mask is still visible');
    }

    /**
     * @param string $value
     *
     * @When /^I search "(.*)"$/
     */
    public function iSearch($value)
    {
        $this->getDatagrid()->search($value);
    }

    /**
     * @param string $filterName
     *
     * @Then /^I open the "(.*)" filter$/
     */
    public function iOpenFilter($filterName)
    {
        $this->getDatagrid()->openFilter($filterName);
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
        $filter = $this->getDatagrid()->getFilter($filterName);
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

        $this->getDatagrid()->checkOptionInFilter($optionNames, $filterName);
    }

    /**
     * @param boolean $not
     * @param string $option
     * @param string $filterName
     *
     * @throws ExpectationException
     *
     * @throws ExpectationException
     *
     * @Given /^I should( not)? see the available option "([^"]*)" in the filter "([^"]*)"$/
     */
    public function iShouldNotSeeTheAvailableOptionInTheFilter($not, $option, $filterName)
    {
        $filter = $this->getDatagrid()->getFilter($filterName);
        $filter->open();

        if ($not && in_array($option, $filter->getAvailableValues())) {
            throw $this->createExpectationException(
                sprintf('Option "%s" should not be available for the filter "%s"', $option, $filterName)
            );
        }

        if (!$not && !in_array($option, $filter->getAvailableValues())) {
            throw $this->createExpectationException(
                sprintf('Option "%s" should be available for the filter "%s"', $option, $filterName)
            );
        }

        $filter->close();
    }

    /**
     * @param string $row
     *
     * @When /^I click on the "([^"]*)" row$/
     */
    public function iClickOnTheRow($row)
    {
        $this->spin(function () use ($row) {
            $row = $this->getDatagrid()->getRow($row);
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
        $this->getMainContext()->executeScript(
            '$(".AknGrid-bodyCell img").css({height:\'10px\'})'
        );

        foreach ($rows as $row) {
            $this->spin(function () use ($row) {
                $gridRow  = $this->getDatagrid()->getRow($row);
                $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');


                if (null !== $checkbox && $checkbox->isVisible()) {
                    //$this->getSession()->moveto(array('element' => $checkbox->getID()));
                    $checkbox->check();

                    if ($checkbox->isChecked()) {
                        return true;
                    }
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
                $gridRow  = $this->getDatagrid()->getRow($row);
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
     * @When /^I remove the rows? "([^"]+)"$/
     */
    public function iRemoveTheRows($rows)
    {
        $rows = $this->getMainContext()->listToArray($rows);

        foreach ($rows as $row) {
            $this->spin(function () use ($row) {
                $gridRow  = $this->getDatagrid()->getRow($row);
                $gridRow->mouseOver();
                $removeButton = $gridRow->find('css', '.AknGrid-bodyRowRemove');
                $removeButton->click();
                return true;
            }, sprintf('Unable to remove the row "%s"', $row));
        }
    }

    /**
     * @param string      $rows
     * @param string|null $notChecked If not null, it checks checkbox is not checked.
     *
     * @throws ExpectationException
     *
     * @Then /^the rows? "([^"]*)" should (not )?be checked$/
     */
    public function theRowShouldBeChecked($rows, $notChecked = null)
    {
        $rows = $this->getMainContext()->listToArray($rows);

        foreach ($rows as $row) {
            $this->spin(function () use ($row, $notChecked) {
                $gridRow  = $this->getDatagrid()->getRow($row);
                if (null === $gridRow) {
                    return false;
                }
                $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');

                if (!$checkbox) {
                    return false;
                }

                return ((null === $notChecked && $checkbox->isChecked()) ||
                    (null !== $notChecked && !$checkbox->isChecked()));
            }, sprintf('Fail asserting that "%s" row was %schecked', $row, $notChecked));
        }
    }

    /**
     * @Then /^I reset the grid$/
     */
    public function iResetTheGrid()
    {
        $this->getDatagrid()->clickOnResetButton();
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
     * @param string $column
     *
     * @When /^I hide the "([^"]*)" column$/
     */
    public function iHideTheColumn($column)
    {
        $this->getDatagrid()->openColumnsPopin();
        $this->wait();
        $this->getDatagrid()->hideColumn($column);
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
        $this->getDatagrid()->openColumnsPopin();
        $this->wait();
        $this->getDatagrid()->moveColumn($source, $target);
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
     * @When /^I delete the view$/
     */
    public function iDeleteTheView()
    {
        $this->getCurrentPage()->removeView();
    }

    /**
     * @Then /^I should not be able to remove the view$/
     */
    public function iShouldNotBeAbleToRemoveTheView()
    {
        if (true === $this->getCurrentPage()->isViewDeletable()) {
            throw $this->createExpectationException('The current view should not be allowed to be removed.');
        }
    }

    /**
     * @Then /^I should not be able to save the view$/
     */
    public function iShouldNotBeAbleToSaveTheView()
    {
        if (true === $this->getCurrentPage()->isViewCanBeSaved()) {
            throw $this->createExpectationException('The current view should not be allowed to be saved.');
        }
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
        $this->getCurrentPage()->clickOnCreateViewButton();

        return [
            new Step\Then('I fill in the following information in the popin:', $table),
            new Step\Then('I press the "Save" button')
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
      * @param string $typeLabel
      *
      * @Then /^I should see the "([^"]*)" display in the datagrid$/
      *
      * @throws ExpectationException
      */
    public function iShouldSeeTheDisplayInTheDatagrid($typeLabel)
    {
        return $this->spin(function () use ($typeLabel) {
            return $this->getCurrentPage()->find('css',
                 sprintf('.AknGrid--%s', strtolower($typeLabel))
             );
        }, sprintf('Display type %s is not shown in the datagrid', $typeLabel));
    }

    /**
     * @param string $filterName
     * @param string $criteria
     *
     * @Then /^the criteria of "(.*)" filter should be "(.*)"$/
     */
    public function theCriteriaOfFilterShouldBe($filterName, $criteria)
    {
        $this->spin(function () use ($filterName, $criteria) {
            return $this->getDatagrid()->getCriteria($filterName) === $criteria;
        }, sprintf(
            'Expected to see "%s" as "%s" criteria, found "%s"',
            $criteria,
            $filterName,
            $this->getDatagrid()->getCriteria($filterName)
        ));
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
     * @return Page
     */
    protected function getDatagrid(): Page
    {
        if (null === $this->datagrid) {
            $this->datagrid = $this->pageFactory->createPage('Base\Grid');
        }

        return $this->datagrid;
    }

    /**
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Page
     */
    public function getCurrentPage()
    {
        return $this->getNavigationContext()->getCurrentPage();
    }

    /**
     * @param string $element
     *
     * @return mixed
     */
    protected function getElementFromDatagrid(string $element)
    {
        return $this->spin(function () use ($element) {
            return $this->getDatagrid()->getElement($element);
        }, sprintf('%s element is not found on datagrid', $element));
    }
}
