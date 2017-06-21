<?php

namespace Pim\Behat\Context\Domain;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

class SecondaryActionsContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param string $actionName
     *
     * @When /^I press the secondary action "(.*)"$/
     */
    public function iPressTheSecondaryAction($actionName)
    {
        $module = $this->spin(function () {
            return $this->getCurrentPage()->getElement('Secondary actions');
        }, 'Can not find the Secondary actions module');
        $module->open();

        $this->spin(function () use ($module, $actionName) {
            return $module->getMenuItem($actionName);
        }, sprintf('Could not find the secondary action "%s"', $actionName))->click();
    }

    /**
     * @param string $not
     * @param string $actionName
     *
     * @Then /^I should (not )?see the secondary action "(?P<actionName>.*)"$/
     */
    public function iShouldSeeTheSecondaryAction($not, $actionName)
    {
        $module = $this->spin(function () {
            return $this->getCurrentPage()->getElement('Secondary actions');
        }, 'Can not find the Secondary actions module');
        $module->open();

        $this->spin(function () use ($module, $not, $actionName) {
            if ('' === $not) {
                return null !== $module->getMenuItem($actionName);
            } else {
                return null === $module->getMenuItem($actionName);
            }
        }, sprintf('Secondary action "%s" was %sfound', $actionName, $not));
        $module->close();
    }
}
