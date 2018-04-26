<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Behat\Decorator\Page;

use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Page\GridCapableDecorator as GridCapableDecoratorOrigin;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class GridCapableDecorator extends GridCapableDecoratorOrigin
{
    public function __construct($element)
    {
        parent::__construct($element);

        $this->selectors = array_merge($this->selectors, [
            'Create project button' => '.grid-view-selector .create-project-button .create',
            'Edit project button'   => '.grid-view-selector .edit-button .edit',
            'Remove project button' => '.grid-view-selector .remove-button .remove',
        ]);
    }

    /**
     * Click on the create project button
     */
    public function clickOnCreateProjectButton()
    {
        $this->openSecondaryActions();
        $selector = $this->selectors['Create project button'];

        $this->spin(function () use ($selector) {
            $button = $this->find('css', $selector);
            if (null === $button) {
                return false;
            }

            $button->click();

            return true;
        }, sprintf('Create project button not found (%s).', $selector));
    }

    /**
     * Click on the edit project button
     */
    public function clickOnEditProjectButton()
    {
        $this->openSecondaryActions();
        $selector = $this->selectors['Edit project button'];

        $button = $this->spin(function () use ($selector) {
            return $this->find('css', $selector);
        }, sprintf('Edit project button not found (%s).', $selector));

        $button->click();
    }

    /**
     * Click on the remove project button
     */
    public function clickOnRemoveProjectButton()
    {
        $this->openSecondaryActions();
        $selector = $this->selectors['Remove project button'];

        $button = $this->spin(function () use ($selector) {
            return $this->find('css', $selector);
        }, sprintf('Remove project button not found (%s).', $selector));

        $button->click();
    }

    /**
     * Return the decorated teamwork assistant widget
     *
     * @return ElementDecorator
     */
    public function getTeamworkAssistantWidget()
    {
        $widget = $this->spin(function () {
            return $this->find('css', '#teamwork-assistant-widget');
        }, 'teamwork assistant widget not found.');

        return $this->decorate(
            $widget,
            ['PimEnterprise\Behat\Decorator\Widget\TeamworkAssistantWidgetDecorator']
        );
    }
}
