<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;

class EntityFilterType extends AbstractChoiceType
{
    const NAME = 'oro_type_entity_filter';

    /**
     * {@inheritDoc}
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
        return ChoiceFilterType::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'field_type'    => 'entity',
                'field_options' => array(),
                'translatable'  => false,
            )
        );

        $resolver->setNormalizers(
            array(
                'field_type' => function (Options $options, $value) {
                    if (!empty($options['translatable'])) {
                        $value = 'translatable_entity';
                    }

                    return $value;
                }
            )
        );
    }
}
