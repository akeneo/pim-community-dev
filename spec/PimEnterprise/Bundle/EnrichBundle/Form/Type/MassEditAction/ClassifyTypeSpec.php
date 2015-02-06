<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClassifyTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('PimEnterprise\\Bundle\\EnrichBundle\\MassEditAction\\Operation\\Classify');
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee_enrich_mass_classify');
    }

    function it_has_a_parent()
    {
        $this->getParent()->shouldReturn('pim_enrich_mass_classify');
    }

    function it_has_view_and_edit_permission_fields(FormBuilderInterface $builder)
    {
        $this->buildForm($builder, []);

        $builder
            ->add('notGrantedIdentifiers', 'hidden')
            ->shouldHaveBeenCalled();
    }

    function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolverInterface $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'PimEnterprise\\Bundle\\EnrichBundle\\MassEditAction\\Operation\\Classify',
            ]
        )->shouldHaveBeenCalled();
    }
}
