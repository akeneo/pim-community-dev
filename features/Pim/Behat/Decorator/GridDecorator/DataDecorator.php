<?php

namespace Pim\Behat\Decorator\GridDecorator;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorator to manipulate data of the grid
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Grid toolbar'   => '.grid-toolbar',
        'Grid content'   => '.grid tbody',
        'Grid container' => '.grid-container',
    ];

    /**
     * Returns the grid body
     *
     * @return NodeElement|null
     */
    public function getGridContent()
    {
        return $this->find('css', $this->selectors['Grid content']);
    }

    /**
     * Returns the grid container
     *
     * @return NodeElement|null
     */
    public function getGridContainer()
    {
        return $this->find('css', $this->selectors['Grid container']);
    }

    /**
     * Indicate if the grid is empty (i.e. has the "No records found" div)
     *
     * @return bool
     */
    public function isGridEmpty()
    {
        $container = $this->find('css', $this->selectors['Grid container']);
        $noDataDiv = $this->spin(function () use ($container) {
            return $container->find('css', '.no-data');
        }, '"No data" div not found');

        return $noDataDiv->isVisible();
    }

    /**
     * Get a row from the grid containing the value asked
     *
     * @param string $value
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
     * Get toolbar count
     *
     * @return int
     */
    public function getToolbarCount()
    {
        $pagination = $this
            ->find('css', $this->selectors['Grid toolbar'])
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
     * Count columns in datagrid
     *
     * @return int
     */
    public function countColumns()
    {
        return count($this->getColumnHeaders(false, false));
    }

    /**
     * Get column header
     *
     * @param string $columnName
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
     * Get an image element inside a grid cell
     *
     * @param string $column
     * @param string $row
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
     * @param string $columnName
     * @param bool   $withHidden
     * @param bool   $withActions
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
     * @param NodeElement $row
     * @param int         $position
     *
     * @return NodeElement
     */
    protected function getRowCell($row, $position)
    {
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
     * Get rows
     *
     * @return NodeElement[]
     */
    protected function getRows()
    {
        return $this->getGridContent()->findAll('xpath', '/tr');
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
        $head    = $this->find('css', 'thead');
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
}
