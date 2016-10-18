<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Behat\Decorator\Page\Product;

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
    public function getCreationButton()
    {
        $button = $this->spin(function () {
            return $this->find('css', '.btn-group:contains("Create todo")');
        }, 'The button used to create a view was not found.');

        return $this->decorate(
            $button,
            ['Akeneo\ActivityManager\Behat\Decorator\Element\Grid\ViewSelectorCreateButtonDecorator']
        );
    }
}
