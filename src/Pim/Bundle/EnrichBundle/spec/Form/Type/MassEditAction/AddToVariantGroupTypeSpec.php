<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToVariantGroup;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductMassActionRepositoryInterface;
use Symfony\Component\Form\AbstractType;
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
            Group::class,
            AddToVariantGroup::class
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_mass_add_to_variant_group');
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => AddToVariantGroup::class,
                'groups' => [],
            ]
        )->shouldHaveBeenCalled();
    }
}
