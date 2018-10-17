<?php

namespace Akeneo\Platform\Bundle\UIBundle\Provider\Field;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Field provider for attributes
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseFieldProvider implements FieldProviderInterface
{
    /** @var array */
    protected $fields = [
        AttributeTypes::BOOLEAN              => 'akeneo-switch-field',
        AttributeTypes::DATE                 => 'akeneo-datepicker-field',
        AttributeTypes::FILE                 => 'akeneo-media-uploader-field',
        AttributeTypes::IMAGE                => 'akeneo-media-uploader-field',
        AttributeTypes::METRIC               => 'akeneo-metric-field',
        AttributeTypes::OPTION_MULTI_SELECT  => 'akeneo-multi-select-field',
        AttributeTypes::NUMBER               => 'akeneo-number-field',
        AttributeTypes::PRICE_COLLECTION     => 'akeneo-price-collection-field',
        AttributeTypes::OPTION_SIMPLE_SELECT => 'akeneo-simple-select-field',
        AttributeTypes::IDENTIFIER           => 'akeneo-text-field',
        AttributeTypes::TEXT                 => 'akeneo-text-field',
        AttributeTypes::TEXTAREA             => 'akeneo-textarea-field'
    ];

    /**
     * {@inheritdoc}
     */
    public function getField($attribute)
    {
        return $this->fields[$attribute->getType()];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof AttributeInterface &&
            in_array($element->getType(), array_keys($this->fields));
    }
}
