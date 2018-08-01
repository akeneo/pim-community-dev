<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

/**
 * Maps column names, for instance, 'my-custom-name' to 'family'
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ColumnsMapper
{
    /**
     * @param array $row
     * @param array $mapping
     *
     * @return array mapped row
     */
    public function map(array $row, array $mapping)
    {
        if (empty($mapping)) {
            return $row;
        }

        foreach ($mapping as $originFieldName => $destFieldName) {
            if (isset($row[$originFieldName]) && $originFieldName !== $destFieldName) {
                $row[$destFieldName] = $row[$originFieldName];
                unset($row[$originFieldName]);
            }
        }

        return $row;
    }
}
