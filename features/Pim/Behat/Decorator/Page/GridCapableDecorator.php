<?php

namespace Pim\Behat\Decorator\Page;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to handle the grid of a page
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GridCapableDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /** @var array Selectors to ease find */
    protected $selectors = [
        'Dialog grid'        => '.modal',
        'Grid'               => 'table.grid',
        'View selector'      => '.grid-view-selector .select2-container',
        'Create view button' => '.grid-view-selector .create-button .create',
        'Save view button'   => '.grid-view-selector .save-button .save',
        'Remove view button' => '.grid-view-selector .remove-button .remove',
    ];

    /** @var array */
    protected $gridDecorators = [
        'Pim\Behat\Decorator\Grid\PaginationDecorator',
    ];

    /** @var array */
    protected $viewSelectorDecorators = [
        'Pim\Behat\Decorator\Field\Select2Decorator',
    ];

    /**
     * Returns the view selector
     *
     * @return NodeElement
     */
    public function getViewSelector()
    {
        $viewSelector = $this->spin(function () {
            $result = $this->find('css', $this->selectors['View selector']);
            if ((null === $result) || !$result->isVisible()) {
                return false;
            }

            return $result;
        }, 'View selector not found.');

        return $this->decorate($viewSelector, $this->viewSelectorDecorators);
    }

    /**
     * This method opens the view selector and ensure the drop is displayed
     *
     * @throws TimeoutException
     */
    public function openViewSelector()
    {
        $viewSelector = $this->getViewSelector();
        $this->spin(function () use ($viewSelector) {
            $result = $this->find('css', '.select2-drop.grid-view-selector');
            if ((null === $result) || !$result->isVisible()) {
                $viewSelector->find('css', '.select2-arrow')->click();

                return false;
            }

            return true;
        }, 'Could not open view selector');
    }

    public function clickOnCreateViewButton()
    {
        $selector = $this->selectors['Create view button'];

        $button = $this->spin(function () use ($selector) {
            return $this->find('css', $selector);
        }, sprintf('Create view button not found (%s).', $selector));

        $this->spin(function () use ($button) {
            $button->click();
            $modalHeader = $this->find(
                'css',
                '.modal-header:contains("Choose a label for the view")'
            );

            return null !== $modalHeader;
        }, 'Impossible to open the create view popin');
    }

    /**
     * Returns available views in the datagrid view selector.
     *
     * @return array
     */
    public function getAvailableViews()
    {
        return $this->getViewSelector()->getAvailableValues();
    }

    /**
     * Save the current view
     */
    public function saveView()
    {
        $selector = $this->selectors['Save view button'];

        $button = $this->spin(function () use ($selector) {
            return $this->find('css', $selector);
        }, sprintf('Save view button not found (%s).', $selector));

        $button->click();
    }

    /**
     * Remove the current view
     */
    public function removeView()
    {
        $selector = $this->selectors['Remove view button'];

        $button = $this->spin(function () use ($selector) {
            return $this->find('css', $selector);
        }, sprintf('Remove view button not found (%s).', $selector));

        $button->click();
    }

    /**
     * Returns the currently visible grid, if there is one
     *
     * @return NodeElement
     */
    public function getCurrentGrid()
    {
        $grid = $this->spin(
            function () {
                $modal = $this->find('css', $this->selectors['Dialog grid']);
                if (null !== $modal && $modal->isVisible()) {
                    return $modal->find('css', $this->selectors['Grid']);
                }

                return $this->find('css', $this->selectors['Grid']);
            },
            'No visible grid found'
        );

        return $this->decorate($grid->getParent()->getParent()->getParent(), $this->gridDecorators);
    }

    /**
     * @param string $type
     *
     * @throws TimeoutException
     */
    public function switchViewType($type)
    {
        $widget = $this->getViewSelector()->getWidget();

        $viewTypeSwitcher = $this->spin(function () use ($widget) {
            return $widget->find('css', '.view-selector-type-switcher');
        }, 'Cannot find the View Type Switcher in the View Selector.');

        $viewTypeSwitcher->click();

        $viewType = $this->spin(function () use ($widget, $type) {
            return $widget->find('css', sprintf('.view-type-item[title="%s"]', $type));
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
        $widget = $this->getViewSelector()->getWidget();

        $viewTypeSwitcher = $this->spin(function () use ($widget) {
            return $widget->find('css', '.view-selector-type-switcher');
        }, 'Cannot find the View Type Switcher in the View Selector.');

        return $viewTypeSwitcher->getText();
    }
}
