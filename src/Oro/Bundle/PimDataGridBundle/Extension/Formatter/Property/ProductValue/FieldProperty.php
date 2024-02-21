<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\ProductValue;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
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
        return $value['data'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRawValue(ResultRecordInterface $record)
    {
        try {
            $productValuePath = sprintf('[values][%s]', $this->get(self::NAME_KEY));
            $value = $record->getValue($productValuePath);
        } catch (\LogicException $e) {
            return null;
        }

        return is_array($value) ? current($value) : $value;
    }
}
