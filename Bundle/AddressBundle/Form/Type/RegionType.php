<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FormBundle\Form\Type\TranslatableEntityType;

class RegionType extends AbstractType
{
    const COUNTRY_OPTION_KEY = 'country_field';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->setAttribute(self::COUNTRY_OPTION_KEY, $options[self::COUNTRY_OPTION_KEY]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'class' => 'OroAddressBundle:Region',
                    'property' => 'name',
                    'query_builder' => null,
                    'country'     => null,
                    'country_field' => null,
                    'empty_value' => 'Choose a state...',
                    'empty_data'  => null,
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['country_field'] = $form->getAttribute(self::COUNTRY_OPTION_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TranslatableEntityType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_region';
    }
}
