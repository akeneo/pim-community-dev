<?php

namespace Oro\Bundle\QueryDesignerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SortingChoiceType extends AbstractType
{
    const NAME = 'oro_sorting_choice';

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'choices'     => array(
                    'ASC'  => 'oro.querydesigner.form.sorting_asc',
                    'DESC' => 'oro.querydesigner.form.sorting_desc'
                ),
                'empty_value' => 'oro.querydesigner.form.choose_sorting',
                'empty_data'  => ''
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
