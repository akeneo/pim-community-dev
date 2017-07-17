<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\EnrichBundle\Form\Type\AttributeOptionValueType;
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
            [
                'entry_type'   => AttributeOptionValueType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]
        )->shouldBeCalled();

        $builder->add('code', TextType::class, ['required' => true])->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_sets_default_option(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class'      => AttributeOption::class,
                'csrf_protection' => false
            ]
        )->shouldHaveBeenCalled();
    }
}
