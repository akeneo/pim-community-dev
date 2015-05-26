<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class ProductSavingOptionsResolverSpec extends ObjectBehavior
{
    function it_a_saving_options_resolver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface');
    }

    function it_resolves_single_save_options()
    {
        $this
            ->resolveSaveOptions(['flush' => true, 'recalculate' => true, 'schedule' => true, 'flush_only_object' => false])
            ->shouldReturn(['flush' => true, 'recalculate' => true, 'schedule' => true, 'flush_only_object' => false]);
    }

    function it_resolves_default_values_for_single_save_options()
    {
        $this
            ->resolveSaveOptions([])
            ->shouldReturn(['flush' => true, 'recalculate' => true, 'schedule' => true, 'flush_only_object' => false]);
    }

    function it_resolves_bulk_save_options()
    {
        $this
            ->resolveSaveAllOptions(['flush' => true, 'recalculate' => false, 'schedule' => true, 'flush_only_object' => false])
            ->shouldReturn(['flush' => true, 'recalculate' => false, 'schedule' => true, 'flush_only_object' => false]);
    }

    function it_resolves_default_values_for_bulk_save_options()
    {
        $this
            ->resolveSaveAllOptions([])
            ->shouldReturn(['flush' => true, 'recalculate' => false, 'schedule' => true, 'flush_only_object' => false]);
    }

    function it_throws_an_exception_when_resolve_unknown_saving_option()
    {
        $this
            ->shouldThrow(new InvalidOptionsException('The option "fake_option" does not exist. Known options are: "flush", "flush_only_object", "recalculate", "schedule"'))
            ->duringResolveSaveOptions(['fake_option' => true, 'recalculate' => false, 'flush' => false, 'schedule' => true, 'flush_only_object' => false]);
    }
}
