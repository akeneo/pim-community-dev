<?php

namespace Pim\Behat\Context\Domain;

use Behat\Mink\Element\NodeElement;
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
        $module = $this->getModule();
        $this->openModule($module);
        $this->spin(function () use ($module, $actionName) {
            return $this->getAction($module, $actionName);
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
        $module = $this->getModule();
        $this->openModule($module);
        $this->spin(function () use ($not, $module, $actionName) {
            if ('' === $not) {
                return null !== $this->getAction($module, $actionName);
            } else {
                return null === $this->getAction($module, $actionName);
            }
        }, sprintf('Secondary action "%s" was %sfound', $actionName, $not));
        $this->closeModule($module);
    }

    /**
     * Returns the DOM element containing the secondary actions
     *
     * @return NodeElement
     */
    protected function getModule()
    {
        return $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.AknSecondaryActions');
        }, 'Could not find "Secondary Actions" module');
    }

    /**
     * Opens the dropdown
     *
     * @param NodeElement $module
     */
    protected function openModule($module)
    {
        if (!$module->hasClass('open')) {
            $this->spin(function () use ($module) {
                $module->find('css', '.AknSecondaryActions-button')->click();

                return true;
            }, 'Could not find "Secondary Actions" button');
        }

    }

    /**
     * Closes the dropdown
     *
     * @param NodeElement $module
     */
    protected function closeModule($module)
    {
        if ($module->hasClass('open')) {
            $this->getCurrentPage()->find('css', 'body')->click();
        }
    }

    /**
     * Returns the DOM element of the secondary action from its name.
     * If no action is found, returns null.
     *
     * @param $module
     * @param $actionName
     *
     * @return NodeElement|null
     */
    protected function getAction($module, $actionName)
    {
        $links = $module->findAll('css', '.AknDropdown-menuLink');
        foreach ($links as $link) {
            if (trim($link->getText()) === $actionName) {
                return $link;
            }
        }

        return null;
    }
}
