<?php

namespace Oro\Bundle\FilterBundle\Extension\Orm;

class PercentFilter extends NumberFilter
{
    /**
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        $data = parent::parseData($data);

        if ($data && is_numeric($data['value'])) {
            $data['value'] /= 100;
        }

        return $data;
    }
}
