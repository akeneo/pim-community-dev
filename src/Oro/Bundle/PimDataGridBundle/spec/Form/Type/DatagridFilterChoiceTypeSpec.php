<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Form\Type;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatagridFilterChoiceTypeSpec extends ObjectBehavior
{
    function let(Manager $manager, FiltersConfigurator $configurator)
    {
        $this->beConstructedWith($manager, $configurator, 'foo-grid');
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_is_a_parent_of_choice()
    {
        $this->getParent()->shouldReturn(ChoiceType::class);
    }

    function it_have_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_datagrid_product_filter_choice');
    }

    function it_configure_choices_and_skip_disallowed(
        $manager,
        $configurator,
        DatagridInterface $datagrid,
        Acceptor $acceptor,
        DatagridConfiguration $configuration,
        OptionsResolver $resolver
    ) {
        $acceptor->getConfig()->willReturn($configuration);
        $datagrid->getAcceptor()->willReturn($acceptor);
        $manager->getDatagrid('foo-grid')->willReturn($datagrid);
        $configurator->configure($configuration)->shouldBeCalled();

        $configuration->offsetGetByPath('[filters][columns]')->willReturn([
            'foobar' => ['label' => 'FooBar'],
            'foo-1'  => ['label' => 'Foo 1'],
            'foo-2'  => ['label' => 'Foo 2'],
            'bar-1'  => ['label' => 'Bar 1'],
            'scope'  => ['label' => 'Scope'],
            'locale' => ['label' => 'Locale'],
        ]);

        $configuration->offsetGetByPath('[source][attributes_configuration]')->willReturn([
            'foo-1' => ['group' => 'foo'],
            'foo-2' => ['group' => 'foo'],
            'bar-1' => ['group' => 'bar'],
        ]);

        $resolver->setDefaults(['choices' => [
            'System' => ['foobar' => 'FooBar'],
            'foo'    => ['foo-1' => 'Foo 1', 'foo-2' => 'Foo 2'],
            'bar'    => ['bar-1' => 'Bar 1']
        ]])->shouldBeCalled();

        $this->configureOptions($resolver);
    }
}
