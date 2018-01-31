<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityFilterType extends AbstractChoiceType
{
    const NAME = 'oro_type_entity_filter';

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return ChoiceFilterType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'field_type'    => EntityType::class,
                'field_options' => [],
                'translatable'  => false,
            ]
        );

        $resolver->setNormalizer('field_type', function (Options $options, $value) {
            if (!empty($options['translatable'])) {
                $value = 'translatable_entity';
            }

            return $value;
        });
    }
}
