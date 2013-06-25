<?php

namespace Oro\Bundle\AddressBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

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
        $choices = function (Options $options) {
            // show empty list if country is not selected
            if (empty($options['country'])) {
                return array();
            }

            return null;
        };

        $resolver
            ->setDefaults(
                array(
                    'class'         => 'OroAddressBundle:Region',
                    'property'      => 'name',
                    'query_builder' => null,
                    'choices'       => $choices,
                    'country'       => null,
                    'country_field' => null,
                    'configs' => array(
                        'placeholder' => 'oro.address.form.choose_state',
                    ),
                    'empty_value' => '',
                    'empty_data'  => null
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
        return 'genemu_jqueryselect2_translatable_entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_region';
    }
}
