<?php

namespace Pim\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;

/**
 * Overriding of ChoiceFilterType for categories
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryFilterType extends ChoiceFilterType
{

    /**
     * Form type name
     * @staticvar string
     */
    const NAME = 'pim_type_category_filter';

    /**
     * Operators defined
     * @staticvar integer
     */
    const TYPE_CONTAINS     = 1;
    const TYPE_NOT_CONTAINS = 2;
    const TYPE_CLASSIFIED   = 3;
    const TYPE_UNCLASSIFIED = 4;

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
        return parent::getName();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $operators = array(
            self::TYPE_CONTAINS     => $this->translator->trans('label_type_contains', array(), 'OroFilterBundle'),
            self::TYPE_NOT_CONTAINS => $this->translator->trans('label_type_not_contains', array(), 'OroFilterBundle'),
            self::TYPE_CLASSIFIED   => $this->translator->trans('label_type_contains', array(), 'PimFilterBundle'),
            self::TYPE_UNCLASSIFIED => $this->translator->trans('label_type_contains', array(), 'PimFilterBundle')
        );

        $typeValues = array(
            'contains' => self::TYPE_CONTAINS,
            'notContains' => self::TYPE_NOT_CONTAINS,
            'classified' => self::TYPE_CLASSIFIED,
            'unclassified' => self::TYPE_UNCLASSIFIED
        );

        $resolver->setDefaults(
            array(
                'field_type'       => 'choice',
                'field_options'    => array('choices' => array()),
                'operator_choices' => $operators,
                'type_values'      => $typeValues
            )
        );
    }
}
