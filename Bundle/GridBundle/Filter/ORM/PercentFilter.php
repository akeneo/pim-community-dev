<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;

class PercentFilter extends NumberFilter
{
    /**
     * @param mixed $data
     * @return array|bool
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
