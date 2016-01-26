<?php

namespace Context\Page\Base;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\NodeElement;
use Context\Spin\TimeoutException;

/**
 * Page object for datagrid generated by the OroGridBundle
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Grid extends Index
{
    const FILTER_CONTAINS         = 1;
    const FILTER_DOES_NOT_CONTAIN = 2;
    const FILTER_IS_EQUAL_TO      = 3;
    const FILTER_STARTS_WITH      = 4;
    const FILTER_ENDS_WITH        = 5;
    const FILTER_IS_EMPTY         = 'empty';
    const FILTER_IN_LIST          = 'in';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            [
                'Grid container'        => ['css' => '.grid-container'],
                'Grid'                  => ['css' => 'table.grid'],
                'Grid content'          => ['css' => 'table.grid tbody'],
                'Filters'               => ['css' => 'div.filter-box'],
                'Grid toolbar'          => ['css' => 'div.grid-toolbar'],
                'Manage filters'        => ['css' => 'div.filter-list'],
                'Configure columns'     => ['css' => 'a:contains("Columns")'],
                'View selector'         => ['css' => '#view-selector'],
                'Views list'            => ['css' => '.ui-multiselect-menu.highlight-hover'],
                'Select2 results'       => ['css' => '#select2-drop .select2-results'],
                'Mass Edit'             => ['css' => '.mass-actions-panel .action i.icon-edit'],
                'Main context selector' => [
                    'css'        => '#container',
                    'decorators' => [
                        'Pim\Behat\Decorator\ContextSwitcherDecorator'
                    ]
                ]
            ],
            $this->elements
        );
    }

    /**
     * Returns the currently visible grid, if there is one
     *
     * @return NodeElement
     */
    public function getGrid()
    {
        return $this->spin(
            function () {
                $modal = $this->getElement('Body')->find('css', $this->elements['Dialog']['css']);
                if (null !== $modal && $modal->isVisible()) {
                    return $modal->find('css', $this->elements['Grid']['css']);
                }

                return $this->getElement('Container')->find('css', $this->elements['Grid']['css']);
            },
            'No visible grid found'
        );
    }

    /**
     * Returns the grid body
     *
     * @return NodeElement|null
     */
    public function getGridContent()
    {
        return $this->getGrid()->find('css', 'tbody');
    }

    /**
     * Get a row from the grid containing the value asked
     *
     * @param string $value
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
     */
    public function getRow($value)
    {
        $value   = str_replace('"', '', $value);

        try {
            $gridRow = $this->getGridContent()->find('css', sprintf('tr td:contains("%s")', $value));
        } catch (TimeoutException $e) {
            $gridRow = null;
        }

        if (null === $gridRow) {
            throw new \InvalidArgumentException(
                sprintf('Couldn\'t find a row for value "%s"', $value)
            );
        }

        return $gridRow->getParent();
    }

    /**
     * Check if the grid contains a row with the specified value
     *
     * @param string $value
     *
     * @return bool
     */
    public function hasRow($value)
    {
        $value = str_replace('"', '', $value);

        return null !== $this->getGridContent()->find('css', sprintf('tr td:contains("%s")', $value));
    }

    /**
     * @param string $element
     * @param string $actionName
     *
     * @throws \InvalidArgumentException
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
     * @param string               $filterName The name of the filter
     * @param string               $value      The value to filter by
     * @param bool|string          $operator   If false, no operator will be selected
     * @param DriverInterface|null $driver     Required to filter by multiple choices
     *
     * @throws \InvalidArgumentException
     */
    public function filterBy($filterName, $value, $operator = false, DriverInterface $driver = null)
    {
        $filter = $this->getFilter($filterName);
        $this->openFilter($filter);

        if ($elt = $filter->find('css', 'select')) {
            if ($elt->getText() === "between not between more than less than is empty") {
                $this->filterByDate($filter, $value, $operator);
            } elseif ($elt->getParent()->find('css', 'button.ui-multiselect')) {
                if (!$driver || !$driver instanceof Selenium2Driver) {
                    throw new \InvalidArgumentException('Selenium2Driver is required to filter by a choice filter');
                }
                $values = explode(',', $value);

                foreach ($values as $value) {
                    $driver->executeScript(
                        sprintf(
                            "$('.ui-multiselect-menu:visible input[title=\"%s\"]').click().trigger('click');",
                            $value
                        )
                    );
                    sleep(1);
                }

                // Uncheck the 'All' option
                if (!in_array('All', $values)) {
                    $driver->executeScript(
                        "var all = $('.ui-multiselect-menu:visible input[title=\"All\"]');" .
                        "if (all.length && all.is(':checked')) { all.click().trigger('click'); }"
                    );
                }
            }
        } elseif ($elt = $filter->find('css', 'div.filter-criteria')) {
            $results = $this->getElement('Select2 results');
            $select2 = $filter->find('css', '.select2-input');

            if (false !== $operator) {
                $filter->find('css', 'button.dropdown-toggle')->click();
                $filter->find('css', sprintf('[data-value="%s"]', $operator))->click();
            }

            if (null !== $results && null !== $select2) {
                if (in_array($value, ['empty', 'is empty'])) {
                    // Allow passing 'empty' as value too (for backwards compability with existing scenarios)
                    $filter->find('css', 'button.dropdown-toggle')->click();
                    $filter->find('css', '[data-value="empty"]')->click();
                } else {
                    $values = explode(',', $value);
                    foreach ($values as $value) {
                        $driver->getWebDriverSession()
                            ->element('xpath', $select2->getXpath())
                            ->postValue(['value' => [$value]]);
                        sleep(2);
                        $results->find('css', 'li')->click();
                        sleep(2);
                    }
                }
            } elseif ($value !== false) {
                $elt->fillField('value', $value);
            }

            $filter->find('css', 'button.filter-update')->click();
        } else {
            throw new \InvalidArgumentException(
                sprintf('Filtering by "%s" is not yet implemented"', $filterName)
            );
        }
    }

    /**
     * @param NodeElement $filter
     * @param string      $value
     * @param string      $operator
     */
    protected function filterByDate($filter, $value, $operator)
    {
        $elt = $filter->find('css', 'select');
        if ('empty' === $operator) {
            $elt->selectOption('is empty');
        } else {
            $elt->selectOption($operator);
        }

        $filter->find('css', 'button.filter-update')->click();
    }

    /**
     * Count all rows in the grid
     *
     * @return int
     */
    public function countRows()
    {
        try {
            return count($this->getRows());
        } catch (\InvalidArgumentException $e) {
            return 0;
        }
    }

    /**
     * Indicate if the grid is empty (i.e. has the "No records found" div)
     *
     * @return bool
     */
    public function isGridEmpty()
    {
        $container = $this->getElement('Grid container');
        $noDataDiv = $this->spin(function () use ($container) {
            return $container->find('css', '.no-data');
        }, '"No data" div not found');

        return $noDataDiv->isVisible();
    }

    /**
     * Get toolbar count
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    public function getToolbarCount()
    {
        $pagination = $this
            ->getElement('Grid toolbar')
            ->find('css', 'div label.dib:contains("record")');

        // If pagination not found or is empty, count rows
        if (!$pagination || !$pagination->getText()) {
            return $this->countRows();
        }

        if (preg_match('/([0-9][0-9 ]*) records?$/', $pagination->getText(), $matches)) {
            return $matches[1];
        } else {
            throw new \InvalidArgumentException('Impossible to get count of datagrid records');
        }
    }

    /**
     * @param int $num
     */
    public function changePageSize($num)
    {
        assertContains($num, [10, 25, 50, 100], 'Only 10, 25, 50 and 100 records per page are available');

        $element = $this->spin(function () {
            return $this->getGrid()
                ->getParent()
                ->getParent()
                ->getParent()
                ->find('css', $this->elements['Grid toolbar']['css']);
        }, 'Cannot find the grid toolbar');

        $dropdownButton = $this->spin(function () use ($element) {
            return $element->find('css', '.page-size button.dropdown-toggle');
        }, 'Cannot find the change page size button');
        $dropdownButton->click();

        $element->find('css', sprintf('ul.dropdown-menu li a:contains("%d")', $num))->click();
    }

    /**
     * @param int $num
     */
    public function pageSizeIs($num)
    {
        assertContains($num, [10, 25, 50, 100], 'Only 10, 25, 50 and 100 records per page are available');
        $element = $this->getElement('Grid toolbar')->find('css', '.page-size');
        assertNotNull($element->find('css', sprintf('button:contains("%d")', $num)));
    }

    /**
     * Get the text in the specified column of the specified row
     *
     * @param string $column
     * @param string $row
     *
     * @return string
     */
    public function getColumnValue($column, $row)
    {
        return $this->getColumnNode($column, $row)->getText();
    }

    /**
     * Get the node in the specified column of the specified row
     *
     * @param string $column
     * @param string $row
     *
     * @return NodeElement
     */
    public function getColumnNode($column, $row)
    {
        return $this->getRowCell(
            $this->getRow($row),
            $this->getColumnPosition($column, true, true)
        );
    }

    /**
     * Get an array of values in the specified column
     *
     * @param string $columnName
     *
     * @return array
     */
    public function getValuesInColumn($columnName)
    {
        $position = $this->getColumnPosition($columnName, true, true);
        $rows     = $this->getRows();
        $values   = [];

        foreach ($rows as $row) {
            $cell = $this->getRowCell($row, $position);
            if ($span = $cell->find('css', 'span')) {
                $values[] = (string) (strpos($span->getAttribute('class'), 'success') !== false);
            } else {
                $values[] = $cell->getText();
            }
        }

        return $values;
    }

    /**
     * Get an image element inside a grid cell
     *
     * @param string $column
     * @param string $row
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
     */
    public function getCellImage($column, $row)
    {
        $cell  = $this->getColumnNode($column, $row);
        $image = $cell->find('css', 'img');
        if (null === $image) {
            throw new \InvalidArgumentException(
                sprintf('Column "%s" of row "%s" contains no image.', $column, $row)
            );
        }

        return $image;
    }

    /**
     * @param string $columnName
     * @param bool   $withHidden
     * @param bool   $withActions
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    public function getColumnPosition($columnName, $withHidden, $withActions)
    {
        $headers = $this->getColumnHeaders($withHidden, $withActions);
        foreach ($headers as $position => $header) {
            if (strtolower($columnName) === strtolower($header->getText())) {
                return $position;
            }
        }

        throw new \InvalidArgumentException(
            sprintf('Could not find a column header "%s"', $columnName)
        );
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
        $notOrdered = $this->spin(function () use ($columnName, $order) {
            return $this->getColumnHeader($columnName)->getAttribute('class') !== $order;
        }, 'The column is not well ordered');
        if ($notOrdered) {
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
     * Count columns in datagrid
     *
     * @return int
     */
    public function countColumns()
    {
        return count($this->getColumnHeaders(false, false));
    }

    /**
     * Get column sorter
     *
     * @param string $columnName
     *
     * @return NodeElement
     */
    public function getColumnSorter($columnName)
    {
        $header = $this->getColumnHeader($columnName);

        return $this->spin(
            function () use ($header) {
                return $header->find('css', 'a');
            },
            sprintf('Column %s is not sortable', $columnName)
        );
    }

    /**
     * Get grid filter from label name
     *
     * @param string $filterName
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
     */
    public function getFilter($filterName)
    {
        $filter = $this->spin(function () use ($filterName) {
            if (strtolower($filterName) === 'channel') {
                $filter = $this->getElement('Grid toolbar')->find('css', 'div.filter-item');
            } else {
                $filter = $this->getElement('Filters')->find('css', sprintf('div.filter-item:contains("%s")', $filterName));
            }

            return $filter;
        }, sprintf('Couldn\'t find a filter with name "%s"', $filterName));

        return $filter;
    }

    /**
     * @param string $filterName
     *
     * @return bool
     */
    public function isFilterAvailable($filterName)
    {
        $this->clickFiltersList();

        $filterElement = $this
            ->getElement('Manage filters')
            ->find('css', sprintf('label:contains("%s")', $filterName));

        return null !== $filterElement;
    }

    /**
     * Show a filter from the management list
     *
     * @param string $filterName
     */
    public function showFilter($filterName)
    {
        $this->clickFiltersList();
        $this->activateFilter($filterName);
        $this->clickFiltersList();
    }

    /**
     * Make sure a filter is visible
     *
     * @param string $filterName
     */
    public function assertFilterVisible($filterName)
    {
        if (!$this->getFilter($filterName)->isVisible()) {
            throw new \InvalidArgumentException(
                sprintf('Filter "%s" is not visible', $filterName)
            );
        }
    }

    /**
     * Hide a filter from the management list
     *
     * @param string $filterName
     */
    public function hideFilter($filterName)
    {
        $this->clickFiltersList();
        $this->deactivateFilter($filterName);
        $this->clickFiltersList();
    }

    /**
     * Click on the reset button of the datagrid toolbar
     */
    public function clickOnResetButton()
    {
        $resetBtn = $this->spin(function () {
            return $this
                ->getElement('Grid toolbar')
                ->find('css', sprintf('a:contains("%s")', 'Reset'));
        }, 'Reset button not found');

        $resetBtn->click();
    }

    /**
     * Click on the refresh button of the datagrid toolbar
     */
    public function clickOnRefreshButton()
    {
        $refreshBtn = $this->spin(function () {
            return $this
                ->getElement('Grid toolbar')
                ->find('css', sprintf('a:contains("%s")', 'Refresh'));
        }, 'Refresh button not found');

        $refreshBtn->click();
    }

    /**
     * Click on view in the view select
     *
     * @param string $viewLabel
     *
     * @throws \InvalidArgumentException
     */
    public function applyView($viewLabel)
    {
        $view = $this->spin(function () use ($viewLabel) {
            return $this->findView($viewLabel);
        }, sprintf('Impossible to find view "%s"', $viewLabel));

        $view->click();
    }

    /**
     * Find a view in the list
     *
     * @param string $viewLabel
     *
     * @return NodeElement|null
     */
    public function findView($viewLabel)
    {
        $this
            ->getElement('View selector')
            ->getParent()
            ->find('css', 'button.pimmultiselect')
            ->click();

        return $this
            ->getElement('Views list')
            ->find('css', sprintf('label:contains("%s")', $viewLabel));
    }

    /**
     * Activate a filter
     *
     * @param string $filterName
     */
    protected function activateFilter($filterName)
    {
        try {
            if (!$this->getFilter($filterName)->isVisible()) {
                $this->clickOnFilterToManage($filterName);
            }
        } catch (TimeoutException $e) {
            $this->clickOnFilterToManage($filterName);
        }
    }

    /**
     * Deactivate filter
     *
     * @param string $filterName
     *
     * @throws \InvalidArgumentException
     */
    protected function deactivateFilter($filterName)
    {
        if ($this->getFilter($filterName)->isVisible()) {
            $this->clickOnFilterToManage($filterName);
        }

        if ($this->getFilter($filterName)->isVisible()) {
            throw new \InvalidArgumentException(
                sprintf('Filter "%s" is visible', $filterName)
            );
        }
    }

    /**
     * Click on a filter in filter management list
     *
     * @param string $filterName
     */
    protected function clickOnFilterToManage($filterName)
    {
        $filterElement = $this->spin(function () use ($filterName) {
            return $this
                ->getElement('Manage filters')
                ->find('css', sprintf('label:contains("%s")', $filterName));
        }, sprintf('Impossible to activate filter "%s"', $filterName));

        $filterElement->click();
    }

    /**
     * Open/close filters list
     */
    protected function clickFiltersList()
    {
        $filterList = $this->spin(function () {
            return $this
                ->getElement('Filters')
                ->find('css', '#add-filter-button');
        }, 'Impossible to find filter list');

        $filterList->click();
    }

    /**
     * Select a row
     *
     * @param string $value
     * @param bool   $check
     *
     * @return NodeElement|null
     */
    public function selectRow($value, $check = true)
    {
        $row      = $this->getRow($value);
        $checkbox = $this->spin(function () use ($row) {
            return $row->find('css', 'input[type="checkbox"]');
        }, sprintf('Couldn\'t find a checkbox for row "%s"', $value));

        if ($check) {
            $checkbox->check();
        } else {
            $checkbox->uncheck();
        }

        return $checkbox;
    }

    /**
     * @param NodeElement $row
     * @param int         $position
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
     */
    protected function getRowCell($row, $position)
    {
        // $row->findAll('css', 'td') will not work in the case of nested table (like proposals changes)
        // because we only need to find the direct children cells
        $cells = $row->findAll('xpath', './td');
        if (!isset($cells[$position])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Trying to access cell %d of a row which has %d cell(s).',
                    $position + 1,
                    count($cells)
                )
            );
        }

        return $cells[$position];
    }

    /**
     * Open the filter
     *
     * @param NodeElement $filter
     *
     * @throws \InvalidArgumentException
     */
    public function openFilter(NodeElement $filter)
    {
        $element = $this->spin(function () use ($filter) {
            return $filter->find('css', 'button');
        }, 'Impossible to open filter or maybe its type is not yet implemented');

        $element->click();
    }

    /**
     * Get column headers
     *
     * @param bool $withHidden
     * @param bool $withActions
     *
     * @return NodeElement[]
     */
    protected function getColumnHeaders($withHidden = false, $withActions = true)
    {
        $head    = $this->getGrid()->find('css', 'thead');
        $headers = $head->findAll('css', 'th');

        if (!$withActions) {
            $headers = array_filter($headers, function ($header) {
                return false === strpos($header->getAttribute('class'), 'action-column') &&
                    false === strpos($header->getAttribute('class'), 'select-all-header-cell') &&
                    null === $header->find('css', 'input[type="checkbox"]');
            });
        }

        if ($withHidden) {
            return $headers;
        }

        $visibleHeaders = array_filter($headers, function ($header) {
            return $header->isVisible();
        });
        $visibleHeaders = array_values($visibleHeaders);

        return $visibleHeaders;
    }

    /**
     * Get column header
     *
     * @param string $columnName
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
     */
    public function getColumnHeader($columnName)
    {
        $headers = $this->getColumnHeaders(true);
        foreach ($headers as $header) {
            if (strtolower($columnName) === strtolower($header->getText())) {
                return $header;
            }
        }

        throw new \InvalidArgumentException(
            sprintf('Could not find column header "%s"', $columnName)
        );
    }

    /**
     * Get rows
     *
     * @return NodeElement[]
     */
    protected function getRows()
    {
        return $this->getGridContent()->findAll('xpath', '/tr');
    }

    /**
     * @param string $filterName The name of the price filter
     * @param string $action     Type of filtering (>, >=, etc.)
     * @param number $value      Value to filter
     * @param string $currency   Currency on which to filter
     */
    public function filterPerPrice($filterName, $action, $value, $currency)
    {
        $filter = $this->getFilter($filterName);
        $this->openFilter($filter);

        if (null !== $value) {
            $criteriaElt = $filter->find('css', 'div.filter-criteria');
            $criteriaElt->fillField('value', $value);
        }

        $buttons        = $filter->findAll('css', '.currencyfilter button.dropdown-toggle');
        $actionButton   = array_shift($buttons);
        $currencyButton = array_shift($buttons);

        // Open the dropdown menu with currency list and click on $currency line
        $currencyButton->click();
        $currencyButton->getParent()->find('css', sprintf('ul a:contains("%s")', $currency))->click();

        // Open the dropdown menu with action list and click on $action line
        $actionButton->click();
        $actionButton->getParent()->find('xpath', sprintf("//ul//a[text() = '%s']", $action))->click();

        $filter->find('css', 'button.filter-update')->click();
    }

    /**
     * @param string $filterName The name of the metric filter
     * @param string $action     Type of filtering (>, >=, etc.)
     * @param float  $value      Value to filter
     * @param string $unit       Unit on which to filter
     */
    public function filterPerMetric($filterName, $action, $value, $unit)
    {
        $filter = $this->getFilter($filterName);
        $this->openFilter($filter);

        $criteriaElt = $filter->find('css', 'div.filter-criteria');
        $criteriaElt->fillField('value', $value);

        $buttons      = $filter->findAll('css', '.metricfilter button.dropdown-toggle');
        $actionButton = array_shift($buttons);
        $unitButton   = array_shift($buttons);

        // Open the dropdown menu with unit list and click on $unit line
        $unitButton->click();
        $unitButton->getParent()->find('xpath', sprintf("//ul//a[text() = '%s']", $unit))->click();

        // Open the dropdown menu with action list and click on $action line
        $actionButton->click();
        $actionButton->getParent()->find('xpath', sprintf("//ul//a[text() = '%s']", $action))->click();

        $filter->find('css', 'button.filter-update')->click();
    }

    /**
     * @param string $filterName The name of the number filter
     * @param string $action     Type of filtering (>, >=, etc.)
     * @param float  $value      Value to filter
     */
    public function filterPerNumber($filterName, $action, $value)
    {
        $filter = $this->getFilter($filterName);
        $this->openFilter($filter);

        $criteriaElt = $filter->find('css', 'div.filter-criteria');
        $criteriaElt->fillField('value', $value);

        $buttons      = $filter->findAll('css', '.filter-criteria button.dropdown-toggle');
        $actionButton = array_shift($buttons);

        // Open the dropdown menu with action list and click on $action line
        $actionButton->click();
        $actionButton->getParent()->find('xpath', sprintf("//ul//a[text() = '%s']", $action))->click();

        $filter->find('css', 'button.filter-update')->click();
    }

    /**
     * Open the column configuration popin
     */
    public function openColumnsPopin()
    {
        $this->getElement('Configure columns')->click();
    }

    /**
     * Hide a grid column
     *
     * @param string $column
     */
    public function hideColumn($column)
    {
        return $this->getElement('Configuration Popin')->hideColumn($column);
    }

    /**
     * Move a grid column
     *
     * @param string $source
     * @param string $target
     */
    public function moveColumn($source, $target)
    {
        return $this->getElement('Configuration Popin')->moveColumn($source, $target);
    }

    /**
     * Press the mass edit button
     */
    public function massEdit()
    {
        $button = $this->getElement('Mass Edit');
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
     * Get the dropdown row selector
     *
     * @return NodeElement
     */
    protected function getDropdownSelector()
    {
        return $this->spin(function () {
            return $this->getElement('Grid')->find('css', 'th .btn-group');
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
}
