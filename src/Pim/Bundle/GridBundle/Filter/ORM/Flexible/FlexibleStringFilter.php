<?php

namespace Pim\Bundle\GridBundle\Filter\ORM\Flexible;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\ORM\StringFilter;

class FlexibleStringFilter extends AbstractFlexibleFilter
{
    /**
     * @var string
     */
    protected $parentFilterClass = 'Oro\\Bundle\\GridBundle\\Filter\\ORM\\StringFilter';

    /**
     * @var StringFilter
     */
    protected $parentFilter;

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        $data = $this->parentFilter->parseData($data);
        if (!$data) {
            return;
        }

        $operator = $this->parentFilter->getOperator($data['type']);

        if ('=' == $operator) {
            $value = $data['value'];
        } else {
            $value = sprintf($this->parentFilter->getFormatByComparisonType($data['type']), $data['value']);
        }

        // apply filter
        $this->applyFlexibleFilter($proxyQuery, $field, $value, $operator);
    }
}
