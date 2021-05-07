<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class TextFilterType extends AbstractType
{
    public const TYPE_CONTAINS = 1;
    public const TYPE_NOT_CONTAINS = 2;
    public const TYPE_EQUAL = 3;
    public const TYPE_STARTS_WITH = 4;
    public const TYPE_ENDS_WITH = 5;
    public const TYPE_EMPTY = 'empty';
    public const NAME = 'oro_type_text_filter';

    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
        return FilterType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = [
            self::TYPE_CONTAINS     => $this->translator->trans('oro.filter.form.label_type_contains'),
            self::TYPE_NOT_CONTAINS => $this->translator->trans('oro.filter.form.label_type_not_contains'),
            self::TYPE_EQUAL        => $this->translator->trans('oro.filter.form.label_type_equals'),
            self::TYPE_STARTS_WITH  => $this->translator->trans('oro.filter.form.label_type_start_with'),
            self::TYPE_EMPTY        => $this->translator->trans('oro.filter.form.label_type_empty'),
        ];

        $resolver->setDefaults(
            [
                'field_type'       => TextType::class,
                'operator_choices' => $choices,
            ]
        );
    }
}
