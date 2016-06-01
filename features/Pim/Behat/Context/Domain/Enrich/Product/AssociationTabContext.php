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
        $this->getCurrentPage()
            ->getAssociationsList()
            ->clickLink($association);
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
        $list     = $this->getCurrentPage()->getAssociationsList();
        $tabLabel = $this->spin(function () use ($list) {
            $activeTab = $list->find('css', '.active');
            if (null !== $activeTab) {
                return $activeTab->getText();
            }

            return false;
        }, 'Cannot find ".active" element');

        $tabLabel = trim($tabLabel);
        if ($tabLabel !== $association) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting "%s" to be the current association type, got "%s"',
                    $association,
                    $tabLabel
                )
            );
        }
    }
}
