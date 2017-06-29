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
     * Checks if the add attribute selector has an option.
     * If the optional parameter $groupLabel is set, it will check if the option belongs to this group.
     *
     * @param string      $optionLabel
     * @param string|null $groupLabel
     *
     * @return bool
     */
    public function hasAvailableOption($optionLabel, $groupLabel = null)
    {
        $result = false;

        $attribute = $this->openDropList()
            ->evaluateSearch($optionLabel)
            ->getResultForSearch($optionLabel);

        if (null !== $attribute) {
            $result = true;

            if (null !== $groupLabel) {
                $groupElement = $attribute->getParent()
                    ->find('css', '.group-label');

                $result = trim($groupElement->getText()) === $groupLabel;
            }
        }

        $this->closeDropList();

        return $result;
    }
}
