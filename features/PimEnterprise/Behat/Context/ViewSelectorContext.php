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
use Context\ViewSelectorContext as BaseViewSelectorContext;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ViewSelectorContext extends BaseViewSelectorContext
{
    use SpinCapableTrait;

    /**
     * @Given /^I click on the create project button$/
     */
    public function iClickOnCreateProjectButton()
    {
        $this->getCurrentPage()->clickOnCreateProjectButton();
    }

    /**
     * @Given /^I click on the edit project button$/
     */
    public function iClickOnEditProjectButton()
    {
        $this->getCurrentPage()->clickOnEditProjectButton();
    }

    /**
     * @Given /^I click on the remove project button$/
     */
    public function iClickOnRemoveProjectButton()
    {
        $this->getCurrentPage()->clickOnRemoveProjectButton();
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
