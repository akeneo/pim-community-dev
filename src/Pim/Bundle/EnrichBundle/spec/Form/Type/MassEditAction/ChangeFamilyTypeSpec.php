<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeFamilyTypeSpec extends ObjectBehavior
{
    function let(FamilyRepositoryInterface $repository)
    {
        $this->beConstructedWith(
            ChangeFamily::class,
            $repository
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_mass_change_family');
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => ChangeFamily::class,
            ]
        )->shouldHaveBeenCalled();
    }
}
