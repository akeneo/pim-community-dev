<?php

namespace Context\Page\Element;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Datagrid configuration popin
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurationPopin extends Element
{
    use SpinCapableTrait;

    protected $selector = ['css' => '.modal'];

    /**
     * @param string[] $labels
     */
    public function addColumns($labels)
    {
        $dropZone = $this->spin(function () {
            return $this->find('css', '#column-selection');
        }, 'Cannot find the drop zone to add columns');

        $this->loadAllColumns();

        foreach ($labels as $label) {
            $item = $this->getItemForLabel($label);
            $this->dragElementTo($item, $dropZone);
        }
    }

    /**
     * Run the infinite scroll on the column list
     */
    public function loadAllColumns()
    {
        return $this->spin(function () {
            $this->getSession()->executeScript('$("[data-columns]").scrollTop(10000);');

            return $this->find('css', '[data-columns].more') === null;
        }, 'Cannot load all columns in list');
    }

    /**
     * @param string[] $labels
     */
    public function removeColumns($labels)
    {
        $dropZone = $this->spin(function () {
            return $this->find('css', '#column-list');
        }, 'Cannot find the drop zone to remove columns');

        foreach ($labels as $label) {
            $item = $this->getItemForLabel($label);
            $this->dragElementTo($item, $dropZone);
        }
    }

    /**
     * Apply the configuration
     */
    public function apply()
    {
        $this->find('css', '.ok')->click();
    }

    /**
     * Drags an element on another one.
     * Works better than the standard dragTo.
     *
     * @param $element
     * @param $dropZone
     */
    protected function dragElementTo($element, $dropZone)
    {
        $session = $this->getSession()->getDriver()->getWebDriverSession();

        $from = $session->element('xpath', $element->getXpath());
        $to = $session->element('xpath', $dropZone->getXpath());

        $session->moveto(['element' => $from->getID()]);
        $session->buttondown('');
        $session->moveto(['element' => $to->getID()]);
        $session->buttonup('');
    }

    /**
     * @param string $label
     *
     * @throws TimeoutException
     *
     * @return NodeElement
     */
    protected function getItemForLabel($label)
    {
        return $this->spin(function () use ($label) {
            $items = $this->findAll('css', '.ui-sortable-handle');

            foreach ($items as $item) {
                if (strtolower($label) === strtolower($item->getText())) {
                    return $item;
                }
            }

            return false;
        }, sprintf('Cannot find the column "%s" in the list', $label));
    }
}
