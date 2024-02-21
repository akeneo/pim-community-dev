<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Bundle\Form\Type\AttributeOptionValueType;
use Prophecy\Argument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeOptionTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(AttributeOption::class);
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_attribute_option');
    }

    function it_builds_form(FormBuilderInterface $builder)
    {
        $builder->add('id', HiddenType::class)->shouldBeCalled();

        $builder->add(
            'optionValues',
            CollectionType::class,
            Argument::that(
                fn ($arg): bool => \is_array($arg) &&
                    ($arg['entry_type'] ?? null === AttributeOptionValueType::class) &&
                    ($arg['allow_add'] ?? null) === true &&
                    ($arg['allow_delete'] ?? null) === true &&
                    ($arg['by_reference'] ?? null) === false &&
                    \is_callable($arg['delete_empty'] ?? null)
            )
        )->shouldBeCalled();

        $builder->add('code', TextType::class, ['required' => true])->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_sets_default_option(OptionsResolver $resolver)
    {
        $this->configureOptions($resolver);

        $resolver->setDefaults(
            [
                'data_class'      => AttributeOption::class,
                'csrf_protection' => false
            ]
        )->shouldHaveBeenCalled();
    }
}
