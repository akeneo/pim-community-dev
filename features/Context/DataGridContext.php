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
     * @param PageFactory $pageFactory
     */
    public function setPageFactory(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
        $this->datagrid = $pageFactory->createPage('DataGrid');
    }

    /**
     * @Given /^the grid should contain (\d+) elements?$/
     */
    public function theGridShouldContainElement($count)
    {
        if (intval($count) !== $actualCount = $this->datagrid->countRows()) {
            throw $this->createExpectationException(sprintf(
                'Expecting to see %d row(s) in the datagrid, actually saw %d.',
                $count, $actualCount
            ));
        }
    }

    /**
     * @Given /^the grid should contains the elements? (.*)$/
     * @param string $expectedElements
     */
    public function theGridShouldContainsTheElements($expectedElements)
    {
        $expectedElements = $this->getMainContext()->listToArray($expectedElements);

        foreach ($expectedElements as $expectedElement) {
            $this->datagrid->getGridRow($expectedElement);
        }
    }

    /**
     * @Given /^the grid should not contains the elements? (.*)$/
     * @param string $notExpectedElements
     */
    public function theGridShouldNotContainsTheElement($notExpectedElements)
    {
        $notExpectedElements = $this->getMainContext()->listToArray($notExpectedElements);

        foreach ($notExpectedElements as $notExpectedElement) {
            try {
                $expectedValue = $this->datagrid->getGridRow($notExpectedElement);
                throw new \InvalidArgumentException(
                    sprintf('The grid should not contains the element %s', $notExpectedElement)
                );
            } catch (\InvalidArgumentException $e) {
                // nothing to do
            }
        }
    }

    /**
     * @Given /^Value of column "([^"]*)" of the row which contain "([^"]*)" should be "([^"]*)"$/
     */
    public function valueOfColumnOfTheRowWhichContainShouldBe($column, $row, $expectation)
    {
        $column = strtoupper($column);
        if ($expectation !== $actual = $this->datagrid->getColumnValue($column, $row, $expectation)) {
            throw $this->createExpectationException(sprintf(
                'Expecting column "%s" to contain "%s", got "%s".',
                $column, $expectation, $actual
            ));
        }
    }

    /**
     * @Given /^I should see the filters (.*)$/
     *
     * @param string $filters
     */
    public function iShouldSeeTheFilters($filters)
    {
        $filters = $this->getMainContext()->listToArray($filters);
        foreach ($filters as $filter) {
            $this->datagrid->getFilter($filter);
        }
    }

    /**
     * @When /^I click on the "([^"]*)" row$/
     */
    public function iClickOnTheRow($row)
    {
        $this->datagrid->getGridRow($row)->click();
        $this->wait(5000, null);
    }

    private function createExpectationException($message)
    {
        return $this->getMainContext()->createExpectationException($message);
    }

    private function wait($time = 5000, $condition = 'document.readyState == "complete" && !$.active')
    {
        return $this->getMainContext()->wait($time, $condition);
    }
}

