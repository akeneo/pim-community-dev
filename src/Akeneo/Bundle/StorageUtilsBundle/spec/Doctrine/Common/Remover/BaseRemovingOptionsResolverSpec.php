<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Remover;

use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class BaseRemovingOptionsResolverSpec extends ObjectBehavior
{
    function it_a_removing_options_resolver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface');
    }

    function it_resolves_single_remove_options()
    {
        $this
            ->resolveRemoveOptions([])
            ->shouldReturn([]);
    }

    function it_throws_an_exception_when_resolve_unknown_saving_option()
    {
        $this
            ->shouldThrow(new UndefinedOptionsException('The option "fake_option" does not exist. Defined options are: "".'))
            ->duringResolveRemoveOptions(['fake_option' => true]);
    }
}
