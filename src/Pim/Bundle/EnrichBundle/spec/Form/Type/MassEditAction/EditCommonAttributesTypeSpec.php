<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes;
use Symfony\Component\Form\AbstractType;

class EditCommonAttributesTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            EditCommonAttributes::class
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_mass_edit_common_attributes');
    }
}
