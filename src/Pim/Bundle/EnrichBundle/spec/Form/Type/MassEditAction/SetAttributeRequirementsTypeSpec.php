<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\SetAttributeRequirements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SetAttributeRequirementsTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(SetAttributeRequirements::class);
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_mass_set_attribute_requirements');
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => SetAttributeRequirements::class,
            ]
        )->shouldHaveBeenCalled();
    }
}
