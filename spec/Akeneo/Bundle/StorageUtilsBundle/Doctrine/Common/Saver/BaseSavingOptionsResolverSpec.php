<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Saver;

use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class BaseSavingOptionsResolverSpec extends ObjectBehavior
{
    function it_a_saving_options_resolver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface');
    }

    function it_resolves_single_save_options()
    {
        $this
            ->resolveSaveOptions(['flush' => true])
            ->shouldReturn(['flush' => true]);
    }

    function it_resolves_default_values_for_single_save_options()
    {
        $this
            ->resolveSaveOptions([])
            ->shouldReturn(['flush' => true]);
    }

    function it_resolves_bulk_save_options()
    {
        $this
            ->resolveSaveAllOptions(['flush' => true])
            ->shouldReturn(['flush' => true]);
    }

    function it_resolves_default_values_for_bulk_save_options()
    {
        $this
            ->resolveSaveAllOptions([])
            ->shouldReturn(['flush' => true]);
    }

    function it_throws_an_exception_when_resolve_unknown_saving_option()
    {
        $this
            ->shouldThrow(new UndefinedOptionsException('The option "fake_option" does not exist. Defined options are: "flush".'))
            ->duringResolveSaveOptions(['fake_option' => true, 'flush' => false]);
    }
}
