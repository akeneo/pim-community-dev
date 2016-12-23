<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Behat\Decorator\Element\Grid;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ViewSelectorCreateButtonDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    public function open()
    {
        $this->click();

        return $this;
    }

    public function chooseAction($action)
    {
        $parent = $this->getParent();

        $dropDownMenu = $this->spin(function () use ($parent) {
            return $parent->find('css', '.create-view-dropdown-menu');
        }, sprintf('Impossible to find the drop down', $action));

        $createBtn = $this->spin(function () use ($dropDownMenu, $action) {
            return $dropDownMenu->find('css', sprintf(
                '.action:contains("Create %s")',
                $action
            ));
        }, sprintf('Item "Create %s" of dropdown button not found', $action));

        $createBtn->click();
    }
}
