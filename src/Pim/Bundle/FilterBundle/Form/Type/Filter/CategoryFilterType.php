<?php

namespace Pim\Bundle\FilterBundle\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Pim\Bundle\FilterBundle\Form\Type\CategoryType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Category filter type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFilterType extends NumberFilterType
{
    /** @staticvar integer */
    const EXCLUDE_SUB = 0;

    /** @staticvar integer */
    const INCLUDE_SUB = 1;

    /** @staticvar string */
    const NAME = 'pim_type_category_filter';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return NumberFilterType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = array(
            self::EXCLUDE_SUB => 0,
            self::INCLUDE_SUB => 1
        );

        $resolver->setDefaults(
            array(
                'field_type'        => CategoryType::NAME,
                'operator_choices'  => $choices,
                'empty_value'       => self::EXCLUDE_SUB,
                'data_type'         => self::DATA_INTEGER,
                'formatter_options' => array()
            )
        );
    }
}
