<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue;

/**
 * Able to render attribute type which use options as backend
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionsProperty extends FieldProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $data = $this->getBackendData($value);

        $optionValues = [];
        foreach ($data as $option) {
            if (isset($option['optionValues']) && count($option['optionValues']) === 1) {
                $optionValue = current($option['optionValues']);
                $optionValues[]= $optionValue['value'];
            } else {
                $optionValues[] = '['.$option['code'].']';
            }
        }
        $result = implode(', ', $optionValues);

        return $result;
    }
}
