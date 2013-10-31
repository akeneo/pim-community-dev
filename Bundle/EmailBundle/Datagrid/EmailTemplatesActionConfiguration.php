<?php

namespace Oro\Bundle\EmailBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

class EmailTemplatesActionConfiguration
{
    /**
     * Returns callback for configuration of grid/actions visibility per row
     *
     * @return callable
     */
    public static function getClosure()
    {
        return function (ResultRecordInterface $record) {
            if ($record->getValue('isSystem')) {
                return array('delete' => false);
            }
        };
    }
}
