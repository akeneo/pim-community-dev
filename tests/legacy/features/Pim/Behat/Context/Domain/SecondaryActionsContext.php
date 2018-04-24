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
        $this->spin(function () use ($actionName) {
            $module = $this->getElementOnCurrentPage('Secondary actions');
            $module->open();
            sleep(1);

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
        $module = $this->getElementOnCurrentPage('Secondary actions');
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
