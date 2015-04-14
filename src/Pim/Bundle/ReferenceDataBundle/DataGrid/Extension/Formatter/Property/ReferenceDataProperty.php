<?php

namespace Pim\Bundle\ReferenceDataBundle\DataGrid\Extension\Formatter\Property;

use Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue\FieldProperty;

/**
 * Able to render a reference data type
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataProperty extends FieldProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $referenceData = $value[$value['attribute']['properties']['reference_data_name']];

        if (isset($referenceData['code'])) {
            return sprintf('[%s]', $referenceData['code']);
        }

        if (is_array($referenceData)) {
            $codes = [];
            foreach ($referenceData as $data) {
                $codes[] = sprintf('[%s]', $data['code']);
            }

            return implode(', ', $codes);
        }

        return null;
    }
}
