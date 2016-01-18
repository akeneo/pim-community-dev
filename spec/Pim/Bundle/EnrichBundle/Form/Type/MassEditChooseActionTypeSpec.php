<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MassEditChooseActionTypeSpec extends ObjectBehavior
{
    function it_builds_the_mass_edit_choose_action_form(
        FormBuilderInterface $builder
    ) {
        $options = [
            'operations' => [
                'erase'     => 'erase.label',
                'duplicate' => 'duplicate.label'
            ]
        ];

        $builder->add(
            'operationAlias',
            'choice',
            [
                'choices'  => $options['operations'],
                'expanded' => true,
                'multiple' => false,
            ]
        )->shouldBeCalled();

        $this->buildForm($builder, $options);
    }

    function it_sets_the_default_options(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'operations' => []
            ]
        )->shouldBeCalled();

        $this->setDefaultOptions($resolver);
    }

    function it_gets_the_form_type_name()
    {
        $this->getName()->shouldReturn('pim_enrich_mass_edit_choose_action');
    }
}
