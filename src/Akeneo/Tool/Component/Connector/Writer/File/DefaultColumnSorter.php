<?php

namespace Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Connector\ArrayConverter\FieldSplitter;

/**
 * Reorder columns before export
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DefaultColumnSorter implements ColumnSorterInterface
{
    /** @var FieldSplitter */
    protected $fieldSplitter;

    /** @var array */
    protected $firstDefaultColumns;

    /**
     * @param FieldSplitter $fieldSplitter
     * @param array $firstDefaultColumns
     */
    public function __construct(FieldSplitter $fieldSplitter, array $firstDefaultColumns)
    {
        $this->fieldSplitter = $fieldSplitter;
        $this->firstDefaultColumns = $firstDefaultColumns;
    }

    /**
     * {@inheritdoc}
     */
    public function sort(array $unsortedColumns, array $context = [])
    {
        $mainColumns = [];
        $trailingColumns = [];
        $additionalColumns = [];

        foreach ($unsortedColumns as $column) {
            if ($this->isInOrderConf($column)) {
                $mainColumns[] = $column;
            } else if ($this->isTrailingColumn($column)) {
                $trailingColumns[] = $column;
            } else {
                $additionalColumns[] = $column;
            }
        }

        usort($mainColumns, [$this, 'compare']);
        natcasesort($additionalColumns);
        natcasesort($trailingColumns);

        return array_merge($mainColumns, $additionalColumns, $trailingColumns);
    }

    /**
     * @param string $column
     *
     * @return bool
     */
    protected function isInOrderConf($column)
    {
        $splitedColumn = $this->fieldSplitter->splitFieldName($column);
        $column = is_array($splitedColumn) ? $splitedColumn[0] : $column;

        return in_array($column, $this->firstDefaultColumns);
    }

    /**
     * @param string $a
     * @param string $b
     *
     * @return int
     */
    protected function compare($a, $b)
    {
        $a = $this->getColumnCode($a);
        $b = $this->getColumnCode($b);

        return array_search($a, $this->firstDefaultColumns) - array_search($b, $this->firstDefaultColumns);
    }

    /**
     * @param string $column
     *
     * @return string
     */
    protected function getColumnCode($column)
    {
        $splitedColumn = $this->fieldSplitter->splitFieldName($column);

        return is_array($splitedColumn) ? $splitedColumn[0] : $column;
    }

    // @fixme Find a better way to put the quality score columns at the end?
    private function isTrailingColumn(string $column): bool
    {
        return 0 === strpos($column, 'quality_score-');
    }
}
