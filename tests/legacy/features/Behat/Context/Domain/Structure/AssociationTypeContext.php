<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Domain\Structure;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssociationTypeContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @When I search association types with the word :searchWord
     */
    public function iSearchAssociationTypesWithTheWord($searchWord)
    {
        $searchInput = $this->spin(function () use ($searchWord) {
            return $this->getCurrentPage()->find('css', '.association-type-grid-search input');
        }, 'Search input not found');

        $searchInput->setValue($searchWord);
    }

    /**
     * @Then /^I should not see association type (.*)$/
     */
    public function iShouldNotSeeAssociationType(string $associationType)
    {
        $grid = $this->findDataGridElement();

        $this->spin(function () use ($associationType, $grid) {
            return !$grid->find('css', sprintf('tr td:contains("%s")', $associationType));
        }, sprintf('Expected not to see association type "%s"', $associationType));
    }

    /**
     * @Then /^I should see association type (.*)$/
     */
    public function iShouldSeeAssociationType(string $associationType)
    {
        $grid = $this->findDataGridElement();

        $this->spin(function () use ($associationType, $grid) {
            return $grid->find('css', sprintf('tr td:contains("%s")', $associationType));
        }, sprintf('Expected to see association type "%s"', $associationType));
    }

    private function findDataGridElement(): NodeElement
    {
        return $this->spin(function () {
            return $this->getCurrentPage()->find('css', 'table.grid') ?? false;
        }, 'Association-types data-grid not found');
    }
}
