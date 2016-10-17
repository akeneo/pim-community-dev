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
        'Dialog grid'   => '.modal',
        'Grid'          => 'table.grid',
        'View selector' => '.grid-view-selector .select2-container',
        'Save button'   => '.save-button [data-action="save"]:not(.hide)'
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
            return $this->find('css', $this->selectors['View selector']);
        }, 'View selector not found.');

        return $this->decorate($viewSelector, $this->viewSelectorDecorators);
    }

    /**
     * @param string $button
     */
    public function clickCreateOnButton($button)
    {
        $button = $this->spin(function () use ($button) {
            return $this->find('css', sprintf('.create-button:contains("%s")', $button));
        }, sprintf('Button "%s" not found.', $button));

        $button->click();
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
        $viewSelector = $this->getViewSelector()->getParent();

        $saveButton = $this->spin(function () use ($viewSelector) {
            return $viewSelector->find('css', $this->selectors['Save button']);
        }, 'Save button not found.');

        $saveButton->click();
    }

    /**
     * Remove the datagrid view with the given $viewLabel
     *
     * @param $viewLabel
     *
     * @throws TimeoutException
     */
    public function removeView($viewLabel)
    {
        $widget = $this->getViewSelector()->getWidget();

        $row = $this->spin(function () use ($widget, $viewLabel) {
            return $widget->find('css', sprintf('.select2-result-label:contains("%s")', $viewLabel));
        }, sprintf('Row "%s" in view selector not found.', $viewLabel));

        $deleteButton = $this->spin(function () use ($row) {
            return $this->find('css', '[data-action="prompt-deletion"]');
        }, sprintf('Delete button not found on row "%s"', $viewLabel));

        $deleteButton->click();
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
}
