<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeOptionValueTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(AttributeOptionValue::class);
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_attribute_option_value');
    }

    function it_builds_form(FormBuilderInterface $builder)
    {
        $builder->add('id', HiddenType::class)->shouldBeCalled();

        $builder->add('locale', HiddenType::class)->shouldBeCalled();

        $builder->add('value', null, ['required' => false])->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_sets_default_option(OptionsResolver $resolver)
    {
        $this->configureOptions($resolver);

        $resolver->setDefaults(
            [
                'data_class' => AttributeOptionValue::class,
            ]
        )->shouldHaveBeenCalled();
    }
}
