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

use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
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
     * @Then /^I should see the "([^"]*)" project$/
     *
     * @param string $projectName
     *
     * @throws TimeoutException
     */
    public function iShouldSeeTheProject($projectName)
    {
        $this->spin(function () use ($projectName) {
            $isProjectDisplayed = $this->isProjectDisplayed($projectName);

            return true === $isProjectDisplayed ? true : null;
        }, sprintf('Project "%s" should be displayed.', $projectName));
    }

    /**
     * @Then /^I should not see the "([^"]*)" project$/
     *
     * @param string $projectName
     *
     * @throws TimeoutException
     */
    public function iShouldNotSeeTheProject($projectName)
    {
        $this->spin(function () use ($projectName) {
            $isProjectDisplayed = $this->isProjectDisplayed($projectName);

            return false === $isProjectDisplayed ? true : null;
        }, sprintf('Project "%s" should not be displayed.', $projectName));
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
     * @Given /^I should see the "([^"]*)" view type$/
     *
     * @param string $viewType
     */
    public function iShouldSeeTheViewType($viewType)
    {
        $this->getCurrentPage()->existViewType($viewType);
    }

    /**
     * @Given /^I should not see the "([^"]*)" view type$/
     *
     * @param string $viewType
     */
    public function iShouldNotSeeTheViewType($viewType)
    {
        $this->getCurrentPage()->notExistViewType($viewType);
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

    private function isProjectDisplayed(string $projectName): bool
    {
        $values = $this->getCurrentPage()->getViewSelector()->getAvailableValues();

        foreach ($values as $value) {
            if (strpos($value, $projectName) !== false) {
                return true;
            }
        }

        return false;
    }
}
