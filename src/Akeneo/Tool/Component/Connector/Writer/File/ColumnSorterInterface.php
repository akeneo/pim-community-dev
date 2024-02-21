<?php

namespace Akeneo\Tool\Component\Connector\Writer\File;

/**
 * Reorder columns for export
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ColumnSorterInterface
{
    /**
     * @param array $columns
     * @param array $context
     *
     * @return array
     */
    public function sort(array $columns, array $context = []);
}
