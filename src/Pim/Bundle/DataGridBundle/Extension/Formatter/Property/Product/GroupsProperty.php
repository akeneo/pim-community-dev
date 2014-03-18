<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\Product;

use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty as OroFieldProperty;

/**
 * Allows to configure a related template for value rendering
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupsProperty extends OroFieldProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        if (!$value) {
            return null;
        }

        foreach ($value as $group) {
            $translations = $group['translations'];
            if (count($translations) > 0 and $translations[0]['label'] != '') {
                $result[]= $translations[0]['label'];
            } else {
                $result[]= '['.$group['code'].']';
            }
        }

        return implode(', ', $result);
    }
}
