<?php

namespace spec\Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ChoiceFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, FilterUtility $util)
    {
        $this->beConstructedWith($factory, $util);
    }

    function it_is_a_filter()
    {
        $this->shouldHaveType(FilterInterface::class);
    }

    function it_gives_metadata($factory, $util, FormBuilderInterface $builder, FormInterface $form)
    {
        $builder->get('type')->willReturn($builder);
        $builder->getOption('choices')->willReturn(['foo', 'bar']);
        $factory->createBuilder(ChoiceFilterType::class, [], ['csrf_protection' => false])->willReturn($builder);
        $factory->create(ChoiceFilterType::class, [], ['csrf_protection' => false])->willReturn($form);
        $util->getExcludeParams()->willReturn([]);
        $util->getParamMap()->willReturn([]);

        $view = new FormView();
        $fieldView = new FormView();
        $typeView = new FormView();

        $view->children['value'] = $fieldView;
        $view->children['type'] = $typeView;
        $view->vars['populate_default'] = false;

        $typeView->vars['choices'] = [];

        $fieldView->vars['multiple'] = true;
        $fieldView->vars['choices'] = [
            new ChoiceView('name', 'name', 'Name'),
            new ChoiceView('description', 'description', 'Description'),
            new ChoiceGroupView('Marketing', [
                ['label' => 'Price', 'value' => 'price'],
            ]),
        ];

        $form->createView()->willReturn($view);

        $this->init('choices', []);
        $this->getMetadata()->shouldReturn([
            'name' => 'choices',
            'label' => 'Choices',
            'choices' => [
                ['label' => 'Name', 'value' => 'name'],
                ['label' => 'Description', 'value' => 'description'],
                ['label' => 'Marketing', 'value' => [
                    ['label' => 'Price', 'value' => 'price']
                ]],
            ],
            'enabled' => true,
            'populateDefault' => false,
            'type' => 'multichoice',
        ]);
    }
}
