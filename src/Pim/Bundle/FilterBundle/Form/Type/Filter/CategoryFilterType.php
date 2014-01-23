<?php

namespace Pim\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Pim\Bundle\FilterBundle\Form\Type\CategoryType;

/**
 * Category filter type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFilterType extends NumberFilterType
{
    /**
     * @staticvar boolean
     */
    const EXCLUDE_SUB = 0;

    /**
     * @staticvar boolean
     */
    const INCLUDE_SUB = 1;

    /**
     * @staticvar string
     */
    const NAME = 'pim_type_category_filter';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return NumberFilterType::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = [
            self::EXCLUDE_SUB => 0,
            self::INCLUDE_SUB => 1
        ];

        $resolver->setDefaults(
            [
                'field_type'        => CategoryType::NAME,
                'operator_choices'  => $choices,
                'empty_value'       => self::EXCLUDE_SUB,
                'data_type'         => self::DATA_INTEGER,
                'formatter_options' => []
            ]
        );
    }
}
