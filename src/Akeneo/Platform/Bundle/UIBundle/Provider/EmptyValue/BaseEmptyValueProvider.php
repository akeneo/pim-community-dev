<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

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
        switch ($attribute->getType()) {
            case AttributeTypes::METRIC:
                $emptyValue = [
                    'amount' => null,
                    'unit'   => $attribute->getDefaultMetricUnit(),
                ];
                break;
            case AttributeTypes::OPTION_MULTI_SELECT:
            case AttributeTypes::PRICE_COLLECTION:
                $emptyValue = [];
                break;
            case AttributeTypes::TEXT:
                $emptyValue = '';
                break;
            case AttributeTypes::BOOLEAN:
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
