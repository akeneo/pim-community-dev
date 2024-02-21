<?php

namespace Pim\Behat\Decorator;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\Field\Select2Decorator;

/**
 * Decorator for the Variant Navigation bar, displayed on Product Edit Form, which allows user
 * to navigate among ascendant and descendant elements of the current product / product model.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class VariantNavigationDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Get the axis name NodeElement for the given $level.
     * The axis name, for example "Material", or "Color, Size".
     *
     * @param int $level
     *
     * @return NodeElement
     */
    public function getAxisNameForLevel(int $level): NodeElement
    {
        $axisNames = $this->spin(function () {
            return $this->findAll('css', '.AknVariantNavigation-axisName');
        }, 'Impossible to find any axis name in the Variant Navigation bar.');

        if (count($axisNames) < $level) {
            throw new \LogicException(sprintf(
                'Impossible to get the axis name for level "%s", only "%s" items found.',
                $level,
                count($axisNames)
            ));
        }

        return $axisNames[$level];
    }

    /**
     * Get the selected axis values NodeElement for the given $level.
     * The selected axis values, for example "Silk", or "Blue, XL".
     *
     * @param int $level
     *
     * @return NodeElement
     */
    public function getSelectedAxisValuesForLevel(int $level): NodeElement
    {
        $selectedAxisValues = $this->spin(function () {
            return $this->findAll('css', '.AknVariantNavigation-axisValue');
        }, 'Impossible to find any selected axis value in the Variant Navigation bar.');

        if (count($selectedAxisValues) < $level) {
            throw new \LogicException(sprintf(
                'Impossible to get the selected axis values for level "%s", only "%s" items found.',
                $level,
                count($selectedAxisValues)
            ));
        }

        return $selectedAxisValues[$level];
    }

    /**
     * Get the Select2 element for the given $level.
     *
     * @param int $level
     *
     * @return ElementDecorator
     */
    public function getChildrenSelectorForLevel(int $level): ElementDecorator
    {
        $childrenSelectors = $this->spin(function () {
            return $this->findAll('css', '.variant-navigation.select2-container');
        }, 'No children selector found in the Variant Navigation bar.');

        if (count($childrenSelectors) < $level) {
            throw new \LogicException(sprintf(
                'Impossible to get the children selector for level "%s", only "%s" items found.',
                $level,
                count($childrenSelectors)
            ));
        }

        // There is no selector on level 0 of the variant navigation.
        // So if we want the selector for level 1, it's on index 0, etc.
        $selectorIndex = $level - 1;

        return $this->decorate($childrenSelectors[$selectorIndex], [Select2Decorator::class]);
    }
}
