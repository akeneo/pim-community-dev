<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            Attribute::class,
            Group::class
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_group');
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $this->configureOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => Group::class,
            ]
        )->shouldHaveBeenCalled();
    }
}
