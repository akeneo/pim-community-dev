<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty as OroFieldProperty;

/**
 * Field property, able to render majority of product attribute values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldProperty extends OroFieldProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $result = $this->getBackendData($value);

        return parent::convertValue($result);
    }

    /**
     * Retrieve the relevant backend data from attribute configuration
     *
     * @param array $value
     *
     * @return array
     */
    protected function getBackendData($value)
    {
        return $value;
    }
}
