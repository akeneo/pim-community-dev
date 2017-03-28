<?php

namespace Pim\Behat\Decorator\Common\AddSelect;

/**
 * Decorate attribute add select element
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeAddSelectDecorator extends AbstractAddSelectDecorator
{
    /** @var string */
    protected $baseClass = '.add-attribute';

    /**
     * @param $item
     * @param $group
     *
     * @return bool
     */
    public function hasAvailableOptionGroupPair($item, $group)
    {
        $result = false;

        $attribute = $this->openDropList()
            ->evaluateSearch($item)
            ->getResultForSearch($item);

        if (null !== $attribute) {
            $groupElement = $attribute->getParent()
                ->find('css', '.group-label');

            $result = trim($groupElement->getText()) === $group;
        }

        $this->closeDropList();

        return $result;
    }
}
