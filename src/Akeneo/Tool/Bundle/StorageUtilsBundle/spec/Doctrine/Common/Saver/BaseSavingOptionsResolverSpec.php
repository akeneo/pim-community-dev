<?php

namespace spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class BaseSavingOptionsResolverSpec extends ObjectBehavior
{
    function it_a_saving_options_resolver()
    {
        $this->shouldHaveType(SavingOptionsResolverInterface::class);
    }

    function it_resolves_default_values_for_single_save_options()
    {
        $this
            ->resolveSaveOptions([])
            ->shouldReturn([]);
    }

    function it_resolves_default_values_for_bulk_save_options()
    {
        $this
            ->resolveSaveAllOptions([])
            ->shouldReturn([]);
    }

    function it_throws_an_exception_when_resolve_unknown_saving_option()
    {
        $this
            ->shouldThrow(new UndefinedOptionsException('The option "fake_option" does not exist. Defined options are: "".'))
            ->duringResolveSaveOptions(['fake_option' => true]);
    }
}
