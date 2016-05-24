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
        $list       = $this->getCurrentPage()->getAssociationsList();
        $currentTab = $this->spin(function () use ($list) {
            return $list->find('css', '.active');
        }, 'Cannot find ".active" element');

        $tabLabel = trim($currentTab->getText());
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
