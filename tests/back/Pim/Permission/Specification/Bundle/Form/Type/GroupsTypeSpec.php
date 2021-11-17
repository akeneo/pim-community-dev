<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Bundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\Group;
use Prophecy\Argument;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupsTypeSpec extends ObjectBehavior
{
    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pimee_security_groups');
    }

    function it_extends_the_entity_form_type()
    {
        $this->getParent()->shouldReturn(EntityType::class);
    }

    function it_configures_the_form_type_to_provide_available_user_groups(OptionsResolver $resolver)
    {
        $this->configureOptions($resolver, []);

        $resolver
            ->setDefaults(
                Argument::allOf(
                    Argument::withEntry('class', Group::class),
                    Argument::withEntry('property', 'name'),
                    Argument::withEntry('multiple', true),
                    Argument::withEntry('required', false),
                    Argument::withEntry('select2', true),
                    Argument::withKey('query_builder')
                )
            )
            ->shouldHaveBeenCalled();
    }
}
