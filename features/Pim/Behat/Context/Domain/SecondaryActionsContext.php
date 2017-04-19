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
            return $this->getCurrentPage()->find('css', '.AknSecondaryActions');
        }, 'Could not find "Secondary Actions" module');

        if (!$module->hasClass('open')) {
            $this->spin(function () use ($module) {
                return $module->find('css', '.AknSecondaryActions-button');
            }, 'Could not find "Secondary Actions" button')->click();
        }

        $this->spin(function () use ($module, $actionName) {
            $links = $module->findAll('css', '.AknDropdown-menuLink');

            foreach ($links as $link) {
                if ($link->getText() === $actionName) {
                    return $link;
                }
            }

            return null;
        }, sprintf('Could not find the secondary action "%s"', $actionName))->click();
    }
}
