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
     * @Given /^the grid should contain (\d+) element$/
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
     * @Given /^Value of column "([^"]*)" of the row which contain "([^"]*)" should be "([^"]*)"$/
     */
    public function valueOfColumnOfTheRowWhichContainShouldBe($column, $row, $expectation)
    {
        if ($expectation !== $actual = $this->datagrid->getColumnValue($column, $row, $expectation)) {
            throw $this->createExpectationException(sprintf(
                'Expecting column "%s" to contain "%s", got "%s".',
                $column, $expectation, $actual
            ));
        }
    }

    private function createExpectationException($message)
    {
        return $this->getMainContext()->createExpectationException($message);
    }
}

