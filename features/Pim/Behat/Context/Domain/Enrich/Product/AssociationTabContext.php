<?php

namespace Pim\Behat\Context\Domain\Enrich\Product;

use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

class AssociationTabContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param string $association
     *
     * @Given /^I select the "([^"]*)" association$/
     */
    public function iSelectTheAssociation($association)
    {
        $this->spin(function () use ($association) {
            $this->getCurrentPage()
                ->getAssociationsList()
                ->clickLink($association);

            return true;
        }, sprintf('Cannot select the association "%s"', $association));
    }

    /**
     * @param string $association
     *
     * @throws ExpectationException
     *
     * @Given /^I should be on the "([^"]*)" association$/
     */
    public function iShouldBeOnTheAssociation($association)
    {
        $this->spin(function () use ($association) {
            $currentTab = $this->getCurrentPage()->getAssociationsList()->find('css', '.active');
            if (null === $currentTab) {
                return false;
            }

            $tabLabel = trim($currentTab->getText());

            return $tabLabel === $association;
        }, sprintf(
            sprintf('Failing to assert that current association is %s', $association),
            $association
        ));
    }
}
