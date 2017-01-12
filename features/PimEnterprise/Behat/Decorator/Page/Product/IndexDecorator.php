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

    /** @var array Selectors to ease find */
    protected $selectors = [
        'Create project button' => '.grid-view-selector .create-project-button .create',
    ];

    /**
     * Click on the create project button
     */
    public function clickOnCreateProjectButton()
    {
        $selector = $this->selectors['Create project button'];

        $button = $this->spin(function () use ($selector) {
            return $this->find('css', $selector);
        }, sprintf('Create project button not found (%s).', $selector));

        $button->click();
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
            ['PimEnterprise\Behat\Decorator\Widget\ActivityManagerWidgetDecorator']
        );
    }
}
