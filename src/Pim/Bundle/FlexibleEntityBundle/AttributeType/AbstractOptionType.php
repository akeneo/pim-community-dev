<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Abstract option attribute type
 */
abstract class AbstractOptionType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $attribute = $value->getAttribute();
        $options['empty_value']   = false;
        $options['class']         = 'PimFlexibleEntityBundle:AttributeOption';
        $options['query_builder'] = function (EntityRepository $er) use ($attribute) {
            return $er->createQueryBuilder('opt')->where('opt.attribute = '.$attribute->getId());
        };

        return $options;
    }
}
