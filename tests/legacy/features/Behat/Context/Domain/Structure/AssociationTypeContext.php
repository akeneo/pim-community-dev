<?php

declare(strict_types=1);

namespace Pim\Behat\Context\Domain\Structure;

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
}
