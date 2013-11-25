<?php

namespace Pim\Bundle\GridBundle\Filter\ORM\Flexible;

use Oro\Bundle\GridBundle\Filter\ORM\NumberFilter;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;

class FlexibleBooleanFilter extends AbstractFlexibleFilter
{
    /**
     * @var string
     */
    protected $parentFilterClass = 'Oro\\Bundle\\GridBundle\\Filter\\ORM\\BooleanFilter';

    /**
     * @var NumberFilter
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

        $value = ($data['value'] == BooleanFilterType::TYPE_YES) ? 1 : 0;

        $this->applyFlexibleFilter($proxyQuery, $field, $value, '=');
    }
}
