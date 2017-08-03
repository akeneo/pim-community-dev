<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\OptionsResolver\OptionsResolver;

class BooleanFilterType extends AbstractChoiceType
{
    const TYPE_YES = 1;
    const TYPE_NO = 2;
    const NAME = 'oro_type_boolean_filter';

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
        $fieldChoices = [
            $this->translator->trans('oro.filter.form.label_type_yes') => self::TYPE_YES,
            $this->translator->trans('oro.filter.form.label_type_no') => self::TYPE_NO,
        ];

        $resolver->setDefaults(
            [
                'field_options' => ['choices' => $fieldChoices],
            ]
        );
    }
}
