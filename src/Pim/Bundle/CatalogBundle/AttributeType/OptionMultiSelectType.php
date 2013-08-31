<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Multi options (select) attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionMultiSelectType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $attribute = $value->getAttribute();
        $options['empty_value']   = false;
        $options['class']         = 'PimCatalogBundle:AttributeOption';
        $options['query_builder'] = function (EntityRepository $repository) use ($attribute) {
            return $repository->findAllForAttribute($attribute);
        };
        $options['expanded'] = false;
        $options['multiple'] = true;

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormData(FlexibleValueInterface $value)
    {
        if ($value->getData() && $value->getData()->isEmpty()) {
            return $value->getAttribute()->getDefaultValue();
        }

        return $value->getData();
    }

    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        $properties = array(
            array(
                'name'      => 'valueCreationAllowed',
                'fieldType' => 'checkbox'
            ),
            array(
                'name'      => 'searchable',
                'fieldType' => 'checkbox'
            ),
            array(
                'name'      => 'options',
                'fieldType' => 'pim_product_options'
            ),
            array(
                'name'      => 'translatable',
                'fieldType' => 'checkbox',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            ),
            array(
                'name'      => 'availableLocales',
                'fieldType' => 'pim_product_available_locales'
            ),
            array(
                'name'      => 'scopable',
                'fieldType' => 'pim_product_scopable',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            ),
            array(
                'name'      => 'unique',
                'fieldType' => 'checkbox',
                'options'   => array(
                    'disabled'  => true,
                    'read_only' => true
                )
            )
        );

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_multiselect';
    }
}
