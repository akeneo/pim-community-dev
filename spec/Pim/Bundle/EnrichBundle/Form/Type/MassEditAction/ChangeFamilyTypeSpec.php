<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChangeFamilyTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            'Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily'
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_mass_change_family');
    }

    function it_sets_default_options(OptionsResolverInterface $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily',
            ]
        )->shouldHaveBeenCalled();
    }
}
