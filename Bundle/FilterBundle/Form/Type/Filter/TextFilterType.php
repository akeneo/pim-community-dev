<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TextFilterType extends AbstractType
{
    const TYPE_CONTAINS     = 1;
    const TYPE_NOT_CONTAINS = 2;
    const TYPE_EQUAL        = 3;
    const TYPE_STARTS_WITH  = 4;
    const TYPE_ENDS_WITH    = 5;
    const NAME              = 'oro_type_text_filter';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
        return FilterType::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = array(
            self::TYPE_CONTAINS     => $this->translator->trans('oro.filter.form.label_type_contains'),
            self::TYPE_NOT_CONTAINS => $this->translator->trans('oro.filter.form.label_type_not_contains'),
            self::TYPE_EQUAL        => $this->translator->trans('oro.filter.form.label_type_equals'),
            self::TYPE_STARTS_WITH  => $this->translator->trans('oro.filter.form.label_type_start_with'),
            self::TYPE_ENDS_WITH    => $this->translator->trans('oro.filter.form.label_type_end_with'),
        );

        $resolver->setDefaults(
            array(
                'field_type'       => 'text',
                'operator_choices' => $choices,
            )
        );
    }
}
