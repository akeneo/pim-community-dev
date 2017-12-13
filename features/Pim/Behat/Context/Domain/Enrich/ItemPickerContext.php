<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

/**
 * Context for the item picker
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ItemPickerContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @Given I remove :entity from the basket
     */
    public function iRemoveFromTheBasket($entity)
    {
        $removeButton = $this->spin(function () use ($entity) {
            return $this->getSession()->getPage()
                ->find('css', sprintf('.item-picker-basket .remove-item[data-itemCode="%s"]', $entity));
        }, 'Cannot find button to remove from basket');

        $removeButton->click();
    }

    /**
     * @Then /^the item picker basket should contain (.+)$/
     */
    public function theItemPickerBasketShouldContain($entities)
    {
        foreach ($this->getMainContext()->listToArray($entities) as $entity) {
            $this->getItemPickerBasketItems($entity);
        }
    }

    /**
     * @param string $code
     *
     * @throws \Exception
     *
     * @return NodeElement
     */
    protected function getItemPickerBasketItems($code)
    {
        return $this->spin(function () use ($code) {
            return $this->getSession()->getPage()
                ->find('css', sprintf(
                    '.item-picker-basket .AknGrid-subTitle:contains("%s"),
                    .item-picker-basket .AknGrid-title:contains("%s")',
                    $code,
                    $code
                ));
        }, sprintf('Cannot find item "%s" in basket', $code));
    }
}
