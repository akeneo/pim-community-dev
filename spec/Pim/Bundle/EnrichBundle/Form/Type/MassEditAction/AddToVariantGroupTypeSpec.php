<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddToVariantGroupTypeSpec extends ObjectBehavior
{
    function let(
        ProductMassActionRepositoryInterface $prodMassActionRepo,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->beConstructedWith(
            $prodMassActionRepo,
            $groupRepository,
            'Pim\CatalogBundle\Model\Group',
            'Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToVariantGroups'
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_mass_add_to_variant_group');
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToVariantGroups',
                'groups' => [],
            ]
        )->shouldHaveBeenCalled();
    }
}
