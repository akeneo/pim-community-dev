<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Behat\Behat\Context\Step;
use Behat\Behat\Context\Step\Then;
use Pim\Behat\Context\PimContext;

/**
 * A context for managing the grid pagination and size
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GridActionContext extends PimContext
{
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
        $this->getCurrentPage()->getCurrentGrid()->clickOnAction($element, $action);
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

        if ($not === $this->getCurrentPage()->getCurrentGrid()->findAction($element, $action)) {
            throw $this->getMainContext()->createExpectationException(
                sprintf(
                    'Expecting action "%s" on the row which containe "%s", but none found.',
                    $action,
                    $element
                )
            );
        }
    }

    /**
     * @Then /^I reset the grid$/
     */
    public function iResetTheGrid()
    {
        $this->getCurrentPage()->getCurrentGrid()->clickOnResetButton();
    }

    /**
     * @Then /^I refresh the grid$/
     */
    public function iRefreshTheGrid()
    {
        $this->getCurrentPage()->getCurrentGrid()->clickOnRefreshButton();
        $this->wait();
    }

    /**
     * @param string $column
     *
     * @When /^I hide the "([^"]*)" column$/
     */
    public function iHideTheColumn($column)
    {
        $dataGrid = $this->getCurrentPage()->getCurrentGrid();

        $dataGrid->openColumnsPopin();
        $this->wait();
        $dataGrid->hideColumn($column);
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
        $dataGrid = $this->getCurrentPage()->getCurrentGrid();

        $dataGrid->openColumnsPopin();
        $this->wait();
        $dataGrid->moveColumn($source, $target);
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
        $this->getCurrentPage()->getCurrentGrid()->massEdit();
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
            $this->getCurrentPage()->getCurrentGrid()->selectRow($entity, true);
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
            $this->getCurrentPage()->getCurrentGrid()->selectRow($entity, false);
        }
    }

    /**
     * @Then /^I select all visible products$/
     */
    public function iSelectAllVisible()
    {
        $this->getCurrentPage()->getCurrentGrid()->selectAllVisible();
    }

    /**
     * @Then /^I select none product$/
     */
    public function iSelectNone()
    {
        $this->getCurrentPage()->getCurrentGrid()->selectNone();
    }

    /**
     * @Then /^I select all products$/
     */
    public function iSelectAll()
    {
        $this->getCurrentPage()->getCurrentGrid()->selectAll();
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
        $this->getCurrentPage()->getCurrentGrid()->massDelete();
        $this->wait();
    }

    /**
     * @When /^I press sequential-edit button$/
     */
    public function iPressSequentialEditButton()
    {
        $this->getCurrentPage()->getCurrentGrid()->sequentialEdit();
        $this->wait();
        $this->getNavigationContext()->currentPage = 'Product edit';
    }
}
