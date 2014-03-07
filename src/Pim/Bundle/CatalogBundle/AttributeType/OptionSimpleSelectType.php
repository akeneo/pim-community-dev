<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Simple options (select) attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionSimpleSelectType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $attribute = $value->getAttribute();
        $options['class']                = 'PimCatalogBundle:AttributeOption';
        $options['collection_id']        = $attribute->getId();
        $options['required']             = false;
        $options['minimum_input_length'] = $attribute->getMinimumInputLength();

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
            'searchable' => [
                'name'      => 'searchable',
                'fieldType' => 'switch'
            ],
            'minimumInputLength' => [
                'name'      => 'minimumInputLength',
                'fieldType' => 'number'
            ],
            'options' => [
                'name'      => 'options',
                'fieldType' => 'pim_enrich_options'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_simpleselect';
    }
}
