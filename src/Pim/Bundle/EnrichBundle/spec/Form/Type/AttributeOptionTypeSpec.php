<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttributeOptionTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\CatalogBundle\Entity\AttributeOption');
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_attribute_option');
    }

    function it_builds_form(FormBuilderInterface $builder)
    {
        $builder->add('id', 'hidden')->shouldBeCalled();

        $builder->add(
            'optionValues',
            'collection',
            [
                'type'         => 'pim_enrich_attribute_option_value',
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]
        )->shouldBeCalled();

        $builder->add('code', 'text', ['required' => true])->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_sets_default_option(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class'      => 'Pim\Bundle\CatalogBundle\Entity\AttributeOption',
                'csrf_protection' => false
            ]
        )->shouldHaveBeenCalled();
    }
}
