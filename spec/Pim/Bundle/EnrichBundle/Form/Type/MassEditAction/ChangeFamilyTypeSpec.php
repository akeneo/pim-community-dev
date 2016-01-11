<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeFamilyTypeSpec extends ObjectBehavior
{
    function let(FamilyRepositoryInterface $repository)
    {
        $this->beConstructedWith(
            'Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily',
            $repository
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

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily',
            ]
        )->shouldHaveBeenCalled();
    }
}
