<?php

namespace Pim\Behat\Decorator\Grid;

use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\Field\Select2Decorator;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ViewTypeSwitcherDecorator extends Select2Decorator
{
    use SpinCapableTrait;

    /**
     * @param string $type
     *
     * @throws TimeoutException
     */
    public function switchViewType($type)
    {
        $widget = $this->getWidget();

        $viewTypeSwitcher = $this->spin(function () use ($widget) {
            return $widget->find('css', '.view-selector-type-switcher');
        }, 'Cannot find the View Type Switcher in the View Selector.');

        $viewTypeSwitcher->click();

        $viewType = $this->spin(function () use ($widget, $type) {
            return $widget->find('css', sprintf('[data-action="switchViewType"][title="%s"]', $type));
        }, sprintf('Cannot find element in the View Type Switcher dropdown with name "%s".', $type));

        $viewType->click();
    }

    /**
     * @throws TimeoutException
     *
     * @return string
     */
    public function getCurrentViewType()
    {
        $widget = $this->getWidget();

        $viewTypeSwitcher = $this->spin(function () use ($widget) {
            return $widget->find('css', '.view-selector-type-switcher');
        }, 'Cannot find the View Type Switcher in the View Selector.');

        return $viewTypeSwitcher->getText();
    }
}
