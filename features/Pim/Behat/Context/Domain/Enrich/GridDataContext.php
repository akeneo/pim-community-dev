<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Behat\Behat\Context\Step;
use Behat\Behat\Context\Step\Then;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

/**
 * A context for managing the grid pagination and size
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GridDataContext extends PimContext
{
    use SpinCapableTrait;

    /** @var array $gridNames */
    protected $gridNames = [
        'products' => 'product-grid'
    ];

    /**
     * Returns the internal grid name from a human readable label
     *
     * @param string $gridLabel
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
     * @param int $count
     *
     * @return mixed
     *
     * @Given /^the grid should contain (\d+) elements?$/
     */
    public function theGridShouldContainElement($count)
    {
        $count = (int) $count;
        $dataGrid = $this->getCurrentPage()->getCurrentGrid();

        if (0 === $count) {
            assertTrue($dataGrid->isGridEmpty());
            return;
        }

        if ($count > 10) {
            $dataGrid->setPageSize(100);
        }

        $this->spin(function () use ($count, $dataGrid) {
            assertEquals(
                $count,
                $actualCount = $dataGrid->getToolbarCount()
            );

            return true;
        }, sprintf(
            'Expecting to see %d record(s) in the datagrid toolbar, actually saw %d',
            $count,
            $dataGrid->countRows()
        ));

        $this->spin(function () use ($count, $dataGrid) {
            assertEquals(
                $count,
                $actualCount = $dataGrid->countRows()
            );

            return true;
        }, sprintf(
            'Expecting to see %d row(s) in the datagrid, actually saw %d.',
            $count,
            $dataGrid->countRows()
        ));
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
        $dataGrid = $this->getCurrentPage()->getCurrentGrid();

        $this->wait('$("table.grid").length > 0');

        $countColumns = $dataGrid->countColumns();
        if ($expectedColumns !== $countColumns) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('Expected %d columns but contains %d', $expectedColumns, $countColumns)
            );
        }

        $expectedPosition = 0;
        foreach ($columns as $column) {
            $position = $dataGrid->getColumnPosition($column, false, false);
            if ($expectedPosition++ !== $position) {
                throw $this->getMainContext()->createExpectationException(
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
     * @param string $columnName
     * @param string $order
     *
     * @When /^I sort by "(.*)" value (ascending|descending)$/
     */
    public function iSortByValue($columnName, $order = 'ascending')
    {
        $this->getCurrentPage()->getCurrentGrid()->sortBy($columnName, $order);

        $loadlingMask = $this
            ->getCurrentPage()
            ->getCurrentGrid()
            ->getGridContainer()
            ->find('css', '.loading-mask .loading-mask');

        $this->spin(function () use ($loadlingMask) {
            return !$loadlingMask->isVisible();
        });
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
            if (!$this->getCurrentPage()->getCurrentGrid()->getRow($element)) {
                throw $this->getMainContext()->createExpectationException(sprintf('Entity "%s" not found', $element));
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
        if ($this->getCurrentPage()->getCurrentGrid()->isGridEmpty()) {
            return;
        }

        foreach ($this->getMainContext()->listToArray($entities) as $entity) {
            if ($this->getCurrentPage()->getCurrentGrid()->hasRow($entity)) {
                throw $this->getMainContext()->createExpectationException(
                    sprintf('Entity "%s" should not be seen', $entity)
                );
            }
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
            $this->assertColumnContainsValue($code, $data['column'], $data['value']);
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
            $gridRow  = $this->getCurrentPage()->getCurrentGrid()->getRow($row);
            $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');

            if (!$checkbox) {
                throw $this->getMainContext()->createExpectationException(
                    sprintf('Unable to find a checkbox for row %s', $row)
                );
            }

            if (!$checkbox->isChecked()) {
                throw $this->getMainContext()->createExpectationException(
                    sprintf('Expecting row %s to be checked', $row)
                );
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
            $gridRow = $this->getCurrentPage()->getCurrentGrid()->getRow($row);
            $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');

            if (!$checkbox) {
                throw $this->getMainContext()->createExpectationException(
                    sprintf('Unable to find a checkbox for row %s', $row)
                );
            }

            if ($checkbox->isChecked()) {
                throw $this->createExpectationException(sprintf('Expecting row %s to be checked', $row));
            }
        }
    }

    /**
     * @param string $order
     * @param string $columnName
     *
     * @throws ExpectationException
     *
     * @Then /^the rows should be sorted (ascending|descending) by (.*)$/
     */
    public function theRowsShouldBeSortedBy($order, $columnName)
    {
        $columnName = strtoupper($columnName);

        if (!$this->getCurrentPage()->getCurrentGrid()->isSortedAndOrdered($columnName, $order)) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('The rows are not sorted %s by column %s', $order, $columnName)
            );
        }
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
                $this->getCurrentPage()->getCurrentGrid()->getColumnSorter($columnName);
            }
        } catch (\InvalidArgumentException $e) {
            throw $this->getMainContext()->createExpectationException($e->getMessage());
        }
    }

    /**
     * @param string $row
     *
     * @When /^I click on the "([^"]*)" row$/
     */
    public function iClickOnTheRow($row)
    {
        $row = $this->spin(function () use ($row) {
            return $this->getCurrentPage()->getCurrentGrid()->getRow($row);
        });

        $row->click();
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
            $gridRow  = $this->getCurrentPage()->getCurrentGrid()->getRow($row);
            $checkbox = $gridRow->find('css', 'td.boolean-cell input[type="checkbox"]:not(:disabled)');

            if (!$checkbox) {
                throw $this->getMainContext()->createExpectationException(
                    sprintf('Unable to find a checkbox for row %s', $row)
                );
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
            $this->getCurrentPage()->getCurrentGrid()->selectRow($row, false);
        }
    }
}
