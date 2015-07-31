<?php

namespace spec\Pim\Bundle\UserBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AclAccessLevelSelectorTypeSpec extends ObjectBehavior
{
    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_acl_access_level_selector');
    }

    function it_extends_the_checkbox_form_type()
    {
        $this->getParent()->shouldReturn('checkbox');
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
        $this->setDefaultOptions($resolver);
    }

    function it_adds_a_view_transformer_to_the_form(FormBuilderInterface $builder)
    {
        $builder
            ->addViewTransformer(
                Argument::type('Pim\Bundle\UserBundle\Form\Transformer\AccessLevelToBooleanTransformer'),
                true
            )
            ->shouldBeCalled();
        $this->buildForm($builder, []);
    }
}
