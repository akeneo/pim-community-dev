<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatagridViewTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(DatagridView::class);
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_datagrid_view');
    }

    function it_has_view_and_edit_permission_fields(FormBuilderInterface $builder)
    {
        $builder->add('label', TextType::class, ['required' => true])->willReturn($builder);
        $builder->add('order', HiddenType::class)->willReturn($builder);
        $builder->add('filters', HiddenType::class)->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolver $resolver)
    {
        $this->configureOptions($resolver);
        $resolver->setDefaults(
            [
                'data_class' => DatagridView::class,
            ]
        )->shouldHaveBeenCalled();
    }
}
