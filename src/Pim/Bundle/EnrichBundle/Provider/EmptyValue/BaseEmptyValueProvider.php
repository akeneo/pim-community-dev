<?php

namespace Pim\Bundle\EnrichBundle\Provider\EmptyValue;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * EmptyValue provider for attributes
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseEmptyValueProvider implements EmptyValueProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmptyValue($attribute)
    {
        switch ($attribute->getAttributeType()) {
            case 'pim_catalog_metric':
                $emptyValue = [
                    'data' => null,
                    'unit' => $attribute->getDefaultMetricUnit(),
                ];
                break;
            case 'pim_catalog_multiselect':
            case 'pim_catalog_price_collection':
                $emptyValue = [];
                break;
            case 'pim_catalog_text':
                $emptyValue = '';
                break;
            case 'pim_catalog_boolean':
                $emptyValue = false;
                break;
            default:
                $emptyValue = null;
                break;
        }

        return $emptyValue;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof AttributeInterface;
    }
}
