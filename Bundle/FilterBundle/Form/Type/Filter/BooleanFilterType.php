<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BooleanFilterType extends AbstractType
{
    const TYPE_YES = 1;
    const TYPE_NO = 2;
    const NAME = 'oro_type_boolean_filter';

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
        return ChoiceFilterType::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $fieldChoices = array(
            self::TYPE_YES => $this->translator->trans('label_type_yes', array(), 'OroFilterBundle'),
            self::TYPE_NO  => $this->translator->trans('label_type_no', array(), 'OroFilterBundle'),
        );

        $resolver->setDefaults(
            array(
                'field_options' => array('choices' => $fieldChoices),
            )
        );
    }
}
