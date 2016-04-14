<?php

namespace Pim\Behat\Decorator\NodeDecorator\GridDecorator;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to add action features to the grid
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ActionDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Grid'         => 'table.grid',
        'Grid toolbar' => 'div.grid-toolbar',
        'Mass Edit'    => '.mass-actions-panel .action i.icon-edit',
        'Configure columns'     => ['css' => 'a:contains("Columns")']
    ];

    /**
     * @param string $element
     * @param string $actionName
     *
     * @return NodeElement|null
     */
    public function findAction($element, $actionName)
    {
        $rowElement = $this->getRow($element);
        $action     = $rowElement->find('css', sprintf('a.action[title="%s"]', $actionName));

        return $action;
    }

    /**
     * @param string $element
     * @param string $actionName
     */
    public function clickOnAction($element, $actionName)
    {
        $action = $this->findAction($element, $actionName);

        if (!$action) {
            throw new \InvalidArgumentException(
                sprintf('Could not find action "%s".', $actionName)
            );
        }

        $action->click();
    }

    /**
     * Press the mass edit button
     */
    public function massEdit()
    {
        $button = $this->find('css', $this->selectors['Mass Edit']);
        $parent = $button->getParent();

        if (null === $parent) {
            throw new \InvalidArgumentException('"Mass edit" button not found');
        }

        $this->pressButton($parent->getText());
    }

    /**
     * Press the mass delete button
     */
    public function massDelete()
    {
        $this->pressButton('Delete');
    }

    /**
     * Press the sequential edit button
     */
    public function sequentialEdit()
    {
        $this->spin(function () {
            $this->pressButton('Sequential Edit');

            return true;
        });
    }

    /**
     * Select all rows
     */
    public function selectAll()
    {
        $selector = $this->getDropdownSelector();

        $allBtn = $this->spin(function () use ($selector) {
            return $selector->find('css', 'button:contains("All")');
        }, '"All" button on dropdown row selector not found');

        $allBtn->click();
    }

    /**
     * Select all visible rows
     */
    public function selectAllVisible()
    {
        $this->clickOnDropdownSelector('All visible');
    }

    /**
     * Unselect all rows
     */
    public function selectNone()
    {
        $this->clickOnDropdownSelector('None');
    }

    /**
     * Sort rows by a column in the specified order
     *
     * @param string $columnName
     * @param string $order
     */
    public function sortBy($columnName, $order = 'ascending')
    {
        $sorter = $this->getColumnSorter($columnName);
        if ($sorter->getParent()->getAttribute('class') !== strtolower($order)) {
            $sorter->click();
        }
    }

    /**
     * Click on the refresh button of the datagrid toolbar
     */
    public function clickOnRefreshButton()
    {
        $refreshBtn = $this->spin(function () {
            return $this
                ->find('css', $this->selectors['Grid toolbar'])
                ->find('css', sprintf('a:contains("%s")', 'Refresh'));
        }, 'Refresh button not found');

        $refreshBtn->click();
    }

    /**
     * Click on the reset button of the datagrid toolbar
     */
    public function clickOnResetButton()
    {
        $resetBtn = $this->spin(function () {
            return $this
                ->find('css', $this->selectors['Grid toolbar'])
                ->find('css', sprintf('a:contains("%s")', 'Reset'));
        }, 'Reset button not found');

        $resetBtn->click();
    }

    /**
     * Predicate to know if a column is sorted and ordered as we want
     *
     * @param string $columnName
     * @param string $order
     *
     * @return bool
     */
    public function isSortedAndOrdered($columnName, $order)
    {
        $order = strtolower($order);
        if ($this->getColumnHeader($columnName)->getAttribute('class') !== $order) {
            return false;
        }

        $values = $this->getValuesInColumn($columnName);
        $values = $this->formatColumnValues($values);

        $sortedValues = $values;

        if ($order === 'ascending') {
            sort($sortedValues, SORT_NATURAL | SORT_FLAG_CASE);
        } else {
            rsort($sortedValues, SORT_NATURAL | SORT_FLAG_CASE);
        }

        return $sortedValues === $values;
    }

    /**
     * Open the column configuration popin
     */
    public function openColumnsPopin()
    {
        $this->find('css', $this->selectors['Configure columns'])->click();
    }

    /**
     * Hide a grid column
     *
     * @param string $column
     */
    public function hideColumn($column)
    {
        return $this->find('css', $this->selectors['Configuration Popin'])->hideColumn($column);
    }

    /**
     * Move a grid column
     *
     * @param string $source
     * @param string $target
     */
    public function moveColumn($source, $target)
    {
        return $this->find('css', $this->selectors['Configuration Popin'])->moveColumn($source, $target);
    }

    /**
     * Overriden for compatibility with links
     *
     * @param string $locator
     *
     * @throws ElementNotFoundException
     */
    public function pressButton($locator)
    {
        $button = $this->find(
            'named',
            [
                'link',
                $this->getSession()->getSelectorsHandler()->xpathLiteral($locator)
            ]
        );

        if (null === $button) {
            throw new ElementNotFoundException($this->getSession(), 'button', 'id|name|title|alt|value', $locator);
        }

        $button->click();
    }

    /**
     * Format column values before sorting
     *
     * @param string[] $values
     *
     * @return string[]
     */
    protected function formatColumnValues(array $values)
    {
        $cleanValues = [];

        foreach ($values as $key => $value) {
            $clean = trim($value);

            if (false !== $timestamp = strtotime($clean)) {
                $clean = date('Ymd-H:i:s', $timestamp);
            }

            $cleanValues[$key] = $clean;
        }

        return $cleanValues;
    }

    /**
     * Get the dropdown row selector
     *
     * @return NodeElement
     */
    protected function getDropdownSelector()
    {
        return $this->spin(function () {
            return $this->find('css', $this->selectors['Grid'])->find('css', 'th .btn-group');
        }, 'Grid dropdown row selector not found');
    }

    /**
     * Click on an item of the dropdown selector
     *
     * @param string $item
     */
    protected function clickOnDropdownSelector($item)
    {
        $selector = $this->getDropdownSelector();

        $dropdown = $this->spin(function () use ($selector) {
            return $selector->find('css', 'button.dropdown-toggle');
        }, 'Dropdown row selector not found');

        $dropdown->click();

        $listItem = $this->spin(function () use ($dropdown, $item) {
            return $dropdown->getParent()->find('css', sprintf('li:contains("%s") a', $item));
        }, sprintf('Item "%s" of dropdown row selector not found', $item));

        $listItem->click();
    }
}
