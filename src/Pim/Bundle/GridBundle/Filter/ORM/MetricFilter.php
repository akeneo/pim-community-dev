<?php

namespace Pim\Bundle\GridBundle\Filter\ORM;

use Pim\Bundle\FilterBundle\Form\Type\Filter\MetricFilterType;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\ORM\NumberFilter;

/**
 * Metric filter related to flexible entities
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class MetricFilter extends NumberFilter
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => MetricFilterType::NAME
        );
    }

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        var_dump($data);
    }

    /**
     * Overriden to validate metric unit
     *
     * {@inheritdoc}
     */
    public function parseData($data)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return parent::getRenderSettings();
    }
}
