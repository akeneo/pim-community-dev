<?php

namespace Context\Page\Base;

/**
 * Page that is with grid
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait WithGrid
{
    protected $grid;

    public function getGrid()
    {
        return $this->grid->getGrid();
    }

    public function getGridContent()
    {
        return $this->grid->getGridContent();
    }

    public function getRow($value)
    {
        return $this->grid->getRow($value);
    }

    public function clickOnAction($element, $actionName)
    {
        return $this->grid->clickOnAction($element, $actionName);
    }

    public function filterBy($filterName, $value, $operator = false)
    {
        return $this->grid->filterBy($filterName, $value, $operator);
    }

    public function countRows()
    {
        return $this->grid->countRows();
    }

    public function getGridToolbar()
    {
        return $this->grid->getGridToolbar();
    }

    public function getToolbarCount()
    {
        return $this->grid->getToolbarCount();
    }

    public function changePageSize($num)
    {
        return $this->grid->changePageSize($num);
    }

    public function getColumnValue($column, $row)
    {
        return $this->grid->getColumnValue($column, $row);
    }

    public function getValuesInColumn($column)
    {
        return $this->grid->getValuesInColumn($column);
    }

    public function getColumnPosition($column)
    {
        return $this->grid->getColumnPosition($column);
    }

    public function sortBy($column, $order = 'ascending')
    {
        return $this->grid->sortBy($column, $order);
    }

    public function isSortedAndOrdered($column, $order)
    {
        return $this->grid->isSortedAndOrdered($column, $order);
    }

    public function countColumns()
    {
        return $this->grid->countColumns();
    }

    public function getColumn($columnName)
    {
        return $this->grid->getColumn($columnName);
    }

    public function getColumnSorter($columnName)
    {
        return $this->grid->getColumnSorter($columnName);
    }

    public function getFilters()
    {
        return $this->grid->getFilters();
    }

    public function getFilter($filterName)
    {
        return $this->grid->getFilter($filterName);
    }

    public function showFilter($filterName)
    {
        return $this->grid->showFilter($filterName);
    }

    public function hideFilter($filterName)
    {
        return $this->grid->hideFilter($filterName);
    }

    public function clickOnResetButton()
    {
        return $this->grid->clickOnResetButton();
    }

    public function clickOnRefreshButton()
    {
        return $this->grid->clickOnRefreshButton();
    }

    public function getManageFilters()
    {
        return $this->grid->getManageFilters();
    }

    public function selectRow($value)
    {
        return $this->grid->selectRow($value);
    }

    public function getColumnHeaders($withHidden = false, $withActions = true)
    {
        return $this->grid->getColumnHeaders($withHidden, $withActions);
    }

    public function filterPerPrice($filterName, $action, $value, $currency)
    {
        return $this->grid->filterPerPrice($filterName, $action, $value, $currency);
    }

    public function filterPerMetric($filterName, $action, $value, $unit)
    {
        return $this->grid->filterPerMetric($filterName, $action, $value, $unit);
    }

    public function openColumnsPopin()
    {
        return $this->grid->openColumnsPopin();
    }
}
