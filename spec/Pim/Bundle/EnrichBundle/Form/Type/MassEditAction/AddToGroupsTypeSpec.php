<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddToGroupsTypeSpec extends ObjectBehavior
{
    function let(GroupRepositoryInterface $groupRepository)
    {
        $this->beConstructedWith(
            $groupRepository,
            'Pim\CatalogBundle\Model\Group',
            'Pim\\Bundle\\EnrichBundle\\MassEditAction\\Operation\\AddToGroups'
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_mass_add_to_groups');
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
                'data_class' => 'Pim\\Bundle\\EnrichBundle\\MassEditAction\\Operation\\AddToGroups',
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
            'entity',
            [
                'class'    => 'Pim\CatalogBundle\Model\Group',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices'  => [$electroGroup]
            ]
        )->shouldBeCalled();

        $this->buildForm($builder, $options);
    }
}
