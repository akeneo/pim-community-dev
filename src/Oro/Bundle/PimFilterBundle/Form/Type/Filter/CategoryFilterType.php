<?php

namespace Oro\Bundle\PimFilterBundle\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\PimFilterBundle\Form\Type\CategoryType;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return NumberFilterType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = [
            self::EXCLUDE_SUB => 0,
            self::INCLUDE_SUB => 1
        ];

        $resolver->setDefaults(
            [
                'field_type'        => CategoryType::class,
                'operator_choices'  => $choices,
                'placeholder'       => self::EXCLUDE_SUB,
                'data_type'         => self::DATA_INTEGER,
                'formatter_options' => []
            ]
        );
    }
}
