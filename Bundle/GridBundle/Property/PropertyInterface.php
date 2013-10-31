<?php

namespace Oro\Bundle\GridBundle\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;

interface PropertyInterface
{
    /**
     * Get value name
     *
     * @return string
     */
    public function getName();

    /**
     * Get field value from data
     *
     * @param ResultRecordInterface $record
     * @return mixed
     */
    public function getValue(ResultRecordInterface $record);
}
