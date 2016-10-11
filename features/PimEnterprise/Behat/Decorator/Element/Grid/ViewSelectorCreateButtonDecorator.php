<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Behat\Decorator\Element\Grid;

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
        $dropdownMenu = $this->getParent()->find('css', '.dropdown-menu');

        $createBtn = $this->spin(function () use ($dropdownMenu, $action) {
            return $dropdownMenu->find('css', sprintf(
                '.action:contains("Create %s") .select-view-action-list',
                $action,
                $action
            ));
        }, sprintf('Item "Create %s" of dropdown button not found', $action));
        $createBtn->click();
    }
}
