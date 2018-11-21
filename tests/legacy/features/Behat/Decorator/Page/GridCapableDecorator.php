<?php

namespace Pim\Behat\Decorator\Page;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;
use Pim\Behat\Decorator\Grid\PaginationDecorator;

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
        'Dialog grid'                  => '.modal',
        'Grid'                         => 'table.grid',
        'View selector'                => '.grid-view-selector .select2-container',
        'View type switcher'           => '.grid-view-selector .view-selector-type-switcher',
        'Create view button'           => '.grid-view-selector .create-button .create',
        'Save view button'             => '.grid-view-selector .save-button .save',
        'Remove view button'           => '.grid-view-selector .remove-button .remove',
        'Grid view secondary actions'  => '.grid-view-selector .secondary-actions',
    ];

    /** @var array */
    protected $gridDecorators = [
        PaginationDecorator::class,
    ];

    /** @var array */
    protected $viewSelectorDecorators = [
        Select2Decorator::class,
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
        $this->openSecondaryActions();
        $selector = $this->selectors['Create view button'];

        $button = $this->spin(function () use ($selector) {
            return $this->find('css', $selector);
        }, sprintf('Create view button not found (%s).', $selector));

        $this->spin(function () use ($button) {
            if (null !== $this->find('css', '.modal-body')) {
                return true;
            }
            $button->click();

            return false;
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
        $this->openSecondaryActions();
        $selector = $this->selectors['Save view button'];

        $button = $this->spin(function () use ($selector) {
            return $this->find('css', $selector);
        }, sprintf('Save view button not found (%s).', $selector));

        $button->click();
    }

    /**
     * Remove the current view
     *
     * @throws TimeoutException
     */
    public function removeView()
    {
        $this->openSecondaryActions();
        $selector = $this->selectors['Remove view button'];

        $button = $this->spin(function () use ($selector) {
            return $this->find('css', $selector);
        }, sprintf('Remove view button not found (%s).', $selector));

        $this->spin(function () use ($button) {
            $modal = $this->find('css', '.modal');
            if ($modal !== null && $modal->isVisible()) {
                return true;
            };

            $button->click();

            return false;
        }, 'Can not show the validation modal after click on remove view');
    }

    /**
     * Is the remove button available for the current view ?
     */
    public function isViewDeletable()
    {
        $this->openSecondaryActions();
        $selector = $this->selectors['Remove view button'];

        try {
            return $this->spin(function () use ($selector) {
                $button = $this->find('css', $selector);

                return $button ? true : false;
            }, sprintf('Remove view button not found (%s).', $selector));
        } catch (TimeoutException $e) {
            return false;
        }
    }

    /**
     * Is the save button available for the current view ?
     */
    public function isViewCanBeSaved()
    {
        $this->openSecondaryActions();
        $selector = $this->selectors['Save view button'];

        try {
            return $this->spin(function () use ($selector) {
                $button = $this->find('css', $selector);

                return $button ? true : false;
            }, sprintf('Save view button not found (%s).', $selector));
        } catch (TimeoutException $e) {
            return false;
        }
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
        $selector = $this->spin(function () {
            $selector = $this->find('css', $this->selectors['View type switcher']);
            if (null !== $selector) {
                if ($selector->getParent()->hasClass('open')) {
                    return $selector;
                }
                $selector->click();
            }

            return false;
        }, 'Cannot open view type switcher');

        $this->spin(function () use ($selector, $type) {
            $currentViewType = $selector->find('css', '.current-view-type');
            if (null !== $currentViewType && strtolower($currentViewType->getText()) === strtolower($type)) {
                return true;
            }

            $viewType = $this->find('css', sprintf('.view-type-item[title="%s"]', $type));
            if (null !== $viewType) {
                $viewType->click();
            }

            return false;
        }, sprintf('Cannot click element in the View Type Switcher dropdown with name "%s".', $type));
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

    /**
     * Opens the secondary actions dropdown
     */
    protected function openSecondaryActions()
    {
        $this->spin(function () {
            $element = $this->find('css', $this->selectors['Grid view secondary actions']);
            if ($element !== null) {
                if ($element->hasClass('open')) {
                    return true;
                } else {
                    $element->click();
                }
            }

            return false;
        }, 'Can not open the grid view selector secondary actions');
    }
}
