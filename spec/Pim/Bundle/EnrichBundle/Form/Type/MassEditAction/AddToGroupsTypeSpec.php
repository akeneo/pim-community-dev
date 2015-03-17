<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
        OptionsResolverInterface $resolver,
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

    function it_gets_warning_messages_if_there_is_no_group($groupRepository)
    {
        $groupRepository->getAllGroupsExceptVariant()->willReturn([]);
        $this->getWarningMessages()->shouldReturn([
            [
                'key'     => 'pim_enrich.mass_edit_action.add-to-groups.no_group',
                'options' => []
            ]
        ]);
    }

    function it_gets_no_warning_message_if_there_is_at_least_one_group($groupRepository, GroupInterface $houseGroup)
    {
        $groupRepository->getAllGroupsExceptVariant()->willReturn([$houseGroup]);
        $this->getWarningMessages()->shouldReturn([]);
    }
}
