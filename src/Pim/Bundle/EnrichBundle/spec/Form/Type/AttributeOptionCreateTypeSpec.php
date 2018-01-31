<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\EnrichBundle\Form\Type\AttributeOptionValueType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeOptionCreateTypeSpec extends ObjectBehavior
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
        $this->getBlockPrefix()->shouldReturn('pim_attribute_option_create');
    }

    function it_builds_form(FormBuilderInterface $builder)
    {
        $builder->add('code', TextType::class, ['required' => true])->willReturn($builder);
        $builder->add(
            'optionValues',
            CollectionType::class,
            [
                'type'         => AttributeOptionValueType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false
            ]
        )->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolver $resolver)
    {
        $this->configureOptions($resolver);

        $resolver->setDefaults(
            [
                'data_class' => AttributeOption::class,
            ]
        )->shouldHaveBeenCalled();
    }
}
