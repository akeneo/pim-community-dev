<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupTypeTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            GroupType::class
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_grouptype');
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $this->configureOptions($resolver);

        $resolver->setDefaults(
            [
                'data_class' => GroupType::class,
            ]
        )->shouldHaveBeenCalled();
    }
}
