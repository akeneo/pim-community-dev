<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Behat\Context;

use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ViewSelectorContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @Given /^I open the view selector$/
     */
    public function iOpenTheViewSelector()
    {
        $this->getCurrentPage()->getViewSelector()->click();
    }

    /**
     * @Given /^I click on the create project button$/
     */
    public function iClickOnCreateProjectButton()
    {
        $this->getCurrentPage()->clickOnCreateProjectButton();
    }

    /**
     * @Then /^I should( not)? see the "([^"]*)" project$/
     *
     * @param string $not
     * @param string $projectName
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheProject($not, $projectName)
    {
        $values = $this->getCurrentPage()->getViewSelector()->getAvailableValues();
        $found = false;

        foreach ($values as $value) {
            if (strpos($value, $projectName) !== false) {
                $found = true;
            }
        }

        if ($not && $found) {
            throw new \UnexpectedValueException(
                sprintf('Project "%s" should not be displayed.', $projectName)
            );
        } elseif (!$not && !$found) {
            throw new \UnexpectedValueException(
                sprintf('Project "%s" should be displayed.', $projectName)
            );
        }
    }

    /**
     * @Given /^I filter view selector with name "([^"]*)"$/
     *
     * @param string $name
     */
    public function iFilterViewSelectorWithName($name)
    {
        $this->getCurrentPage()->getViewSelector()->search($name);
    }

    /**
     * @Given /^I switch view selector type to "([^"]*)"$/
     *
     * @param string $viewType
     */
    public function iSwitchViewSelectorTypeTo($viewType)
    {
        $this->getCurrentPage()->switchViewType($viewType);
    }

    /**
     * @Then /^view selector type switcher should be on "([^"]*)"$/
     *
     * @param string $expectedType
     */
    public function viewSelectorTypeSwitcherShouldBeOn($expectedType)
    {
        $currentType = $this->getCurrentPage()->getCurrentViewType();
        $currentType = ucfirst(strtolower($currentType));

        if ($currentType !== $expectedType) {
            throw new \UnexpectedValueException(
                sprintf('View selector type switcher should be on "%s", but is on "%s".', $expectedType, $currentType)
            );
        }
    }
}
