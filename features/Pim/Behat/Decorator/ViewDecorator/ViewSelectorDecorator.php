<?php

namespace Pim\Behat\Decorator\ViewDecorator;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to manipulate view selector
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViewSelectorDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'View list' => '.ui-multiselect-menu.highlight-hover',
    ];

    /**
     * Find a view in the list
     *
     * @return NodeElement|null
     */
    public function showViewList()
    {
        $this->find('css', 'button.pimmultiselect')->click();
    }

   /**
    * Clicks creates the view button
    */
    public function createView()
    {
        $this->find('css', '#create-view')->click();
    }

    /**
     * Update view button
     */
    public function updateView()
    {
        $this->find('css', '#update-view')->click();
    }

    /**
     * Delete the view button
     */
    public function deleteView()
    {
        $this->find('css', '#remove-view')->click();
    }

    /**
     * Click on view in the view select
     *
     * @param string $viewLabel
     */
    public function applyView($viewLabel)
    {
        $this->findView($viewLabel)->click();
    }

    /**
     * Finds the view with label in view list
     *
     * @param $viewLabel
     *
     * @return NodeElement|null
     *
     * @throws \Context\Spin\TimeoutException
     */
    public function findView($viewLabel)
    {
        $viewList = $this
            ->find('xpath', 'ancestor::body')
            ->find('css', $this->selectors['View list']);

        return $this->spin(function () use ($viewList, $viewLabel) {
            return $viewList->find('css', sprintf('label:contains("%s")', $viewLabel));
        }, sprintf('Impossible to find view "%s"', $viewLabel));
    }
}
