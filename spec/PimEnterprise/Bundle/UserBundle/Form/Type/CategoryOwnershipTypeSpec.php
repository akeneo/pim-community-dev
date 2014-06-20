<?php

namespace spec\PimEnterprise\Bundle\UserBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

class CategoryOwnershipTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('category');
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee_user_category_ownership');
    }

    function it_does_not_map_form_data_by_default(OptionsResolverInterface $resolver)
    {
        $this->setDefaultOptions($resolver);

        $resolver->setDefaults(['mapped' => false])->shouldHaveBeenCalled();
    }

    function it_adds_append_and_remove_category_fields_to_the_form(FormBuilderInterface $builder)
    {
        $builder->add(Argument::cetera())->willReturn($builder);

        $this->buildForm($builder, []);

        $builder
            ->add(
                'appendCategories',
                'oro_entity_identifier',
                [
                    'class'    => 'category',
                    'required' => false,
                    'multiple' => true,
                ]
            )
            ->shouldHaveBeenCalled();

        $builder
            ->add(
                'removeCategories',
                'oro_entity_identifier',
                [
                    'class'    => 'category',
                    'required' => false,
                    'multiple' => true,
                ]
            )
            ->shouldHaveBeenCalled();
    }
}
