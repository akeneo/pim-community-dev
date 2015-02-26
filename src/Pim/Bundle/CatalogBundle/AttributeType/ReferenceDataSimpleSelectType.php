<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;

/**
 * Reference data simple options (select) attribute type
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataSimpleSelectType extends OptionSimpleSelectType
{
    /**
     * {@inheritdoc}
     */
    public function prepareValueFormOptions(ProductValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $options['class'] = $value->getAttribute()->getReferenceData();

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
            'reference_data' => [
                'name' => 'reference_data',
                'fieldType' => 'choice',
                'options' => [
                    //TODO-CR: remove this hardcode
                    'choices' => [
                        'Acme\Bundle\AppBundle\Entity\Car' => 'Car',
                        'Acme\Bundle\AppBundle\Entity\Moto' => 'Moto',
                        'Acme\Bundle\AppBundle\Entity\Truck' => 'Truck'
                    ],
                    'required' => true,
                    'multiple'    => false,
                    'empty_value' => 'Choose the reference data type',
                    'select2'     => true
                ],
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_reference_data_simpleselect';
    }
}
