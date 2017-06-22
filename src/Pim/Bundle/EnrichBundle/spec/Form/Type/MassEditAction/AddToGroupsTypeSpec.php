<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\AddToGroups;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddToGroupsTypeSpec extends ObjectBehavior
{
    function let(GroupRepositoryInterface $groupRepository)
    {
        $this->beConstructedWith(
            $groupRepository,
            Group::class,
            AddToGroups::class
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_mass_add_to_groups');
    }

    function it_sets_default_options(
        $groupRepository,
        OptionsResolver $resolver,
        GroupInterface $minimalGroup,
        GroupInterface $progressiveGroup
    ) {
        $groupRepository->getAllGroupsExceptVariant()->willReturn([$minimalGroup, $progressiveGroup]);
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => AddToGroups::class,
                'groups' => [$minimalGroup, $progressiveGroup],
            ]
        )->shouldHaveBeenCalled();
    }

    function it_builds_add_groups_to_products_form(
        FormBuilderInterface $builder,
        GroupInterface $electroGroup
    ) {
        $options = ['groups' => [$electroGroup]];

        $builder->add(
            'groups',
            EntityType::class,
            [
                'class'    => Group::class,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices'  => [$electroGroup]
            ]
        )->shouldBeCalled();

        $this->buildForm($builder, $options);
    }
}
