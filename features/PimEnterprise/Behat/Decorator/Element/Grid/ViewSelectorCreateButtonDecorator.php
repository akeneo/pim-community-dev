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

    public function click($label)
    {
        $dropdownToggle = $this->spin(function () {
            return $this->find('css', '.dropdown-toggle');
        }, 'Dropdown toggle button not found');
        $dropdownToggle->click();

        $dropdownMenu = $dropdownToggle->getParent()->find('css', '.dropdown-menu');

        $createViewBtn = $this->spin(function () use ($dropdownMenu, $label) {
            return $dropdownMenu->find('css', sprintf('li:contains("%s") [data-action="prompt-creation"]', $label));
        }, 'Item "Create view" of dropdown button not found');
        $createViewBtn->click();
    }
}
