<?php

namespace Specification\Akeneo\UserManagement\Bundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Bundle\Form\Transformer\AccessLevelToBooleanTransformer;
use Akeneo\UserManagement\Bundle\Form\Type\AclAccessLevelSelectorType;
use Prophecy\Argument;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AclAccessLevelSelectorTypeSpec extends ObjectBehavior
{
    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_acl_access_level_selector');
    }

    function it_extends_the_checkbox_form_type()
    {
        $this->getParent()->shouldReturn(CheckboxType::class);
    }

    function it_sets_the_default_acl_choices(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'choices' => [0 => 0, 5 => 5]
                ]
            )
            ->shouldBeCalled();
        $this->configureOptions($resolver);
    }

    function it_adds_a_view_transformer_to_the_form(FormBuilderInterface $builder)
    {
        $builder
            ->addViewTransformer(
                Argument::type(AccessLevelToBooleanTransformer::class),
                true
            )
            ->shouldBeCalled();
        $this->buildForm($builder, []);
    }
}
