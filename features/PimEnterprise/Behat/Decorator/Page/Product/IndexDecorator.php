<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Behat\Decorator\Page\Product;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class IndexDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * @return mixed
     */
    public function getSelectViewActionDropdown()
    {
        $dropdownToggle = $this->spin(function () {
            return $this->find('css', '.create-dropdown.dropdown-toggle');
        }, 'Dropdown toggle button not found');

        return $this->decorate(
            $dropdownToggle,
            ['PimEnterprise\Behat\Decorator\Element\Grid\ViewSelectorCreateButtonDecorator']
        );
    }

    /**
     * Return the decorated Activity Manager widget
     *
     * @return ElementDecorator
     */
    public function getActivityManagerWidget()
    {
        $widget = $this->spin(function () {
            return $this->find('css', '#activity-manager-widget');
        }, 'Activity Manager widget not found.');

        return $this->decorate(
            $widget,
            ['PimEnterprise\Behat\Widget\WidgetDecorator']
        );
    }
}
