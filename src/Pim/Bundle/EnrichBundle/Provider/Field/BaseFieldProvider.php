<?php

namespace Pim\Bundle\EnrichBundle\Provider\Field;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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
        'pim_catalog_boolean'          => 'akeneo-switch-field',
        'pim_catalog_date'             => 'akeneo-datepicker-field',
        'pim_catalog_file'             => 'akeneo-media-uploader-field',
        'pim_catalog_image'            => 'akeneo-media-uploader-field',
        'pim_catalog_metric'           => 'akeneo-metric-field',
        'pim_catalog_multiselect'      => 'akeneo-multi-select-field',
        'pim_catalog_number'           => 'akeneo-number-field',
        'pim_catalog_price_collection' => 'akeneo-price-collection-field',
        'pim_catalog_simpleselect'     => 'akeneo-simple-select-field',
        'pim_catalog_identifier'       => 'akeneo-text-field',
        'pim_catalog_text'             => 'akeneo-text-field',
        'pim_catalog_textarea'         => 'akeneo-textarea-field'
    ];

    /**
     * {@inheritdoc}
     */
    public function getField($attribute)
    {
        return $this->fields[$attribute->getAttributeType()];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof AttributeInterface &&
            in_array($element->getAttributeType(), array_keys($this->fields));
    }
}
