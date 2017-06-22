<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\UIBundle\Form\Type\NumberType as FormNumberType;
use Pim\Bundle\UIBundle\Form\Type\SwitchType;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;

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
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
            'minimumInputLength' => [
                'name'      => 'minimumInputLength',
                'fieldType' => FormNumberType::class
            ],
            'autoOptionSorting' => [
                'name'      => 'autoOptionSorting',
                'fieldType' => SwitchType::class,
                'options'   => [
                    'label'         => 'Automatic option sorting',
                    'property_path' => 'properties[autoOptionSorting]',
                    'help'          => 'info.attribute.auto option sorting',
                    'attr'          => [
                        'class' => 'hide automatic-option-sorting',
                    ]
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return AttributeTypes::OPTION_SIMPLE_SELECT;
    }
}
