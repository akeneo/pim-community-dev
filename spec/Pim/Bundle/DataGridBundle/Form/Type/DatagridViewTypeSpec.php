<?php

namespace spec\Pim\Bundle\DataGridBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatagridViewTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\DataGridBundle\Entity\DatagridView');
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_datagrid_view');
    }

    function it_has_view_and_edit_permission_fields(FormBuilderInterface $builder)
    {
        $builder->add('label', 'text', ['required' => true])->willReturn($builder);
        $builder->add('order', 'hidden')->willReturn($builder);
        $builder->add('filters', 'hidden')->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);
        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\DataGridBundle\Entity\DatagridView',
            ]
        )->shouldHaveBeenCalled();
    }
}
